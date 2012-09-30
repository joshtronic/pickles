<?php

/**
 * JSON Display Class File for PICKLES
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
 * JSON Display
 *
 * Displays data in JavaScript Object Notation.
 */
class Display_JSON extends Display_Common
{
	/**
	 * Renders the data in JSON format
	 */
	public function render()
	{
		echo Convert::toJSON($this->module_return);
	}
}

?>
