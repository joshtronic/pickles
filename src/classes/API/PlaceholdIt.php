<?php

/**
 * Placehold.it Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Placehold.it API Interface
 *
 * @link http://placehold.it
 */
class API_PlaceholdIt extends Object
{
	/**
	 * URL
	 *
	 * Generates a Placehold.it URL based on the passed parameters.
	 *
	 * @param  integer $width width of the image
	 * @param  integer $height optional height of the image
	 * @param  string $format optional format of the image
	 * @param  string $background optional background color of the image
	 * @param  string $foreground optional foreground color of the image
	 * @param  string $text optional text to display in the image
	 * @return string Placehold.it URL
	 */
	public function url($width, $height = null, $format = 'gif', $background = null, $foreground = null, $text = null)
	{
		// Checks if the format is valid
		if (!in_array($format, ['gif', 'jpeg', 'jpg', 'png']))
		{
			throw new Exception('Invalid format. Valid formats: gif, jpeg, jpg and png.');
		}
		// Checks if foreground is present without background
		elseif ($foreground && !$background)
		{
			throw new Exception('You must specify a background color if you wish to specify a foreground color.');
		}
		// Checks the background color's length
		elseif ($background && strlen($background) < 6)
		{
			throw new Exception('The background color must be a 6 character hex code.');
		}
		// Checks the foreground color's length
		elseif ($foreground && strlen($foreground) < 6)
		{
			throw new Exception('The foreground color must be a 6 character hex code.');
		}

		$url = 'http://placehold.it/' . $width;

		if ($height)
		{
			$url .= 'x' . $height;
		}

		$url .= '.' . $format;

		if ($background)
		{
			$url .= '/' . $background;
		}

		if ($foreground)
		{
			$url .= '/' . $foreground;
		}

		if ($text)
		{
			$url .= '&text=' . urlencode($text);
		}

		return $url;
	}

	/**
	 * URL
	 *
	 * Generates a Placehold.it <img> tag based on the passed parameters.
	 *
	 * @param  integer $width width of the image
	 * @param  integer $height optional height of the image
	 * @param  string $format optional format of the image
	 * @param  string $background optional background color of the image
	 * @param  string $foreground optional foreground color of the image
	 * @param  string $text optional text to display in the image
	 * @return string <img> tag with the Placehold.it URL
	 */
	public function img($width, $height = null, $format = 'gif', $background = null, $foreground = null, $text = null)
	{
		return '<img src="' . $this->url($width, $height, $format, $background, $foreground, $text) . '">';
	}
}

?>
