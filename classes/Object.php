<?php

/**
 * Object Class File for PICKLES
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
 * Object Class
 *
 * Every non-Singleton-based class needs to extend this class.
 * Any models will extend the Model class which entends the Object
 * class already.  This class handles getting an instance of the
 * Config object so that it's available.  Also provides a getter
 * and setter for variables.
 */
class Object {

	/**
	 * Constructor
	 */
	public function __construct() { }

	/**
	 * Destructor
	 */
	public function __destruct() { }

	/**
	 *
	 */
	public function __get($variable) {
		if (isset($this->$variable)) {
			return $this->$variable;
		}

		return null;
	}

	/**
	 * Sets a variable
	 *
	 * @param string $variable Name of the variable to be set
	 * @param mixed $value Value to be assigned to the passed variable
	 * @todo  Not sure we need this at all, grep to check if it's being used.
	 */
	public function set($variable, $value) {
		$this->$variable = $value;
	}
}

?>
