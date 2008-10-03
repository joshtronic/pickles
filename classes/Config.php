<?php

/**
 * Configuration class
 *
 * Handles loading and caching of the configuration into a Singleton object.
 * Contains logic to determine if the configuration has already been loaded
 * and will opt to use a "frozen" object rather than instantiate a new one.
 * Also contains logic to reload configurations if the configuration file
 * had been modified since it was originally instantiated (smart caching).
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
	 * @param  string $file File name of the configuration file to be loaded
	 * @return boolean Based on the success or failure of the file load
	 * @todo   Either get rid of or make better use of the Error object
	 * @todo   Some of this code seems screwy, (bool) on an array??
	 */
	public function load($file) {
		if (file_exists($file)) {
			$this->file = $file;

			/**
			 * @todo LIBXML_NOCDATA is 5.1+ and I want PICKLES to be 5.0+ compatible
			 */
			$config_array = ArrayUtils::object2array(simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA));

			/**
			 * @todo Loop through the object and deal with it accordingly
			 */
			if (is_array($config_array)) {
				foreach ($config_array as $variable => $value) {
					if ($value == 'true' || $value == array()) {
						$value = (bool)$value;
					}
					
					if (isset($value['@attributes']['public'])) {
						if ($value['@attributes']['public'] == true) {
							
							if (count($value['@attributes']) == 1) {
								unset($value['@attributes']);
							}
							else {
								unset($value['@attributes']['public']);
							}

							$this->viewer_data[$variable] = $value;
						}
					}

					$this->data[$variable] = $value == array() ? (bool)$value : $value;
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
	 * Gets the default model
	 *
	 * @return Returns the default model as set
	 */
	public function getDefaultModel() {
		if (isset($this->data['models']['default'])) {
			return $this->data['models']['default'];
		}

		return false;
	}

	/**
	 * Gets the viewer data
	 *
	 * @return Returns either the variable value or false if no variable.
	 * @todo   Need better checking if the passed variable is an array when
	 *         the array element value is present
	 * @todo   Returning false could be misleading, especially if you're
	 *         expecting a boolean value to begin with.  Perhaps an error
	 *         should be thrown?
	 */
	public function getViewerData() {
		if (isset($this->viewer_data)) {
			return $this->viewer_data;
		}

		return false;
	}
}

?>
