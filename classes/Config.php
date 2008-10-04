<?php

/**
 * Configuration class
 *
 * Handles loading a configuration file and parsing the data for any public
 * nodes that need to be made available to the viewer.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Add the ability to load in multiple configuration files.
 */
class Config extends Singleton {

	/**
	 * Private instance of the Config class
	 */
	private static $instance;

	/**
	 * Private collection of data to be loaded by the viewer
	 */
	private $viewer_data;

	/**
	 * Private constructor
	 */
	private function __construct() { }

	/**
	 * Gets an instance of the configuration object
	 *
	 * Determines if a Config object has already been instantiated, if so it
	 * will use it.  If not, it will create one.
	 *
	 * @return An instace of the Config class
	 */
	public static function getInstance() {
		$class = __CLASS__;

		if (!self::$instance instanceof $class) {
			self::$instance = new $class();
		}

		return self::$instance;
	}

	/**
	 * Loads a configuration file
	 *
	 * Handles the potential loading of the configuration file and
	 * sanitizing the boolean strings into actual boolean values.
	 *
	 * @param  string $file File name of the config file to be loaded
	 * @return boolean Based on the success or failure of the file load
	 * @todo   Either get rid of or make better use of the Error object
	 */
	public function load($file) {
		if (file_exists($file)) {
			$this->file = $file;
			
			/**
			 * @todo LIBXML_NOCDATA is 5.1+ and I want PICKLES to be 5.0+
			 *       compatible.  Potential fix is to read the file in as
			 *       a string, and if it has CDATA, throw an internal
			 *       warning.
			 */
			$this->data = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);

			// Loops through the top level nodes to find public nodes
			$variables = get_object_vars($this->data);

			if (is_array($variables)) {
				foreach ($variables as $key => $value) {
					if (is_object($value) && isset($value->attributes()->public) && $value->attributes()->public == true) {
						$this->viewer_data[$key] = $value;
					}
				}
			}

			return true;
		}
		else {
			Error::addError('Unable to load the configuration file');
			return false;
		}
	}

	/**
	 * Alias for $config->models->default
	 *
	 * @return Returns the default model set or null
	 * @todo   Need to add a PICKLES fallback model just in case
	 */
	public function getDefaultModel() {
		if (isset($this->data->models->default)) {
			return (string)$this->data->models->default;
		}

		return null;
	}

	/**
	 *
	 */
	public function getSharedModel($requested_model) {

		$additional = null;

		if (strpos($requested_model, '/') !== false) {
			list($requested_model, $additional) = split('/', $requested_model, 2);
			$additional = '/' . $additional;
		}

		if (isset($this->data->models->shared->model)) {
			foreach ($this->data->models->shared->model as $shared_model) {
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

		return null;
	}

	/**
	 * Alias for $config->viewer_data 
	 *
	 * @return Returns either the variable value or null if no variable.
	 */
	public function getViewerData() {
		if (isset($this->viewer_data)) {
			return $this->viewer_data;
		}

		return null;
	}

	public function __get($node) {
		if (isset($this->data->$node) && $this->data->$node != '') {
			if (in_array($this->data->$node, array('true', 'false'))) {
				return (bool)$this->data->$node;
			}
			else if (is_object($this->data->$node)) {
				return $this->data->$node;
			}
		}

		return null;
	}
}

?>
