<?php

/**
 * JSON Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
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
        if (!function_exists('json_encode'))
		{
            echo '{ "type" : "error", "message" : "json_encode() not found" }';
        }
		else
		{
            echo json_encode($this->module_return);
        }
	}
}

?>
