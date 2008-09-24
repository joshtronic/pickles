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
	 */
	public function load($file) {
		if (file_exists($file)) {
			$this->file = $file;

			$config_array = ArrayUtils::object2array(simplexml_load_file($file));

			if (is_array($config_array)) {
				foreach ($config_array as $variable => $value) {
					if ($value == 'true' || $value == array()) {
						$value = (bool)$value;
					}

					$this->$variable = $value == array() ? (bool)$value : $value;
				}
			}

			/**
			 * @todo Okay, now if no default section is specified, we'll
			 *       default to the first section listed.  But what
			 *       should be done if no sections are specified?
			 *       Perhaps force just the index to load, or perhaps
			 *       error out?  I have to keep in mind that single
			 *       page sites exist where no navigation will exist.
			 *       So yeah, I suppose just specifying the default
			 *       would combat against that, or should I drill down
			 *       further, and see if any site level models exist and
			 *       load the first one, or even better I'm thinking
			 *       that a shared model / template would be good when
			 *       nothing is available.  That would in turn tell the
			 *       user to fix the issue to be able to get it all
			 *       working again.  Damn, this ended up being a very
			 *       long @todo.
			 * @todo This may be better suited in the loop above since
			 *       we're already looping through all the values, we
			 *       could snag it in passing.
			 */
			if (!isset($this->navigation['default']) || $this->navigation['default'] == '') {
				$this->navigation['default'] = key($this->navigation['sections']);
			
			}

			return true;
		}
		else {
			Error::addError('Unable to load the configuration file');
			return false;
		}
	}
}

?>
