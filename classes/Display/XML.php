<?php

/**
 * XML Display Class File for PICKLES
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
 * XML Display
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
class Display_XML extends Display_Common
{
	/**
	 * Renders the data in XML format
	 */
	public function render()
	{
		echo Convert::arrayToXML($this->module_return);
	}
}

?>
