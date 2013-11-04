<?php

/**
 * Date Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
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
		return Time::age($date);
	}
}

?>
