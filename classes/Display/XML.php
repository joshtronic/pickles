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
 * Displays data in XML format.
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
