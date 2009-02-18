<?php

/**
 * Common Gateway Class File for PICKLES
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
 * @copyright Copyright 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Common Gateway Class
 *
 * This is the class that each gateway class should be extending from.
 */
abstract class Gateway_Common extends Object {
	
	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the module to the object.
	 */
	public function __construct(Config $config, Error $error) {
		parent::__construct();

		$this->config = $config;
		$this->error  = $error;
	}

	/**
	 * Abstract processing function that is overloaded within the loaded gateway
	 */
	public abstract function process();
}

?>
