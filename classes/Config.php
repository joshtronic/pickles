<?php

/**
 * Configuration Class File for PICKLES
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
 * Config Class
 *
 * Handles loading a configuration file and parsing the data for
 * any public nodes that need to be made available to the viewer.
 *
 * @usage <code>$config = Config::getInstance();
 *$config->node; // Returns SimpleXML object</code>
 */
class Config {

	/**
	 * Private instance of the Config class
	 */
	private static $_instance;

	/**
	 * Private collection of public config data
	 */
	private $_public;

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
	 * Gets an instance of the requested class object
	 *
	 * Determines if a requested class object has already
	 * been instantiated, if so it will use it.  If not,
	 * it will create one.
	 *
	 * @return An instace of the requested class
	 */
	public static function getInstance() {
		if (!self::$_instance instanceof Config) {
			self::$_instance = new Config();
		}

		return self::$_instance;
	}

	/**
	 * Loads a configuration file
	 *
	 * Handles the potential loading of the configuration file and
	 * sanitizing the boolean strings into actual boolean values.
	 *
	 * @param  string $file Filename of the XML file to be loaded
	 * @return boolean Success of the load process
 	 * @todo   Add the ability to load in multiple configuration files.
	 */
	public static function load($file) {

		$config = Config::getInstance();

		if (file_exists($file)) {
			/**
			 * @todo LIBXML_NOCDATA is 5.1+ and I want PICKLES to
			 *       be 5.0+ compatible.  Potential fix is to read
			 *       the file in as a string, and if it has CDATA, 
			 *       throw an internal warning.
			 */
			$data = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

			// Loops through the top level nodes to find public nodes
			$variables = get_object_vars($data);

			if (is_array($variables)) {
				foreach ($variables as $key => $value) {
					if (is_object($value) && isset($value->attributes()->public) && $value->attributes()->public == true) {
						$config->_public[$key] = $value;
					}

					$config->$key = $value;
				}
			}

			return true;
		}
	}

	/** 
	 * Gets the authentication value
	 *
	 * @return boolean The model's authentication setting or false
	 */
	public function getAuthentication() {
		if (isset($this->models->authentication) && $this->models->authentication == 'true') {
			return true;
		}

		return false;
	}

	/**
	 * Alias for $config->models->default with string cast
	 *
	 * @return Returns the default model set or null
	 */
	public function getDefaultModel() {
		if (isset($this->models->default)) {
			return (string)$this->models->default;
		}

		return 'home';
	}
	
	/** 
	 * Gets active status of the site
	 *
	 * @return boolean The site's disabled setting or false
	 */
	public function getDisabled() {
		if (isset($this->models->disabled) && $this->models->disabled == 'true') {
			return true;
		}

		return false;
	}

	/**
	 * Alias for $config->_public 
	 *
	 * @return Returns the variable value or null if no variable.
	 */
	public function getPublicData() {
		if (isset($this->_public)) {
			return $this->_public;
		}

		return null;
	}

	/** 
	 * Gets the session value
	 *
	 * @return boolean The model's session setting or false
	 */
	public function getSession() {
		if (isset($this->models->session) && $this->models->session == 'true') {
			return true;
		}

		return false;
	}

	/**
	 * Gets the shared model
	 *
	 * @param  string $requested_model The model being requested
	 * @return string The name of the shared model or null
	 */
	public function getSharedModel($requested_model) {

		$additional = null;

		if (strpos($requested_model, '/') !== false) {
			list($requested_model, $additional) = split('/', $requested_model, 2);
			$additional = '/' . $additional;
		}

		if (isset($this->models->shared->model)) {
			foreach ($this->models->shared->model as $shared_model) {
				if (isset($shared_model->alias)) {
					if ($requested_model == $shared_model->alias) {
						return (string)$shared_model->name . $additional;
					}
				}
				else {
					if ($requested_model == $shared_model->name) {
						return (string)$shared_model->name . $additional;
					}
				}
			}
		}

		return 'home';
	}

	/** 
	 * Gets the viewer value
	 *
	 * @return boolean The model's viewer setting or false
	 */
	public function getViewer() {
		if (isset($this->models->viewer) && $this->models->viewer == 'true') {
			return true;
		}

		return false;
	}
}

?>
