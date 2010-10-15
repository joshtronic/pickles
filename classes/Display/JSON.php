<?php

/**
 * JSON Display Class File for PICKLES
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
 * JSON Display
 *
 * Displays data in JavaScript Object Notation.
 *
 * Requires PHP 5 >= 5.2.0 or PECL json >= 1.2.0
 * Note: PECL json 1.2.1 is included /vendors
 *
 * @link http://json.org/
 * @link http://us.php.net/json_encode
 * @link http://pecl.php.net/package/json
 */
class Display_JSON extends Display_Common
{
	/**
	 * Renders the data in JSON format
	 */
	public function render()
	{
        if (JSON_AVAILABLE)
		{
            echo json_encode($this->module_return);
        }
		else
		{
            echo '{ "status": "error", "message": "json_encode() not found" }';
        }
	}
}

?>
