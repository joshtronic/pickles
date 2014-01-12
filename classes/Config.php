<?php

/**
 * Configuration Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * @var array
	 */
	public $data = array();

	/**
	 * Constructor
	 *
	 * Calls the parent constructor and loads the passed file.
	 */
	public function __construct()
	{
		parent::__construct();

		$filename     = '../config.php';
		$environments = false;
		$environment  = false;

		// Sanity checks the config file
		if (file_exists($filename) && is_file($filename) && is_readable($filename))
		{
			require_once $filename;
		}

		// Checks that we have the config array
		if (isset($config))
		{
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

					// If we're on the CLI, check an environment was even passed in
					if (IS_CLI == true && $_SERVER['argc'] < 2)
					{
						throw new Exception('You must pass an environment (e.g. php script.php <environment>)');
					}

					// Loops through the environments and tries to match on IP or name
					foreach ($config['environments'] as $name => $hosts)
					{
						if (!is_array($hosts))
						{
							$hosts = array($hosts);
						}

						// Tries to determine the environment name
						foreach ($hosts as $host)
						{
							if (IS_CLI == true)
							{
								// Checks the first argument on the command line
								if ($_SERVER['argv'][1] == $name)
								{
									$environment = $name;
									break;
								}
							}
							else
							{
								// Exact match
								if ((preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host)
									&& $_SERVER['SERVER_ADDR'] == $host)
									|| $_SERVER['HTTP_HOST'] == $host)
								{
									$environment = $name;
									break;
								}
								// Fuzzy match
								elseif (substr($host,0,1) == '/' && (preg_match($host, $_SERVER['SERVER_NAME'], $matches) > 0 || preg_match($host, $_SERVER['HTTP_HOST'], $matches) > 0))
								{
									$environments[$name]           = $matches[0];
									$environment                   = $name;
									$config['environments'][$name] = $matches[0];
									break;
								}
							}
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

			// Defaults profiler to true if it doesn't match an option exactly
			if (isset($this->data['pickles']['profiler']))
			{
				if ($this->data['pickles']['profiler'] !== true)
				{
					// If we have an array convert to a string
					if (is_array($this->data['pickles']['profiler']))
					{
						$this->data['pickles']['profiler'] = implode(',', $this->data['pickles']['profiler']);
					}

					// Checks that one of our known values exists, if not, force true
					if (preg_match('/(objects|timers|queries|explains)/', $this->data['pickles']['profiler'] == false))
					{
						$this->data['pickles']['profiler'] = true;
					}
				}
			}
			else
			{
				$this->data['pickles']['profiler'] = false;
			}

			// Defaults expected PICKLES options to false
			foreach (array('cache', 'logging', 'minify') as $variable)
			{
				if (!isset($this->data['pickles'][$variable]))
				{
					$this->data['pickles'][$variable] = false;
				}
			}

			// Creates constants for the security levels
			if (isset($this->data['security']['levels']) && is_array($this->data['security']['levels']))
			{
				foreach ($this->data['security']['levels'] as $value => $name)
				{
					$constant = 'SECURITY_LEVEL_' . strtoupper($name);

					// Checks if constant is already defined, and throws an error
					if (defined($constant))
					{
						throw new Exception('The constant ' . $constant . ' is already defined');
					}
					else
					{
						define($constant, $value);
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Flatten
	 *
	 * Flattens the configuration array around the specified environment.
	 *
	 * @param  string $environment selected environment
	 * @param  array $array configuration error to flatten
	 * @return array flattened configuration array
	 */
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
