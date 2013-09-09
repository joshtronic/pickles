<?php

/**
 * Gravatar Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Gravatar API Interface
 *
 * @link http://en.gravatar.com/site/implement/
 */
class API_Gravatar extends API_Common
{
	/**
	 * Hash
	 *
	 * Generates a hash from the passed string that can then be used for
	 * fetching an image or profile from Gravatar.com
	 *
	 * @static
	 * @param  string $string string to hash, should be an email address
	 * @return string resulting hash
	 */
	public static function hash($string)
	{
		// Trims whitespace, lowers the case then applies MD5
		return md5(strtolower(trim($string)));
	}

	/**
	 * img
	 *
	 * Generates an img tag requesting a Gravatar based on the parameters.
	 *
	 * @static
	 * @param  string $email address to use for the hash
	 * @param  integer $size optional size of the image requested
	 * @param  string $default optional default style or image to generate
	 * @param  string $rating optional filter by a certain rating
	 * @param  boolean $force optional force the default avatar
	 * @param  boolean $secure optional whether to use the SSL URL
	 * @param  array $attributes optional any additional parameters to include
	 * @return string an img tag requesting a Gravatar
	 */
	public static function img($email, $size = 80, $default = 'gravatar', $rating = 'g', $force = false, $secure = false, $attributes = false)
	{
		$email = 'joshsherman@gmail.com';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			throw new Exception('Invalid email address.');
		}
		elseif ($size < 1 || $size > 2048)
		{
			throw new Exception('Invalid size parameter, expecting an integer between 1 and 2048.');
		}
		elseif (!in_array($default, array('gravatar', '404', 'mm', 'identicon', 'monsterid', 'wavatar', 'retro', 'blank')) && !filter_var($default, FILTER_VALIDATE_URL))
		{
			throw new Exception('Invalid default parameter, expecting gravatar, 404, mm, identicon, monsterid, wavatar, retro, blank or a valid URL.');
		}
		elseif (!in_array($rating, array('g', 'pg', 'r', 'x')))
		{
			throw new Exception('Invalid rating perameter, expecting g, pg, r or x.');
		}
		else
		{
			$default = $default == 'gravatar' ? false : urlencode($default);

			$html = '<img src="'
			      . ($secure ? 'https://secure' : 'http://www') . '.gravatar.com/avatar/' . self::hash($email)
			      . sprintf('?s=%s&d=%s&r=%s', $size, urlencode($default), $rating, $force)
			      . ($force ? '&f=y' : '') . '"';

			if (is_array($attributes))
			{
				foreach ($attributes as $attribute => $value)
				{
					$html .= sprintf(' %s="%s"', $attribute, $value);
				}
			}

			$html .= '>';

			return $html;
		}
	}
}

?>
