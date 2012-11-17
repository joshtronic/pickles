<?php

/**
 * Number Utility Collection
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
 * Number Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant numeric related manipulation.
 */
class Number
{
	/**
	 * Ordinal Indiciator
	 *
	 * Formats a number by appending an ordinal indicator.
	 *
	 * @static
	 * @link   http://en.wikipedia.org/wiki/Ordinal_indicator
	 * @link   http://en.wikipedia.org/wiki/English_numerals#Ordinal_numbers
	 * @param  string $number number to format
	 * @param  boolean $superscript include <sup> tags
	 * @return string formatted number
	 */
	public static function ordinalIndicator($number, $superscript = false)
	{
		$suffix = 'th';

		if (!in_array(($number % 100), array(11, 12, 13)))
		{
			switch ($number % 10)
			{
				case 1: $suffix = 'st'; break;
				case 2: $suffix = 'nd'; break;
				case 3: $suffix = 'rd'; break;
			}
		}

		if ($superscript)
		{
			$suffix = '<sup>' . $suffix . '</sup>';
		}

		return $number . $suffix;
	}
}

?>
