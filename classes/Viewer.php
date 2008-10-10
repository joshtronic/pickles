<?php

/**
 * Viewer Class File for PICKLES
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
 * Viewer Class
 *
 * Uses the factory design pattern to create a new Viewer object
 * based on what viewer the model says it wants to use.
 */
class Viewer {

	/**
	 * Private constructor
	 */
	private function __construct() { }

	/**
	 * Viewer Factory
	 *
	 * Creates an instance of the Viewer type that the model requests.
	 *
	 * @param  string $tye The type of viewer to produce
	 * @return object An instance of the viewer, loaded with the passed model
	 * @todo   Create constants to correspond with each viewer type so it's
	 *         potentially easier to reference from the model (since the
	 *         constants would each be uppercase instead of mixedcase.
	 */
	public static function create($type) {
		$class = 'Viewer_' . $type;
		return new $class();
	}
}

?>
