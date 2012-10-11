<?php

/**
 * Time Utility Collection
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
 * Time Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant time and date related manipulation.
 */
class Time
{
	// {{{ Intervals (in seconds)

	/**
	 * Minute
	 *
	 * Seconds in a minute
	 *
	 * @static
	 * @var    integer
	 */
	public static $minute = 60;

	/**
	 * Hour
	 *
	 * Seconds in an hour (minute * 60)
	 *
	 * @static
	 * @var    integer
	 */
	public static $hour = 3600;

	/**
	 * Day
	 *
	 * Seconds in a day (hour * 24)
	 *
	 * @static
	 * @var    integer
	 */
	public static $day = 86400;

	/**
	 * Week
	 *
	 * Seconds in a week (day * 7)
	 *
	 * @static
	 * @var    integer
	 */
	public static $week = 604800;

	/**
	 * Month
	 *
	 * Seconds in a month (day * 30)
	 *
	 * @static
	 * @var    integer
	 */
	public static $month = 2592000;

	/**
	 * Quarter
	 *
	 * Seconds in a quarter (day * 90)
	 *
	 * @static
	 * @var    integer
	 */
	public static $quarter = 7776000;

	/**
	 * Year
	 *
	 * Seconds in a year (day * 365)
	 *
	 * @static
	 * @var    integer
	 */
	public static $year = 31536000;

	// }}}

	/**
	 * Age
	 *
	 * Calculates age based on the passed date.
	 *
	 * @static
	 * @param  string $date birth / inception date
	 * @return integer $age number of years old
	 */
	public static function age($date)
	{
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date))
		{
			$date = date('Y-m-d', strtotime($date));
		}

		list($year, $month, $day) = explode('-', $date, 3);

		$age = date('Y') - $year;

		if (date('md') < $month . $day)
		{
			$age--;
		}

		return $age;
	}
}

?>
