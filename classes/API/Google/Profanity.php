<?php

/**
 * Google Profanity Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Google Profanity API Interface 
 */
class API_Google_Profanity extends API_Common
{
	/**
	 * Check
	 *
	 * Checks if a word is considered profanity.
	 *
	 * @usage API_Google_Profanity::check('fuck'); // returns true
	 * @param string $word word to check
	 * @return boolean whether or not the word is profanity
	 */
	public static function check($word)
	{
		$response = json_decode(file_get_contents('http://www.wdyl.com/profanity?q=' . $word), true);

		if ($response == null || !isset($response['response']) || !in_array($response['response'], array('true', 'false')))
		{
			throw new Exception('Invalid response from API.');
		}
		else
		{
			return $response['response'] == 'true';
		}
	}
}

?>
