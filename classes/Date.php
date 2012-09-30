<?php

/**
 * Date Utility Collection
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
 * Date Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant date related manipulation.
 */
class Date
{
	// {{{ Age

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

	// }}}
}

?>
