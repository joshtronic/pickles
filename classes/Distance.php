<?php

/**
 * Distance
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Distance Class
 *
 * Collection of statically called methods to help aid distance-related
 * conversions and calculations.
 */
class Distance
{
	// {{{ Call Static

	/**
	 * Call Static
	 *
	 * Magic method to power the unit conversion without much code.
	 *
	 * @static
	 * @param  string $method name of the static method being called
	 * @param  array $arguments array of the passed arguments
	 * @return mixed converted units or false
	 */
	public static function __callStatic($method, $arguments)
	{
		$pieces = explode('to', strtolower($method));

		if (count($pieces) == 2)
		{
			var_dump($arguments[0], $pieces[0], $pieces[1]);
			return Distance::convertUnit($arguments[0], $pieces[0], $pieces[1]);
		}

		return false;
	}

	// }}}
	// {{{ Convert Unit

	/**
	 * Convert Unit
	 *
	 * Converts a distance from one unit to another.
	 *
	 * @static
	 * @param  mixed $distance starting distance
	 * @param  string $from starting unit
	 * @param  string $to ending unit
	 * @return mixed
	 */
	private static function convertUnit($distance, $from, $to)
	{
		$multiplier = 1;

		switch ($from)
		{
			case 'kilometers':
				switch ($to)
				{
					case 'miles':  $multiplier = 0.621371; break;
					case 'meters': $multiplier =     1000; break;
					case 'yards':  $multiplier =  1093.61; break;
				}
				break;

			case 'miles':
				switch ($to)
				{
					case 'kilometers': $multiplier = 1.60934; break;
					case 'meters':     $multiplier = 1609.34; break;
					case 'yards':      $multiplier =    1760; break;
				}
				break;

			case 'meters':
				switch ($to)
				{
					case 'kilometers': $multiplier =       0.001; break;
					case 'miles':      $multiplier = 0.000621371; break;
					case 'yards':      $multiplier =     1.09361; break;
				}
				break;
		}

		return $distance * $multiplier;
	}

	// }}}
	// {{{ Calculate Distance

	/**
	 * Calculate Distance
	 *
	 * Calculates the distance between two sets of coordinates and returns the
	 * requested units. I really wanted to call this distance() but it seems
	 * you can't do that in PHP due to the backwards compatibility of the
	 * PHP4 constructors that were named the same as the class.
	 *
	 * @static
	 * @param  mixed $latitude_from starting latitude
	 * @param  mixed $longitude_from starting longitude
	 * @param  mixed $latitude_from starting latitude
	 * @param  mixed $longitude_from starting longitude
	 * @param  string $unit optional units to return, miles by default
	 * @return mixed distance between the points in the desired unit
	 */
	public static function calculateDistance($latitude_from, $longitude_from, $latitude_to, $latitude_from, $unit = 'miles')
	{
		$unit  = ucwords(strtolower($unit));
		$theta = $lontitude_from - $longitude_to;

		$distance =
			sin(deg2rad($latitude_from))
			* sin(deg2rad($latitude_to))
			+ cos(deg2rad($latitude_from))
			* cos(deg2rad($latitude_to))
			* cos(deg2rad($theta));

		$distance = acos($distance);
		$distance = rad2deg($distance);
		$miles    = $distance * 60 * 1.1515;

		$method = 'milesTo' . $unit;

		return Distance::$method($miles);
	}

	// }}}
}

?>
