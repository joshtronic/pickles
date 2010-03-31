<?php

/**
 * Configuration Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 */

/**
 * Config Class
 *
 * Handles loading the site's configuration file (if available).
 *
 * @usage <code>$config = new Config($filename);</code>
 */
class Config
{
	/**
	 * Instance of the Config object
	 *
	 * @static 
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Config data
	 *
	 * @access private
	 * @var    array
	 */
	private $data = array();

	/**
	 * Constructor
	 *
	 * Calls the parent constructor and loads the pass file
	 *
	 * @param string $filename optional Filename of the config
	 */
	public function __construct($filename = '../config.ini')
	{
		$this->load($filename);
	}

	/**
	 * Loads a configuration file
	 *
	 * Handles the potential loading of the configuration file and
	 * sanitizing the boolean strings into actual boolean values.
	 *
	 * @param  string $filename filename of the config file
	 * @return boolean Success of the load process
 	 * @todo   Add the ability to load in multiple configuration files.
	 */
	public function load($filename)
	{
		// Sanity checks the config file
		if (file_exists($filename) && is_file($filename) && is_readable($filename))
		{
			$this->data = parse_ini_file($filename, true);

			return true;	
		}
		else
		{
			Error::fatal('config.ini is either missing or unreadable');			
		}
	}

	/**
	 * Get instance of the object
	 *
	 * Instantiates a new object if one isn't already available, then
	 * returns the instance.
	 *
	 * @static
	 * @return object self::$instance instance of the Config
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance) || empty(self::$instance))
		{
			self::$instance = new Config();
		}

		return self::$instance;
	}

	/**
	 * Magic Setter Method
	 *
	 * Prohibits the direct modification of module variables.
	 *
	 * @param  string $name name of the variable to be set
	 * @param  mixed $value value of the variable to be set
	 * @return boolean false
	 */
	public function __set($name, $value)
	{
		trigger_error('Cannot set config variables directly', E_USER_ERROR);
		return false;
	}

	/**
	 * Magic Getter Method
	 *
	 * Attempts to load the config variable. If it's not set, will override
	 * the variable with boolean false.
	 *
	 * @param  string $name name of the variable requested
	 * @return mixed value of the variable or boolean false
	 */
	public function __get($name)
	{
		if (!isset($this->data[$name]))
		{
			$this->data[$name] = false;
		}

		return $this->data[$name];
	}
}

?>
