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
 * Handles loading the site's configuration file (if available). At the moment
 * this class is a very skewed Singleton. The plan is to eventually extend this
 * out to support multiple configuration files, and the ability to load in
 * custom config files on the fly as well. The core of PICKLES uses the class
 * as a Singleton so we're not loading the configuration multiple times per
 * page load.
 *
 * @usage <code>$config = new Config($filename);</code>
 */
class Config extends Object
{
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
	 * Calls the parent constructor and loads the passed file.
	 *
	 * @param string $filename optional Filename of the config
	 */
	public function __construct($filename = '../config.php')
	{
		parent::__construct();

		$this->load($filename);
	}

	/**
	 * Loads a configuration file
	 *
	 * @param  string $filename filename of the config file
	 * @return boolean success of the load process
	 */
	public function load($filename)
	{
		$environments = false;
		$environment  = false;

		// Sanity checks the config file
		if (file_exists($filename) && is_file($filename) && is_readable($filename))
		{
			require_once $filename;

			// Determines the environment
			if (isset($config['environment']))
			{
				$environment = $config['environment'];
			}
			else
			{
				if (isset($config['environments']) && is_array($config['environments']))
				{
					$environments = $config['environments'];

					// Loops through the environments and tries to match on IP or name
					foreach ($config['environments'] as $name => $host)
					{
						if ((preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host) && $_SERVER['SERVER_ADDR'] == $host) || $_SERVER['SERVER_NAME'] == $host)
						{
							// Sets the environment and makes a run for it
							$environment = $name;
							break;
						}
					}
				}
			}

			// Flattens the array based on the environment
			$this->data = $this->flatten($environment, $config);

			// Restore environments value
			if ($environments != false)
			{
				$this->data['environments'] = $environments;
			}

			// Sets the environment if it's not set already
			if (!isset($this->data['environment']))
			{
				$this->data['environment'] = $environment;
			}

			return true;
		}

		return false;
	}

	private function flatten($environment, $array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (is_array($value))
				{
					if (isset($value[$environment]))
					{
						$value = $value[$environment];
					}
					else
					{
						$value = $this->flatten($environment, $value);
					}
				}

				$array[$key] = $value;
			}
		}
		
		return $array;
	}

	/**
	 * Get instance of the object
	 *
	 * Let's the parent class do all the work
	 *
	 * @static
	 * @param  string $class name of the class to instantiate
	 * @return object self::$instance instance of the Config class
	 */
	public static function getInstance($class = 'Config')
	{
		return parent::getInstance($class);
	}

	/**
	 * Magic Setter Method
	 *
	 * Prohibits the direct modification of module variables.
	 *
	 * @param string $name name of the variable to be set
	 * @param mixed $value value of the variable to be set
	 */
	public function __set($name, $value)
	{
		throw new Exception('Cannot set config variables directly', E_USER_ERROR);
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
