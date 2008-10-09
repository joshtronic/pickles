<?php

/**
 * Singleton Class File for PICKLES
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
 * Singleton Class
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code base
 * is established as well as the path that Smarty will use to store the
 * compiled pages.
 */
class Singleton {

	/**
	 * Protected collection of data
	 */
	protected $data; 

	/**
	 * Private constructor
	 */
	private function __construct() { }

	/**
	 * __clone
	 */
	public function __clone() {
		trigger_error('Cloning is not available on a Singleton (that would defeat the purpose wouldn\'t it?)', E_USER_ERROR);
	}

	/**
	 * Gets a variable
	 *
	 * @param  string $variable Name of the variable to be returned
	 * @param  string $array_element Name of the array element that's part
	 *         of the requested variable (optional)
	 * @return Returns either the variable value or false if no variable.
	 * @todo   Need better checking if the passed variable is an array when
	 *         the array element value is present
	 * @todo   Returning false could be misleading, especially if you're
	 *         expecting a boolean value to begin with.  Perhaps an error
	 *         should be thrown?
	 */
	 /*
	public function get($variable, $array_element = null) {
		if (isset($this->data[$variable])) {
			if (isset($array_element)) {
				$array = $this->data[$variable];

				if (isset($array[$array_element])) {
					return $array[$array_element];
				}
			}
			else {
				return $this->data[$variable];
			}
		}

		return false;
	}
	*/

	/**
	 * Sets a variable
	 * 
	 * @param string $variable Name of the variable to be set
	 * @param mixed $value Value to be assigned to the passed variable
     */
	//public function set($variable, $value) {
	//	$this->data[$variable] = $value;
	//}
}

?>
