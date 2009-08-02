<?php

/**
 * Common PennySMS Web Service Class File for PICKLES
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
 * Common PennySMS Web Service Class
 *
 * This is the class that each PennySMS gateway class should be extending from.
 */
abstract class WebService_PennySMS_Common extends WebService_Common
{
	protected $variables = array();
	
	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the module to the object.
	 */
	public function __construct(Config $config, Error $error)
	{
		parent::__construct($config, $error);

		$this->config = $config;
		$this->error  = $error;
	}
	
	/**
	 * Variable Setter
	 *
     * Loads an array full of our variables to use
	 */
	public function set($variable, $value)
	{
		$this->variables[$variable] = $value;
	}

	/**
	 * Abstract processing function that is overloaded within the loaded gateway
	 */
	//public abstract function process();
		
	/**
	 * Check Variables
	 *
	 * Checks that the variables are present and non-blank
	 */
	protected function checkVariables()
	{
		$valid = false;

		// Checks that the variables are set
		if (isset($this->variables['api_key'], $this->variables['from'], $this->variables['phone'], $this->variables['message']))
		{
			// Checks that the variables aren't empty
			if (trim($this->variables['api_key']) != '' && trim($this->variables['from']) != '' && trim($this->variables['phone']) != '' && trim($this->variables['message']) != '')
			{
				$valid = true;
			}
		}

		return $valid;
	}
}

?>
