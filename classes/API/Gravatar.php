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
 * @copyright Copyright 2007-2012, Josh Sherman
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
}

?>
