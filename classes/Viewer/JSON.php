<?php

/**
 * JSON Viewer Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * JSON Viewer
 *
 * Displays data in JavaScript Object Notation.  Requires PHP 5 >= 5.2.0 or
 * PECL json 1.2.0 or 1.2.1
 *
 * @link       http://json.org/
 * @link       http://us.php.net/json_encode
 * @link       http://pecl.php.net/package/json
 */
class Viewer_JSON extends Viewer_Common {

	/**
	 * Displays the data in JSON format
	 */
	public function display() {
        if (!function_exists('json_encode')) {
            echo '{ "type" : "error", "message" : "json_encode() not found" }';
        } else {
            echo json_encode($this->data);
        }
	}
}

?>
