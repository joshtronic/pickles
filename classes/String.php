<?php

/**
 * String Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * String Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant string related manipulation.
 */
class String
{
	// {{{ Format Phone Number

	/**
	 * Format Phone Number
	 *
	 * Formats a 10 digit phone number with dashes as ###-###-####.
	 *
	 * @static
	 * @param  string $number number to format
	 * @param  string $replacement output of the string
	 * @return string formatted phone number
	 */
	public static function formatPhoneNumber($number, $replacement = '$1-$2-$3')
	{
		// Strips characters we don't need
		$number = str_replace(array('(', ')', ' ', '-', '.', '_'), '', $number);

		// Formats the number
		return preg_replace('/^(\d{3})(\d{3})(.+)$/', $replacement, $number);
	}

	// }}}
	// {{{ Generate Gravatar Hash

	/**
	 * Generate Gravatar Hash
	 *
	 * Generates a hash from the passed string that can then be used for
	 * fetching an avatar from Gravatar.com
	 *
	 * @static
	 * @param  string $string string to hash, should be an email address
	 * @return string resulting hash
	 */
	public static function generateGravatarHash($string)
	{
		// Trims whitespace, lowers the case then applies MD5
		return md5(strtolower(trim($string)));
	}

	// }}}
	// {{{ Is Empty

	/**
	 * Is Empty
	 *
	 * Checks if a string is empty. You can use the PHP function empty() but
	 * that returns true for a string of "0". Last I checked, that's not an
	 * empty string. PHP's function also doesn't apply trim() to the value
	 * to ensure it's not just a bunch of spaces.
	 *
	 * @static
	 * @param  string $value string(s) to be checked
	 * @return boolean whether or not the string is empty
	 */
	public static function isEmpty()
	{
		foreach (func_get_args() as $value)
		{
			if (trim($value) == '')
			{
				return true;
			}
		}

		return false;
	}

	// }}}
	// {{{ Random

	/**
	 * Random
	 *
	 * Generates a pseudo-random string based on the passed parameters.
	 *
	 * Note: Similar characters = 0, O, 1, I (and may be expanded)
	 *
	 * @static
	 * @param  integer $length optional length of the generated string
	 * @param  boolean $alpha optional include alpha characters
	 * @param  boolean $numeric optional include numeric characters
	 * @param  boolean $similar optional include similar characters
	 * @return string generated string
	 */
	public static function random($length = 8, $alpha = true, $numeric = true, $similar = true)
	{
		$characters = array();
		$string     = '';

		// Adds alpha characters to the list
		if ($alpha == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('A', 'Z'));
			}
			else
			{
				$characters = array_merge($characters, range('A', 'H'), range('J', 'N'), range('P', 'Z'));
			}
		}

		// Adds numeric characters to the list
		if ($numeric == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('0', '9'));
			}
			else
			{
				$characters = array_merge($characters, range('2', '9'));
			}
		}

		if (count($characters) > 0)
		{
			shuffle($characters);

			for ($i = 0; $i < $length; $i++)
			{
				$string .= $characters[$i];
			}
		}

		return $string;
	}

	// }}}
	// {{{ Truncate

	/**
	 * Truncate
	 *
	 * Truncates a string to a specified length and (optionally) adds a span to
	 * provide a rollover to see the expanded text.
	 *
	 * @static
	 * @param  string $string string to truncate
	 * @param  integer $length length to truncate to
	 * @param  boolean $hover (optional) whether or not to add the rollover
	 * @return string truncate string
	 */
	public static function truncate($string, $length, $hover = true)
	{
		if (strlen($string) > $length)
		{
			if ($hover == true)
			{
				$string = '<span title="' . $string . '" style="cursor:help">' . substr($string, 0, $length) . '...</span>';
			}
			else
			{
				$string = substr($string, 0, $length) . '...';
			}
		}

		return $string;
	}

	// }}}
	// {{{ Upper Words

	/**
	 * Upper Words
	 *
	 * Applies strtolower() and ucwords() to the passed string. The exception
	 * being email addresses which are not formatted at all.
	 *
	 * @static
	 * @param  string $string string to format
	 * @return string formatted string
	 */
	public static function upperWords($string)
	{
		// Only formats non-email addresses
		if (filter_var($string, FILTER_VALIDATE_EMAIL) == false)
		{
			$string = ucwords(strtolower($string));
		}

		return $string;
	}

	// }}}
}

?>
