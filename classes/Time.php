<?php

/**
 * Time Utility Collection
 *
 * PHP version 5.3+
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
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
	 * @var integer
	 */
	const MINUTE = 60;

	/**
	 * Hour
	 *
	 * Seconds in an hour (minute * 60)
	 *
	 * @var integer
	 */
	const HOUR = 3600;

	/**
	 * Day
	 *
	 * Seconds in a day (hour * 24)
	 *
	 * @var integer
	 */
	const DAY = 86400;

	/**
	 * Week
	 *
	 * Seconds in a week (day * 7)
	 *
	 * @var integer
	 */
	const WEEK = 604800;

	/**
	 * Month
	 *
	 * Seconds in a month (day * 30)
	 *
	 * @var integer
	 */
	const MONTH = 2592000;

	/**
	 * Quarter
	 *
	 * Seconds in a quarter (day * 90)
	 *
	 * @var integer
	 */
	const QUARTER = 7776000;

	/**
	 * Year
	 *
	 * Seconds in a year (day * 365)
	 *
	 * @var integer
	 */
	const YEAR = 31536000;

	/**
	 * Decade
	 *
	 * Seconds in a decade (year * 10)
	 *
	 * @var integer
	 */
	const DECADE = 315360000;

	/**
	 * Century
	 *
	 * Seconds in a decade (decade * 10)
	 *
	 * @var integer
	 */
	const CENTURY = 3153600000;

	// }}}

	/**
	 * Age
	 *
	 * Calculates age based on the passed date.
	 *
	 * @static
	 * @param  string $date birth / inception date
	 * @return integer $age number of years old
	 * @todo   Wondering if this really should live in the Date class since it's a Date function. Could flip the aliasing to preserve any older code.
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

	/**
	 * Ago
	 *
	 * Generates a relative time (e.g. X minutes ago).
	 *
	 * @static
	 * @param  mixed $time timestamp to calculate from
	 * @return string relative time
	 */
	public static function ago($time)
	{
		$current = strtotime(Time::timestamp());
		$time    = preg_match('/^\d+$/', $time) ? $time : strtotime($time);

		if ($current == $time)
		{
			$time_ago = 'just now';
		}
		else
		{
			if ($current > $time)
			{
				$difference = $current - $time;
				$suffix     = ' ago';
			}
			else
			{
				$difference = $time - $current;
				$suffix     = ' from now';
			}

			// Less than 1 minute ago (seconds ago)
			if ($difference < 60)
			{
				$time_ago = 'seconds';
			}
			// Less than 1 hour ago (minutes ago)
			elseif ($difference < 3600)
			{
				$minutes  = round($difference / 60);
				$time_ago = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
			}
			// Less than 1 day ago (hours ago)
			elseif ($difference < 86400)
			{
				$hours    = round($difference / 3600);
				$time_ago = $hours . ' hour' . ($hours != 1 ? 's' : '');
			}
			// Less than 1 week ago (days ago)
			elseif ($difference < 604800)
			{
				$days     = round($difference / 86400);
				$time_ago = $days . ' day' . ($days != 1 ? 's' : '');
			}
			// Less than 1 month ago (weeks ago)
			elseif ($difference < 2419200)
			{
				$weeks    = round($difference / 604800);
				$time_ago = $weeks . ' week' . ($weeks != 1 ? 's' : '');
			}
			// Less than 1 year ago (months ago)
			elseif ($difference < 31449600)
			{
				$months   = round($difference / 2419200);
				$time_ago = $months . ' month' . ($months != 1 ? 's' : '');
			}
			// Over 1 year ago (years ago)
			else
			{
				$years    = round($difference / 31449600);
				$time_ago = $years . ' year' . ($years != 1 ? 's' : '');
			}

			$time_ago .= $suffix;
		}

		return $time_ago;
	}

	/**
	 * Timestamp
	 *
	 * Current Universal Time in the specified format.
	 *
	 * @static
	 * @param  string $format format of the timestamp
	 * @return string $timestamp formatted timestamp
	 */
	public static function timestamp($format = 'Y-m-d H:i:s')
	{
		return gmdate($format);
	}
}

?>
