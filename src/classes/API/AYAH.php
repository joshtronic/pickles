<?php

/**
 * Are You A Human Class File for PICKLES
 *
 * PHP version 5
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
 * Are You A Human API Interface
 *
 * @link http://areyouahuman.com
 */
class API_AYAH
{
	public static function getHTML()
	{
		$config = Config::getInstance();

		if (!$config->api['ayah'])
		{
			throw new Exception('Missing API configuration.');
		}

		$ayah = new AYAH($config->api['ayah']);

		return $ayah->getPublisherHTML();
	}

	public static function isHuman()
	{
		$config = Config::getInstance();

		if (!$config->api['ayah'])
		{
			throw new Exception('Missing API configuration.');
		}

		$ayah = new AYAH($config->api['ayah']);

		return $ayah->scoreResult();
	}
}

?>
