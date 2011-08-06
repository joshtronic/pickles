<?php

/**
 * Configuration Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
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
	public function __construct($filename = null)
	{
		parent::__construct();

		// Try to fine the configuration
		if ($filename == null)
		{
			$filename = 'config.php';
			$loaded   = false;
			$cwd      = getcwd();

			while ($loaded == false)
			{
				chdir(dirname($filename));

				if (getcwd() == '/')
				{
					throw new Exception('Unable to load configuration.');
				}

				chdir($cwd);

				$filename = '../' . $filename;
				$loaded   = $this->load($filename);
			}
		}
		else
		{
			$this->load($filename);
		}
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
								if ((preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host)
									&& $_SERVER['SERVER_ADDR'] == $host)
									|| $_SERVER['HTTP_HOST'] == $host)
								{
									// Sets the environment and makes a run for it
									$environment = $name;
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

			// Defaults logging to false if it doesn't exist
			if (!isset($this->data['pickles']['logging']))
			{
				$this->data['pickles']['logging'] = false;
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

/**
 * Single Entry Controller
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Controller Class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads modules, serves up user authentication when the
 * module asks for it, and loads the viewer that the module requested. Default
 * values are present to make things easier on the user.
 *
 * @usage <code>new Controller($config);</code>
 */
class Controller extends Object
{
	/**
	 * Pass Thru
	 *
	 * Whether or not the page being loaded is simple a pass thru for an
	 * internal PICKLES file. The point of this variable is to suppress the
	 * profiler report in the destructor.
	 *
	 * @access private
	 * @var    boolean
	 */
	private $passthru = false;

	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic is
	 * executed automatically via use of a constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (isset($_REQUEST['request']))
		{
			// Catches requests that aren't lowercase
			$lowercase_reqest = strtolower($_REQUEST['request']);
			if ($_REQUEST['request'] != $lowercase_reqest)
			{
				header('Location: ' . substr_replace($_SERVER['REQUEST_URI'], $lowercase_reqest, 1, strlen($lowercase_reqest)));
				exit;
			}

			// Catches requests to PICKLES core files and passes them through
			if (preg_match('/^__pickles\/(css|js)\/.+$/', $_REQUEST['request'], $matches))
			{
				// Checks that the file exists
				$file = str_replace('__pickles/', PICKLES_PATH, $_REQUEST['request']);
				if (file_exists($file))
				{
					// Sets the pass thru flag and dumps the data
					$this->passthru = true;

					// This is somewhat hacky, but mime_content_type() is deprecated and finfo_file() is only 5.3+
					header('Content-Type: text/' . ($matches[1] == 'js' ? 'javascript' : $matches[1]));

					exit(file_get_contents($file));
				}
			}
			// Catches requests to the __shared directory
			elseif (preg_match('/^__shared/', $_REQUEST['request']))
			{
				header('Location: /');
				exit;
			}
		}

		// Generate a generic "site down" message if the site is set to be disabled
		if (isset($this->config->pickles['disabled']) && $this->config->pickles['disabled'] == true)
		{
			Error::fatal($_SERVER['SERVER_NAME'] . ' is currently<br />down for maintenance');
		}

		// Checks the passed request for validity
		if (isset($_REQUEST['request']) && trim($_REQUEST['request']) != '')
		{
			$request = $_REQUEST['request'];
		}
		// Loads the default module information if we don't have a valid request
		else
		{
			$request = isset($this->config->pickles['module']) ? $this->config->pickles['module'] : 'home';
		}

		// Loads the module's information
		list($module_class, $module_filename, $template_basename, $css_class, $js_basename) = $this->prepareVariables($request);

		unset($request);

		$module_exists = (isset($module_filename) && $module_filename != null && file_exists($module_filename));

		// Instantiates an instance of the module
		if ($module_exists)
		{
			require_once $module_filename;

			// Checks that our class exists
			if (class_exists($module_class))
			{
				$module = new $module_class;
			}
			else
			{
				if ($this->config->pickles['logging'] === true)
				{
					Log::warning('Class named ' . $module_class . ' was not found in ' . $module_filename);
				}
			}
		}

		// If a new module object wasn't created, create a generic one
		if (!isset($module))
		{
			$module = new Module();
		}

		// Determines if the module is private and should be, well, private
		if ($module->private == true)
		{
			header('Location: /');
			exit;
		}

		// Determines if we need to serve over HTTP or HTTPS
		if ($module->secure == false && isset($_SERVER['HTTPS']))
		{
			header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}
		elseif ($module->secure == true && !isset($_SERVER['HTTPS']))
		{
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}

		// Establishes the session
		if (ini_get('session.auto_start') == 0)
		{
			if ($module->session)
			{
				if (session_id() == '')
				{
					session_start();
				}
			}
		}

		// Validates security level
		if ($module->security !== false)
		{
			$is_authenticated = false;

			if (is_array($module->security))
			{
				$module_security      = $module->security;
				$security_check_class = 'isLevel';

				// Checks the type and validates it
				if (isset($module_security['type']))
				{
					$security_check_type = strtoupper($module_security['type']);

					if (in_array($security_check_type, array('IS', 'HAS', 'BETWEEN')))
					{
						$security_check_class = $security_check_type;
					}

					unset($security_check_type, $module_security['type']);
				}

				$module_security_levels = array();

				// If there's a level(s) key use it
				foreach (array('level', 'levels') as $security_level_key)
				{
					if (isset($module_security[$security_level_key]))
					{
						if (is_array($module_security[$security_level_key]))
						{
							array_merge($module_security_levels, $module_security[$security_level_key]);
						}
						else
						{
							$module_security_levels[] = $module_security[$security_level_key];
						}

						unset($module_security[$security_level_key]);
					}
				}

				// Assume everything left in the array is a level and add it to the array
				array_merge($module_security_levels, $module_security);

				$security_level_count = count($module_security_levels);

				switch ($security_check_class)
				{
					case 'BETWEEN':
						if ($security_level_count >= 2)
						{
							$is_authenticated = Security::betweenLevel($module_security_levels[0], array_pop($module_security_levels));
						}
						break;

					case 'HAS':
						if ($security_level_count > 0)
						{
							$is_authenticated = Security::hasLevel($module_security_levels);
						}
						break;

					case 'IS':
						if ($security_level_count > 0)
						{
							$is_authenticated = Security::isLevel($module_security_levels);
						}
						break;
				}
			}
			else
			{
				$is_authenticated = Security::isLevel($module->security);
			}

			if ($is_authenticated == false)
			{
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					exit('{ "status": "error", "message": "You are not properly authenticated" }');
				}
				else
				{
					// Sets variable for the destination
					$_SESSION['__pickles']['login']['destination'] = isset($_REQUEST['request']) ? $_REQUEST['request'] : '/';

					// Redirect to login page, potentially configured in the config, else /login
					header('Location: /' . (isset($this->config->security['login']) ? $this->config->security['login'] : 'login'));

					exit;
				}
			}
		}

		// Validates the rendering engine
		$engines = is_array($module->engine) ? array_values($module->engine) : array($module->engine);
		$engines = array_combine($engines, $engines);
		$engine  = current($engines);

		// Possibly overrides the engine with the passed return type
		if (isset($return_type))
		{
			$return_type = strtoupper($return_type);

			// Validates the return type against the module
			if (in_array($return_type, array('JSON', 'RSS', 'XML')) && in_array($return_type, $engines))
			{
				$engine = $return_type;
			}

			unset($return_type);
		}

		// Starts up the display engine
		$display_class = 'Display_' . $engine;
		$display       = new $display_class();

		// Assigns the template / template variables
		$display->setTemplateVariables($module->template, $template_basename, $css_class, $js_basename);

		// Checks the templates
		$template_exists = $display->templateExists();

		// If there is no valid module or template, then redirect
		if (!$module_exists && !$template_exists)
		{
			if (!isset($_REQUEST['request']))
			{
				Error::fatal('Way to go, you\'ve successfully created an infinite redirect loop. Good thing I was here or you would have been served with a pretty ugly browser error.<br /><br />So here\'s the deal, no templates were able to be loaded. Make sure your parent and child templates actually exist and if you\'re using non-default values, make sure they\'re defined correctly in your config.');
			}
			else
			{
				$redirect_url = '/';

				if (isset($this->config->pickles['404']) && $_REQUEST['request'] != $this->config->pickles['404'])
				{
					$redirect_url .= $this->config->pickles['404'];
				}

				header('Location: ' . $redirect_url, 404);
				exit;
			}
		}

		// Gets the profiler status
		$profiler = $this->config->pickles['profiler'];

		// Attempts to execute the default method
		if (method_exists($module, '__default'))
		{
			if (isset($requested_id))
			{
				$module->setRequest(array('id' => $requested_id));
			}

			// Sets meta data from the module
			$display->setMetaData(array(
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			));

			// Starts a timer before the module is executed
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module __default');
			}

			$valid_request       = false;
			$valid_security_hash = false;
			$error_message       = 'An unexpected error has occurred';

			// Determines if the request method is valid for this request
			if ($module->method != false)
			{
				$methods = (is_array($module->method) ? $module->method : array($module->method));

				$request_method = $_SERVER['REQUEST_METHOD'];

				foreach ($methods as $method)
				{
					if ($request_method == strtoupper($method))
					{
						$valid_request = true;
						break;
					}
				}

				if ($valid_request == false)
				{
					$error_message = 'There was a problem with your request method';
				}

				unset($methods, $request_method, $method);
			}
			else
			{
				$valid_request = true;
			}

			// Validates the hash if applicable
			if ($module->hash != false)
			{
				if (isset($_REQUEST['security_hash']))
				{
					$hash_value = ($module->hash === true ? get_class($module) : $module->hash);

					if (Security::generateHash($hash_value) == $_REQUEST['security_hash'])
					{
						$valid_security_hash = true;
					}
					else
					{
						$error_message = 'Invalid security hash';
					}

					unset($hash_value);
				}
				else
				{
					$error_message = 'Missing security hash';
				}
			}
			else
			{
				$valid_security_hash = true;
			}

			/**
			 * Note to Self: When building in caching will need to let the
			 * module know to use the cache, either passing in a variable
			 * or setting it on the object
			 */
			$display->setModuleReturn($valid_request && $valid_security_hash ? $module->__default() : array('status' => 'error', 'message' => $error_message));

			unset($error_message);

			// Stops the module timer
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module __default');
			}
		}

		// Starts a timer for the display rendering
		if ($profiler === true || stripos($profiler, 'timers') !== false)
		{
			Profiler::timer('display render');
		}

		// Renders the content
		$display->render();

		// Steps the display timer
		if ($profiler === true || stripos($profiler, 'timers') !== false)
		{
			Profiler::timer('display render');
		}
	}

	/**
	 * Destructor
	 *
	 * Dumps out the Profiler's report if applicable.
	 */
	public function __destruct()
	{
		parent::__destruct();

		// Display the Profiler's report is the stars are aligned
		if ($this->config->pickles['profiler'] != false && $this->passthru == false)
		{
			Profiler::report();
		}
	}

	/**
	 * Prepare Variables
	 *
	 * Processes the request variable and creates all the variables that the
	 * Controller needs to load the page.
	 *
	 * @param  string $basename the requested page
	 * @return array the resulting variables
	 */
	public function prepareVariables($basename)
	{
		// Sets up all of our variables
		$module_class      = strtr($basename, '/', '_');
		$module_filename   = SITE_MODULE_PATH . $basename . '.php';
		$template_basename = $basename;
		$css_class         = $module_class;
		$js_basename       = $basename;

		// Scrubs class names with hyphens
		if (strpos($module_class, '-') !== false)
		{
			$module_class = preg_replace('/(-(.{1}))/e', 'strtoupper("$2")', $module_class);
		}

		return array($module_class, $module_filename, $template_basename, $css_class, $js_basename);
	}
}

/**
 * Converter
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Convert Class
 *
 * Collection of statically called methods to help aid in converting formats.
 */
class Convert
{
	/**
	 * To JSON
	 *
	 * Encodes passed variable as JSON.
	 *
	 * Requires PHP 5 >= 5.2.0 or PECL json >= 1.2.0
	 * Note: PECL json 1.2.1 is included /vendors
	 *
	 * @link http://json.org/
	 * @link http://us.php.net/json_encode
	 * @link http://pecl.php.net/package/json
	 *
	 * @static
	 * @param  mixed $variable variable to convert
	 * @return JSON encoded string
	 */
	public static function toJSON($variable)
	{
		if (JSON_AVAILABLE)
		{
			return json_encode($variable);
        }
		else
		{
            return '{ "status": "error", "message": "json_encode() not found" }';
        }
	}

	/**
	 * Array to XML
	 *
	 * Converts an array into XML tags (recursive). This method expects the
	 * passed array to be formatted very specifically to accomodate the fact
	 * that an array's format isn't quite the same as well-formed XML.
	 *
	 * Input Array =
	 *     array('children' => array(
	 *         'child' => array(
	 *             array('name' => 'Wendy Darling'),
	 *             array('name' => 'John Darling'),
	 *             array('name' => 'Michael Darling')
	 *         )
	 *     ))
	 *
	 * Output XML =
	 *     <children>
	 *         <child><name>Wendy Darling</name></child>
	 *         <child><name>John Darling</name></child>
	 *         <child><name>Michael Darling</name></child>
	 *     </children>
	 *
	 * @static
	 * @param  array $array array to convert into XML
	 * @return string generated XML
	 */
	public static function arrayToXML($array, $format = false, $level = 0)
	{
		$xml = '';

		if (is_array($array))
		{
			foreach ($array as $node => $value)
			{
				// Checks if the value is an array
				if (is_array($value))
				{
					foreach ($value as $node2 => $value2)
					{
						if (is_array($value2))
						{
							// Nest the value if the node is an integer
							$new_value = (is_int($node2) ? $value2 : array($node2 => $value2));

							$xml .= ($format ? str_repeat("\t", $level) : '');
							$xml .= '<' . $node . '>' . ($format ? "\n" : '');
							$xml .= self::arrayToXML($new_value, $format, $level + 1);
							$xml .= ($format ? str_repeat("\t", $level) : '');
							$xml .= '</' . $node . '>' . ($format ? "\n" : '');
						}
						else
						{
							if (is_int($node2))
							{
								$node2 = $node;
							}

							// Checks for special characters
							if (htmlspecialchars($value2) != $value2)
							{
								$xml .= ($format ? str_repeat("\t", $level) : '');
								$xml .= '<' . $node2 . '><![CDATA[' . $value2 . ']]></' . $node2 . '>' . ($format ? "\n" : '');
							}
							else
							{
								$xml .= ($format ? str_repeat("\t", $level) : '');
								$xml .= '<' . $node2 . '>' . $value2 . '</' . $node2 . '>' . ($format ? "\n" : '');
							}
						}
					}
				}
				else
				{
					// Checks for special characters
					if (htmlspecialchars($value) != $value)
					{
						$xml .= ($format ? str_repeat("\t", $level) : '');
						$xml .= '<' . $node . '><![CDATA[' . $value . ']]></' . $node . '>' . ($format ? "\n" : '');
					}
					else
					{
						$xml .= ($format ? str_repeat("\t", $level) : '');
						$xml .= '<' . $node . '>' . $value . '</' . $node . '>' . ($format ? "\n" : '');
					}
				}
			}
		}

		return $xml;
	}
}

/**
 * Database Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Database Factory
 *
 * Generic class to simplify connecting to a database. All database objects
 * should be created by this class to future proof against any internal changes
 * to PICKLES.
 */
class Database extends Object
{
	/**
	 * Constructor
	 *
	 * Attempts to get an instance of the passed database type or attempts to
	 * use a default specified in the config.
	 *
	 * @param string $name optional name of the connection to use
	 */
	public function __construct(String $name = null)
	{
		parent::__construct();

		return Database::getInstance($name);
	}

	/**
	 * Get instance
	 *
	 * Looks up the datasource using the passed name and gets an instance of
	 * it. Allows for easy sharing of certain classes within the system to
	 * avoid the extra overhead of creating new objects each time. Also avoids
	 * the hassle of passing around variables (yeah I know, very global-ish)
	 *
	 * @static
	 * @param  string $name name of the datasource
	 * @return object instance of the class
	 */
	public static function getInstance($name = null)
	{
		$config = Config::getInstance();

		// Checks if we have a default
		if ($name == null)
		{
			// Checks the config for a default
			if (isset($config->pickles['datasource']))
			{
				$name = $config->pickles['datasource'];
			}
			// Tries to use the first defined datasource
			elseif (is_array($config->datasources))
			{
				$datasources = $config->datasources;
				$name        = key($datasources);
			}
		}

		// If we have a name try to set up a connection
		if ($name != null)
		{
			if (isset($config->datasources[$name]))
			{
				$datasource = $config->datasources[$name];

				$datasource['driver'] = strtolower($datasource['driver']);

				if (!isset(self::$instances['Database'][$name]))
				{
					// Checks the driver is legit and scrubs the name
					switch ($datasource['driver'])
					{
						case 'mongo':      $class = 'Mongo';          break;
						case 'pdo_mysql':  $class = 'PDO_MySQL';      break;
						case 'pdo_pgsql':  $class = 'PDO_PostgreSQL'; break;
						case 'pdo_sqlite': $class = 'PDO_SQLite';     break;

						default:
							throw new Exception('Datasource driver "' . $datasource['driver'] . '" is invalid');
							break;
					}

					// Instantiates our database class
					$class    = 'Database_' . $class;
					$instance = new $class();

					// Sets our database parameters
					if (isset($datasource['hostname']))
					{
						$instance->setHostname($datasource['hostname']);
					}

					if (isset($datasource['port']))
					{
						$instance->setPort($datasource['port']);
					}

					if (isset($datasource['socket']))
					{
						$instance->setSocket($datasource['socket']);
					}

					if (isset($datasource['username']))
					{
						$instance->setUsername($datasource['username']);
					}

					if (isset($datasource['password']))
					{
						$instance->setPassword($datasource['password']);
					}

					if (isset($datasource['database']))
					{
						$instance->setDatabase($datasource['database']);
					}
				}

				// Caches the instance for possible reuse later
				if (isset($instance))
				{
					self::$instances['Database'][$name] = $instance;
				}

				// Returns the instance
				return self::$instances['Database'][$name];
			}
		}

		return false;
	}
}

/**
 * Date Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Date Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant date related manipulation.
 */
class Date
{
	// {{{ Age

	/**
	 * Age
	 *
	 * Calculates age based on the passed date.
	 *
	 * @static
	 * @param  string $date birth / inception date
	 * @return integer $age number of years old
	 */
	public static function age($date) 
	{
		if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date))
		{
			$date = date('Y-m-d', strtotime($date));
		}

		list($year, $month, $day) = explode('-', $date, 3);

		$age = date('Y') - $year;

		if (date('md') < $month . $day)
		{
			$age--;
		}

		return $age;
	}

	// }}}
}

/**
 * Dynamic Content Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Dynamic Class
 *
 * Handles generating links to static content that are a timestamp injected as
 * to avoid hard caching. Also minifies content where applicable.
 *
 * Note: you will want to add a mod_rewrite line to your .htaccess to support
 * the routing to the filenames with the timestamp injected:
 *
 * RewriteRule ^(.+)\.([\d]+)\.(css|js|gif|png|jpg|jpeg)$ /$1.$3 [NC,QSA]
 */
class Dynamic extends Object
{
	/**
	 * Generate Reference
	 *
	 * Appends a dynamic piece of information to the passed reference in the
	 * form of a UNIX timestamp added to the query string.
	 *
	 * @param  string $reference URI reference of the file
	 * @param  string $failover URI reference to use if the reference can't be found
	 * @return string URI reference reference with dynamic content
	 */
	public function reference($reference, $failover = false)
	{
		// Checks if the URI reference is absolute, and not relative
		if (substr($reference, 0, 1) == '/')
		{
			// Checks if we're working with an internal PICKLES file
			$is_internal = preg_match('/^\/__pickles\/(css|js)\/.+$/', $reference);

			$query_string = '';

			// Checks for ? and extracts query string
			if (strstr($reference, '?'))
			{
				list($reference, $query_string) = explode('?', $reference);
			}

			if ($is_internal)
			{
				// Sets the path to the actual internal path
				$file = str_replace('/__pickles/', PICKLES_PATH, $reference);
			}
			else
			{
				// Adds the dot so the file functions can find the file
				$file = '.' . $reference;
			}

			if (file_exists($file))
			{
				// Replaces the extension with time().extension
				$parts = explode('.', $reference);

				if (count($parts) == 1)
				{
					throw new Exception('Filename must have an extension (e.g. /path/to/file.png)');
				}
				else
				{
					end($parts);
					$parts[key($parts)] = filemtime($file) . '.' . current($parts);
					$reference = implode('.', $parts);
				}

				// Adds the query string back
				if ($query_string != '')
				{
					$reference .= '?' . $query_string;
				}
			}
			else
			{
				if ($failover != false)
				{
					$reference = $failover;
				}
				else
				{
					throw new Exception('Supplied reference does not exist');
				}
			}
		}
		else
		{
			throw new Exception('Reference value must be absolute (e.g. /path/to/file.png)');
		}

		return $reference;
	}

	/**
	 * Generate Stylesheet Reference
	 *
	 * Attempts to minify the stylesheet and then  returns the reference URI
	 * for the file, minified or not.
	 *
	 * @param  string $reference URI reference of the Stylesheet
	 * @return string URI reference reference with dynamic content
	 */
	public function css($original_reference)
	{
		if (preg_match('/^\/__pickles\/css\/.+$/', $original_reference) == false)
		{
			// Injects .min into the filename
			$parts = explode('.', $original_reference);

			if (count($parts) == 1)
			{
				throw new Exception('Filename must have an extension (e.g. /path/to/file.css)');
			}
			else
			{
				end($parts);
				$parts[key($parts)] = 'min.' . current($parts);
				$minified_reference = implode('.', $parts);
			}

			$original_filename = '.' . $original_reference;
			$minified_filename = '.' . $minified_reference;

			$path = dirname($original_filename);

			if (file_exists($original_filename))
			{
				$reference = $original_reference;

				if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)))
				{
					// Minifies CSS with a few basic character replacements.
					$stylesheet = file_get_contents($original_filename);
					$stylesheet = str_replace(array("\t", "\n", ', ', ' {', ': ', ';}'), array('', '', ',', '{', ':', '}'), $stylesheet);
					$stylesheet = preg_replace('/\/\*.+?\*\//', '', $stylesheet);
					file_put_contents($minified_filename, $stylesheet);

					$reference = $minified_reference;
				}
				elseif (file_exists($minified_filename))
				{
					$reference = $minified_reference;
				}
				else
				{
					if ($this->config->pickles['logging'] === true)
					{
						Log::warning('Unable to minify ' . $original_reference . ' and a minified copy does not already exist');
					}
				}

				$reference = $this->reference($reference);
			}
			else
			{
				throw new Exception('Supplied reference does not exist');
			}
		}
		else
		{
			$reference = $this->reference($original_reference);
		}

		return $reference;
	}

	/**
	 * Generate Javascript Reference
	 *
	 * Attempts to minify the source with Google's Closure compiler, and then
	 * returns the reference URI for the file, minified or not.
	 *
	 * @link   http://code.google.com/closure/compiler/
	 * @param  string $reference URI reference of the Javascript file
	 * @return string URI reference reference with dynamic content
	 */
	public function js($original_reference, $level = 'simple')
	{
		if (preg_match('/^\/__pickles\/js\/.+$/', $original_reference) == false)
		{
			$level = strtoupper($level);

			switch ($level)
			{
				CASE 'WHITESPACE':
				CASE 'SIMPLE':
				CASE 'ADVANCED':
					// Injects .min into the filename
					$parts = explode('.', $original_reference);

					if (count($parts) == 1)
					{
						throw new Exception('Filename must have an extension (e.g. /path/to/file.js)');
					}
					else
					{
						end($parts);
						$parts[key($parts)] = 'min.' . current($parts);
						$minified_reference = implode('.', $parts);
					}

					$original_filename = '.' . $original_reference;
					$minified_filename = '.' . $minified_reference;

					$path = dirname($original_filename);

					if (file_exists($original_filename))
					{
						$reference = $original_reference;

						if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)) && extension_loaded('curl'))
						{
							// Sets up the options list
							$options = array(
								CURLOPT_URL             => 'http://closure-compiler.appspot.com/compile',
								CURLOPT_RETURNTRANSFER  => true,
								CURLOPT_HTTPHEADER      => array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'),
								CURLOPT_POST            => true,
								CURLOPT_POSTFIELDS      => 'js_code=' . urlencode(file_get_contents($original_filename)) . '&compilation_level=' . ($level . '_' . ($level == 'WHITESPACE' ? 'ONLY' : 'OPTIMIZATIONS')) . '&output_format=text&output_info=compiled_code'
							);

							try
							{
								// Executes the request
								$curl = curl_init();
								curl_setopt_array($curl, $options);
								file_put_contents($minified_filename, curl_exec($curl));
								curl_close($curl);

								$reference = $minified_reference;
							}
							catch (Exception $exception)
							{
								$reference = $original_reference;
							}
						}
						elseif (file_exists($minified_filename))
						{
							$reference = $minified_reference;
						}
						else
						{
							if ($this->config->pickles['logging'] === true)
							{
								Log::warning('Unable to minify ' . $original_reference . ' and a minified copy does not already exist');
							}
						}

						$reference = $this->reference($reference);
					}
					else
					{
						throw new Exception('Supplied reference does not exist');
					}

					break;

				default:
					throw new Exception('The level "' . $level . '" is invalid. Valid levels include "whitespace", "simple" and "advanced"');
					break;
			}
		}
		else
		{
			$reference = $this->reference($original_reference);
		}

		return $reference;
	}
}

/**
 * Error Reporting for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Error Class
 *
 * Standardized error reporting, mostly used to display fatal errors.
 */
class Error
{
	/**
	 * Fatal Error
	 *
	 * Displays a friendly error to the user via HTML, logs it then exits.
	 *
	 * @static
	 * @param  string $message the message to be displayed to the user
	 */
	public static function fatal($message)
	{
		if ($this->config->pickles['logging'] === true)
		{
			if (Log::error($message) == false)
			{
				$message .= '<br /><br />This error message could not be logged as the log path or log file is not writable';
			}
		}
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title><?php echo $_SERVER['SERVER_NAME']; ?> - error</title>
				<style>
					html
					{
						background: #eee;
						font-family: "Lucida Sans", "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, sans-serif;
						width: 100%;
						height: 100%;
						font-size: 1em;
					}
					body
					{
						text-align: center;
						margin-top: 100px;
					}
					div
					{
						font-size: 150%;
						color: #600;
						text-shadow: 2px 2px 2px #eb8383;
						margin: 0;
						font-weight: bold;
						background: #ff9c9c;
						padding: 20px;
						border-radius: 20px;
						-moz-border-radius: 20px;
						-webkit-border-radius: 20px;
						width: 550px;
						margin: 0 auto;
						border: 3px solid #890f0f;
					}
					h1, a
					{
						font-size: 70%;
						color: #999;
						text-decoration: none;
					}
					a:hover
					{
						color: #000;
					}
				</style>
			</head>
			<body>
				<h1><?php echo $_SERVER['SERVER_NAME']; ?></h1>
				<div><?php echo $message; ?></div>
				<a href="http://p.ickl.es" target="_blank">Powered by PICKLES</a>
			</body>
		</html>
		<?php

		exit;
	}

}

/**
 * File Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * File Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant file related manipulation.
 */
class File
{
	/**
	 * Remove a Directory, Recursively
	 *
	 * Removes a directory by emptying all of the contents recursively and then
	 * removing the directory, as PHP will not let you rmdir() on ain non-empty
	 * directory. Use with caution, seriously.
	 *
	 * @static
	 * @param  string $directory directory to remove
	 * @return boolean status of the final rmdir();
	 */
	public static function removeDirectory($directory)
	{
		if (substr($directory, -1) != '/')
		{
			$directory .= '/';
		}

		// If directory is a directory, read in all the files
		if (is_dir($directory))
		{
			$files = scandir($directory);
			
			// Loop through said files, check for directories, and unlink files
			foreach ($files as $file)
			{
				if (!in_array($file, array('.', '..')))
				{
					if (is_dir($directory . $file))
					{
						File::removeDirectory($directory . $file);
					}
					else
					{
						unlink($directory . $file);
					}
				}
			}
		}

		rmdir($directory);
	}
}

/**
 * Form Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Form Class
 *
 * This class contains methods for easily generating form elements. There is a
 * heavy focus on select boxes as they have the most overhead for a developer.
 */
class Form extends Object
{
	// {{{ Get Instance

	/**
	 * Get Instance
	 *
	 * Gets an instance of the Form class
	 *
	 * @static
	 * @param  string $class name of the class to get an instance of
	 * @return object instance of the class
	 */
	public static function getInstance($class = 'Form')
	{
		return parent::getInstance($class);
	}

	// }}}
	// {{{ Input

	/**
	 * Input
	 *
	 * Generates an input with the passed data.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  string $type optional type of input
	 * @param  boolean $checked optional whether the input is checked
	 * @return string HTML for the input
	 */
	public function input($name, $value = '', $classes = '', $additional = null, $type = 'text', $checked = false)
	{
		if ($additional != null)
		{
			$additional = ' ' . $additional;
		}

		if (in_array($type, array('checkbox', 'radio')) && $checked == true)
		{
			$additional .= ' checked="checked"';
		}

		return '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" value="' . $value . '" class="' . $classes . '"' . $additional . ' />' . "\n";
	}

	// }}}
	// {{{ Hidden

	/**
	 * Hidden
	 *
	 * Shorthand method to generate a hidden input.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function hidden($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'hidden');
	}
	
	/**
	 * Hidden Input
	 *
	 * Shorthand method to generate a hidden input.
	 *
	 * @deprecated Use hidden() instead
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function hiddenInput($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'hidden');
	}

	// }}}
	// {{{ Password

	/**
	 * Password
	 *
	 * Shorthand method to generate a password input.
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function password($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'password');
	}

	/**
	 * Password Input
	 *
	 * Shorthand method to generate a password input.
	 *
	 * @deprecated Use password() instead
	 *
	 * @param  string $name name (and ID) for the element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function passwordInput($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'password');
	}

	// }}}
	// {{{ Submit

	/**
	 * Submit
	 *
	 * Shorthand method to generate a submit input (button).
	 *
	 * @param  string $name name (and ID) for the input element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function submit($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'submit');
	}

	/**
	 * Submit Input
	 *
	 * Shorthand method to generate a submit input (button).
	 *
	 * @deprecated Use submit() instead
	 *
	 * @param  string $name name (and ID) for the input element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function submitInput($name, $value = '', $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'submit');
	}

	// }}}
	// {{{ Security

	/**
	 * Security
	 *
	 * Generates a hidden input with an SHA1 hash as the value. The name of the
	 * field is cannot be changed as this method was only intended for use with
	 * forms that are submitted via AJAX to provide better security.
	 *
	 * @param  string $value value to hash
	 * @return string HTML for the input
	 */
	public function security($value)
	{
		// Returns the hidden input
		return $this->hiddenInput('security_hash', Security::generateHash($value));
	}

	/**
	 * Security Input
	 *
	 * Generates a hidden input with an SHA1 hash as the value. The name of the
	 * field is cannot be changed as this method was only intended for use with
	 * forms that are submitted via AJAX to provide better security.
	 *
	 * @deprecated Use security() instead
	 *
	 * @param  string $value value to hash
	 * @return string HTML for the input
	 */
	public function securityInput($value)
	{
		// Returns the hidden input
		return $this->hiddenInput('security_hash', Security::generateHash($value));
	}

	// }}}
	// {{{ Checkbox

	/**
	 * Checkbox
	 *
	 * Generates a checkbox input with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  boolean $checked optional whether the checkbox is checked
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function checkbox($name, $value = '', $checked = false, $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'checkbox', $checked);
	}

	// }}}
	// {{{ Checkboxes

	// @todo

	// }}}
	// {{{ Radio Button

	/**
	 * Radio Button
	 *
	 * Generates a radio input with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  boolean $checked optional whether the checkbox is checked
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the input
	 */
	public function radio($name, $value = '', $checked = false, $classes = '', $additional = null)
	{
		return $this->input($name, $value, $classes, $additional, 'radio', $checked);
	}

	// }}}
	// {{{ Radio Buttons

	// @todo

	// }}}
	// {{{ Text Area

	/**
	 * Textarea
	 *
	 * Generates a textarea with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  string $value optional preset value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  string $type optional type of input
	 * @return string HTML for the input
	 */
	public function textarea($name, $value = '', $classes = '', $additional = null)
	{
		if ($additional != null)
		{
			$additional = ' ' . $additional;
		}

		return '<textarea name="' . $name . '" id="' . $name . '" class="' . $classes . '"' . $additional . '>' . $value . '</textarea>' . "\n";
	}

	// }}}
	// {{{ Select

	/**
	 * Select
	 *
	 * Generates a select box with the passed data.
	 *
	 * @param  string $name name (and ID) for the select element
	 * @param  array $options key/values for the option elements
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select box
	 */
	public function select($name, $options, $selected = null, $classes = '', $additional = null)
	{
		if ($additional != null)
		{
			$additional = ' ' . $additional;
		}

		return '<select id="' . $name . '" name="' . $name . '" class="' . $classes . '"' . $additional . '>' . $this->options($options, $selected) . '</select>' . "\n";
	}

	// }}}
	// {{{ Options

	/**
	 * Options
	 *
	 * Generates the option elements from the passed array
	 *
	 * @param  array $options key/values for the options
	 * @param  string $selected optional default option
	 * @return string HTML for the options
	 */
	public function options($options, $selected = null)
	{
		$found_selected = false;
		$options_html   = '';

		if (is_array($options))
		{
			foreach ($options as $main_key => $main_label)
			{
				if (is_array($main_label))
				{
					$options_html .= '<optgroup label="' . addslashes($main_key) . '">';

					foreach ($main_label as $sub_key => $sub_label)
					{
						$selected_attribute = false;
						if ($selected !== null && $found_selected === false)
						{
							if ($selected == $sub_key)
							{
								$selected_attribute = ' selected="selected"';
								$found_selected     = true;
							}
						}

						$options_html .= '<option label="' . addslashes($sub_label) . '" value="' . $sub_key . '"' . $selected_attribute . '>' . $sub_label . '</option>';
					}

					$options_html .= '</optgroup>';
				}
				else
				{
					$selected_attribute = false;
					if ($selected !== null && $found_selected === false)
					{
						if ($selected == $main_key)
						{
							$selected_attribute = ' selected="selected"';
							$found_selected     = true;
						}
					}

					$options_html .= '<option label="' . addslashes($main_label) . '" value="' . $main_key . '"' . $selected_attribute . '>' . $main_label . '</option>';
				}
			}
		}

		if ($selected !== null && $found_selected === false)
		{
			$options_html .= '<option value="' . $selected . '" selected="selected" class="error">' . $selected . '</option>';
		}

		return $options_html;
	}

	// }}}
	// {{{ State Select

	/**
	 * State Select
	 *
	 * Generates a select box with the United States, Puerto Rico and miliary
	 * options
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select box
	 */
	public function stateSelect($name = 'state', $selected = null, $classes = '', $additional = null)
	{
		$options = array(
			null => '-- Select State --',
			'AK' => 'Alaska',
			'AL' => 'Alabama',
			'AS' => 'American Samoa',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'GU' => 'Guam',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MH' => 'Marshall Islands',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'MP' => 'Northern Mariana Islands',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PW' => 'Palau',
			'PA' => 'Pennsylvania',
			'PR' => 'Puerto Rico',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VI' => 'Virgin Islands',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
			'AE' => 'Armed Forces Africa',
			'AA' => 'Armed Forces Americas (except Canada)',
			'AE' => 'Armed Forces Canada',
			'AE' => 'Armed Forces Europe',
			'AE' => 'Armed Forces Middle East',
			'AP' => 'Armed Forces Pacific'
		);

		return $this->select($name, $options, $selected, $classes, $additional);
	}

	// }}}
	// {{{ Date Select

	/**
	 * Date Select
	 *
	 * Generates 3 select boxes (month, day, year)
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @param  integer $start_year optional first year to display
	 * @param  integer $end_year optional last year to display
	 * @return string HTML for the select boxes
	 */
	public function dateSelect($name = 'date', $selected = null, $classes = '', $additional = null, $start_year = null, $end_year = null)
	{
		$html = '';

		// Breaks apart the selected value if present
		if ($selected == null || $selected == '0000-00-00')
		{
			$selected_month = null;
			$selected_day   = null;
			$selected_year  = null;
		}
		else
		{
			list($selected_year, $selected_month, $selected_day) = explode('-', $selected);
		}

		$month_options = array(
			null => 'Month',
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
		);

		$day_options   = array(null => 'Day');
		$year_options  = array(null => 'Year');

		// Generates the list of days
		for ($i = 1; $i <= 31; ++$i)
		{
			$day_options[str_pad($i, 2, '0', STR_PAD_LEFT)] = $i;
		}

		// Generates the list of years
		$current_year = date('Y');
		$start_year   = $start_year == null ? $current_year - 10 : $start_year;
		$end_year     = $end_year   == null ? $current_year + 10 : $end_year;

		for ($i = $start_year; $i >= $end_year; --$i)
		{
			$year_options[$i] = $i;
		}

		// Loops through and generates the selects
		foreach (array('month', 'day', 'year') as $part)
		{
			$options  = $part . '_options';
			$selected = 'selected_' . $part;
			$html   .= ' ' . $this->select($name . '[' . $part . ']', $$options, $$selected, $classes, $additional);
		}

		return $html;
	}

	// }}}
	// {{{ Date of Birth Select

	/**
	 * Date of Birth Select
	 *
	 * Generates 3 select boxes (month, day, year)
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 * @return string HTML for the select boxes
	 */
	public function dobSelect($name = 'dob', $selected = null, $classes = '', $additional = null)
	{
		// Note: Start year based on oldest living person: http://en.wikipedia.org/wiki/Oldest_people as of November 2010
		// Note: Start and end year may seem backwards, but we want them in descending order when rendered
		return $this->dateSelect($name, $selected, $classes, $additional, date('Y'), 1896);
	}

	// }}}
	// {{{ Polar Select

	/**
	 * Polar Select
	 *
	 * Generates a polar (yes / no) select box.
	 *
	 * @param  string $name optional name (and ID) for the select element
	 * @param  string $selected optional selected option
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 */
	public function polarSelect($name = 'decision', $selected = 0, $classes = '', $additional = null)
	{
		$options = array(1 => 'Yes', 0 => 'No');

		return $this->select($name, $options, $selected, $classes, $additional);
	}

	// }}}
	// {{{ Phone Input

	/**
	 * Phone Input
	 *
	 * Generates 3 inputs for a phone number from the passed values.
	 *
	 * @param  string $name optional name (and ID) for the input elements
	 * @param  string $value optional existing value
	 * @param  string $classes optional class names
	 * @param  string $additional optional additional parameters
	 */
	public function phoneInput($name = 'phone', $value = null, $classes = '', $additional = null)
	{
		if ($value == null)
		{
			$value = array(
				'area_code'   => '',
				'prefix'      => '',
				'line_number' => ''
			);
		}
		else
		{
			$value = array(
				'area_code'   => substr($value, 0, 3),
				'prefix'      => substr($value, 3, 3),
				'line_number' => substr($value, 6)
			);
		}

		$parts = array(
			'area_code'   => 3,
			'prefix'      => 3,
			'line_number' => 4
		);

		if ($additional != null)
		{
			$additional = ' ' . $additional;
		}

		$html = '';
		foreach ($parts as $part => $size)
		{
			$html .= ($html != '' ? ' ' : '');
			$html .= '<input type="input" name="' . $name . '[' . $part . ']" id="' . $name . '[' . $part . ']" value="' . $value[$part] . '" minlength="' . $size . '" maxlength="' . $size . '" class="digits ' . $class . '"' . $additional . ' />';
		}

		return $html . "\n";
	}

	// }}}
}

/**
 * Logging System for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Log Class
 *
 * Standardized logging methods for ease of reporting.
 */
class Log
{
	/**
	 * Log Information
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function information($message)
	{
		return self::write('information', $message);
	}

	/**
	 * Log Warning
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function warning($message)
	{
		return self::write('warning', $message);
	}

	/**
	 * Log Error
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function error($message)
	{
		return self::write('error', $message);
	}

	/**
	 * Log Slow Query
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function slowQuery($message)
	{
		return self::write('slow_query', $message);
	}

	/**
	 * Log Credit Card Transaction
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function transaction($message)
	{
		return self::write('transaction', $message);
	}

	/**
	 * Log PHP Error
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function phpError($message, $time = false)
	{
		return self::write('php_error', $message, false, $time);
	}

	/**
	 * Log SQL Query
	 *
	 * @static
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	public static function query($message)
	{
		return self::write('query', $message);
	}

	/**
	 * Write Message to Log File
	 *
	 * @static
	 * @access private
	 * @param  string $message message to log
	 * @return boolean whether or not the write was successful
	 */
	private static function write($log_type, $message, $format = true, $time = false)
	{
		$log_path = LOG_PATH . date('Y/m/d/', ($time == false ? time() : $time));

		try
		{
			if (!file_exists($log_path))
			{
				mkdir($log_path, 0755, true);
			}

			$log_file = $log_path . $log_type . '.log';

			$message .= "\n";

			if ($format == true)
			{
				$backtrace = debug_backtrace();
				rsort($backtrace);
				$frame = $backtrace[strpos($backtrace[0]['file'], 'index.php') === false ? 0 : 1];

				return file_put_contents($log_file, date('H:i:s') . ' ' . str_replace(getcwd(), '', $frame['file']) . ':' . $frame['line'] . ' ' . $message, FILE_APPEND);
			}
			else
			{
				return file_put_contents($log_file, $message, FILE_APPEND);
			}
		}
		catch (ErrorException $exception)
		{
			return false;
		}
	}
}

/**
 * Model Parent Class for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Model Class
 *
 * This is a parent class that all PICKLES data models should be extending.
 * When using the class as designed, objects will function as active record
 * pattern objects.
 */
class Model extends Object
{
	// {{{ Properties

	/**
	 * Database Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * SQL Array
	 *
	 * @access private
	 * @var    array
	 */
	private $sql = array();

	/**
	 * Input Parameters Array
	 *
	 * @access private
	 * @var    array
	 */
	private $input_parameters = array();

	/**
	 * Datasource
	 *
	 * @access protected
	 * @var    string
	 */
	protected $datasource;

	/**
	 * Delayed Insert
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $delayed = false;

	/**
	 * Replace instead of Insert/Update?
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $replace = false;

	/**
	 * Field List
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $fields = '*'; // SELECT

	/**
	 * Table Name
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $table = false; // FROM

	/**
	 * ID Column
	 *
	 * @access protected
	 * @var    string
	 */
	protected $id = 'id'; // WHERE ___ = ?

	/**
	 * Collection Name
	 *
	 * For compatibility with the naming conventions used by MongoDB, the
	 * collection name can be specified. If the collection name is set, it will
	 * set the table name value to it and proceed as normal.
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $collection = false;

	/**
	 * Joins
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $joins = false; // JOIN

	/**
	 * [Index] Hints
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $hints = false; // USE INDEX

	/**
	 * Conditions
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $conditions = false; // WHERE

	/**
	 * Group
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $group  = false; // GROUP BY

	/**
	 * Having
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $having = false; // HAVING

	/**
	 * Order
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $order = false; // ORDER BY

	/**
	 * Limit
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $limit = false; // LIMIT

	/**
	 * Offset
	 *
	 * @access protected
	 * @var    mixed (string or array)
	 */
	protected $offset = false; // OFFSET

	/**
	 * Query Results
	 *
	 * @access protected
	 * @var    array
	 */
	protected $results = null;

	/**
	 * Index
	 *
	 * @var integer
	 */
	private $index = null;

	/**
	 * Record
	 *
	 * @access private
	 * @var    array
	 */
	public $record = null;

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = null;

	/**
	 * Original Record
	 *
	 * @access private
	 * @var    array
	 */
	private $original = null;

	/**
	 * Iterate
	 *
	 * Used to hold the status during a walk()
	 *
	 * @access private
	 * @var    boolean
	 */
	private $iterate = false;

	// }}}
	// {{{ Class Constructor

	/**
	 * Constructor
	 *
	 * Creates a new (empty) object or creates the record set from the passed
	 * arguments. The record and records arrays are populated as well as the
	 * count variable.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function __construct($type_or_parameters = null, $parameters = null)
	{
		// Runs the parent constructor so we have the config
		parent::__construct();

		// Gets an instance of the database
		$this->db = Database::getInstance($this->datasource != '' ? $this->datasource : null);

		// Builds out the query
		if ($type_or_parameters != null)
		{
			// Loads the parameters into the object
			if (is_array($type_or_parameters))
			{
				if (is_array($parameters))
				{
					throw new Exception('You cannot pass in 2 query parameter arrays');
				}

				$this->loadParameters($type_or_parameters);
			}
			elseif (is_array($parameters))
			{
				$this->loadParameters($parameters);
			}
			elseif (ctype_digit((string)$type_or_parameters))
			{
				$this->loadParameters(array($this->id => $type_or_parameters));
			}
			elseif (ctype_digit((string)$parameters))
			{
				$this->loadParameters(array($this->id => $parameters));
			}

			// Overwrites the table name with the available collection name
			if ($this->collection != false)
			{
				$this->table = $this->collection;
			}

			// If we're using an RDBMS (not Mongo) proceed with using SQL to pull the data
			if ($this->db->getDriver() != 'mongo')
			{
				// Starts with a basic SELECT ... FROM
				$this->sql = array(
					'SELECT ' . (is_array($this->fields) ? implode(', ', $this->fields) : $this->fields),
					'FROM '   . $this->table,
				);

				switch ($type_or_parameters)
				{
					// Updates query to use COUNT syntax
					case 'count':
						$this->sql[0] = 'SELECT COUNT(*) AS count';
						$this->generateQuery();
						break;

					// Adds the rest of the query
					case 'all':
					case 'list':
					case 'indexed':
					default:
						$this->generateQuery();
						break;
				}

				$this->records = $this->db->fetch(implode(' ', $this->sql), (count($this->input_parameters) == 0 ? null : $this->input_parameters));
			}
			else
			{
				throw new Exception('Sorry, Mongo support in the PICKLES Model is not quite ready yet');
			}

			$index_records = in_array($type_or_parameters, array('list', 'indexed'));

			// Flattens the data into a list
			if ($index_records == true)
			{
				$list = array();

				foreach ($this->records as $record)
				{
					// Users the first value as the key and the second as the value
					if ($type_or_parameters == 'list')
					{
						$list[array_shift($record)] = array_shift($record);
					}
					// Uses the first value as the key
					else
					{
						$list[current($record)] = $record;
					}
				}

				$this->records = $list;
			}

			// Sets up the current record
			if (isset($this->records[0]))
			{
				$this->record = $this->records[0];
			}
			else
			{
				if ($index_records == true)
				{
					$this->record[key($this->records)] = current($this->records);
				}
				else
				{
					$this->record = $this->records;
				}
			}

			$this->index    = 0;
			$this->original = $this->records;
		}

		return true;
	}

	// }}}
	// {{{ SQL Generation Methods

	/**
	 * Generate Query
	 *
	 * Goes through all of the object variables that correspond with parts of
	 * the query and adds them to the master SQL array.
	 *
	 * @return boolean true
	 */
	private function generateQuery()
	{
		// Adds the JOIN syntax
		if ($this->joins != false)
		{
			if (is_array($this->joins))
			{
				foreach ($this->joins as $join => $tables)
				{
					$join_pieces = array((stripos('JOIN ', $join) === false ? 'JOIN' : strtoupper($join)));

					if (is_array($tables))
					{
						foreach ($tables as $table => $conditions)
						{
							$join_pieces[] = $table;

							if (is_array($conditions))
							{
								$type       = strtoupper(key($conditions));
								$conditions = current($conditions);

								$join_pieces[] = $type;
								$join_pieces[] = $this->generateConditions($conditions, true);
							}
							else
							{
								$join_pieces = $conditions;
							}
						}
					}
					else
					{
						$join_pieces[] = $tables;
					}
				}

				$this->sql[] = implode(' ', $join_pieces);

				unset($join_pieces);
			}
			else
			{
				$this->sql[] = (stripos('JOIN ', $join) === false ? 'JOIN ' : '') . $this->joins;
			}
		}

		// Adds the index hints
		if ($this->hints != false)
		{
			if (is_array($this->hints))
			{
				foreach ($this->hints as $hint => $columns)
				{
					if (is_array($columns))
					{
						$this->sql[] = $hint . ' (' . implode(', ', $columns) . ')';
					}
					else
					{
						$format = (stripos($columns, 'USE ') === false);

						$this->sql[] = ($format == true ? 'USE INDEX (' : '') . $columns . ($format == true ? ')' : '');
					}
				}
			}
			else
			{
				$format = (stripos($this->hints, 'USE ') === false);

				$this->sql[] = ($format == true ? 'USE INDEX (' : '') . $this->hints . ($format == true ? ')' : '');
			}
		}

		// Adds the WHERE conditionals
		if ($this->conditions != false)
		{
			$this->sql[] = 'WHERE ' . (is_array($this->conditions) ? $this->generateConditions($this->conditions) : $this->conditions);
		}

		// Adds the GROUP BY syntax
		if ($this->group != false)
		{
			$this->sql[] = 'GROUP BY ' . (is_array($this->group) ? implode(', ', $this->group) : $this->group);
		}

		// Adds the HAVING conditions
		if ($this->having != false)
		{
			$this->sql[] = 'HAVING ' . (is_array($this->having) ? $this->generateConditions($this->having) : $this->having);
		}

		// Adds the ORDER BY syntax
		if ($this->order != false)
		{
			$this->sql[] = 'ORDER BY ' . (is_array($this->order) ? implode(', ', $this->order) : $this->order);
		}

		// Adds the LIMIT syntax
		if ($this->limit != false)
		{
			$this->sql[] = 'LIMIT ' . (is_array($this->limit) ? implode(', ', $this->limit) : $this->limit);
		}

		// Adds the OFFSET syntax
		if ($this->offset != false)
		{
			$this->sql[] = 'OFFSET ' . $this->offset;
		}

		return true;
	}

	/**
	 * Generate Conditions
	 *
	 * Generates the conditional blocks of SQL from the passed array of
	 * conditions. Supports as much as I could remember to implement. This
	 * method is utilized by both the WHERE and HAVING clauses.
	 *
	 * @param  array $conditions array of potentially nested conditions
	 * @param  boolean $inject_values whether or not to use input parameters
	 * @param  string $conditional syntax to use between conditions
	 * @return string $sql generated SQL for the conditions
	 */
	private function generateConditions($conditions, $inject_values = false, $conditional = 'AND')
	{
		$sql = '';

		foreach ($conditions as $key => $value)
		{
			$key = trim($key);

			if (strtoupper($key) == 'NOT')
			{
				$key = 'AND NOT';
			}

			// Checks if conditional to start recursion
			if (preg_match('/^(AND|&&|OR|\|\||XOR)( NOT)?$/i', $key))
			{
				if (is_array($value))
				{
					// Determines if we need to include ( )
					$nested = (count($value) > 1);

					$conditional = $key;

					$sql .= ' ' . ($sql == '' ? '' : $key) . ' ' . ($nested ? '(' : '');
					$sql .= $this->generateConditions($value, $inject_values, $conditional);
					$sql .= ($nested ? ')' : '');
				}
				else
				{
					$sql .= ' ' . ($sql == '' ? '' : $key) . ' ' . $value;
				}
			}
			else
			{
				if ($sql != '')
				{
					if (preg_match('/^(AND|&&|OR|\|\||XOR)( NOT)?/i', $key))
					{
						$sql .= ' ';
					}
					else
					{
						$sql .= ' ' . $conditional . ' ';
					}
				}

				// Checks for our keywords to control the flow
				$operator  = preg_match('/(<|<=|=|>=|>|!=|!|<>| LIKE)$/i', $key);
				$between   = preg_match('/ BETWEEN$/i', $key);
				$is_is_not = preg_match('/( IS| IS NOT)$/i', $key);

				// Checks for boolean and null
				$is_true  = ($value === true);
				$is_false = ($value === false);
				$is_null  = ($value === null);


				// Generates an IN statement
				if (is_array($value) && $between == false)
				{
					$sql .= $key . ' IN (';

					if ($inject_values == true)
					{
						$sql .= implode(', ', $value);
					}
					else
					{
						$sql .= implode(', ', array_fill(1, count($value), '?'));
						$this->input_parameters = array_merge($this->input_parameters, $value);
					}

					$sql .= ')';
				}
				else
				{
					// If the key is numeric it wasn't set, so don't use it
					if (is_numeric($key))
					{
						$sql .= $value;
					}
					else
					{
						// Omits the operator as the operator is there
						if ($operator == true || $is_is_not == true)
						{
							if ($is_true || $is_false || $is_null)
							{
								// Scrubs the operator if someone doesn't use IS / IS NOT
								if ($operator == true)
								{
									$key = preg_replace('/ ?(!=|!|<>)$/i',         ' IS NOT', $key);
									$key = preg_replace('/ ?(<|<=|=|>=| LIKE)$/i', ' IS',     $key);
								}

								$sql .= $key . ' ';

								if ($is_true)
								{
									$sql .= 'TRUE';
								}
								elseif ($is_false)
								{
									$sql .= 'FALSE';
								}
								else
								{
									$sql .= 'NULL';
								}
							}
							else
							{
								$sql .= $key . ' ';

								if ($inject_values == true)
								{
									$sql .= $value;
								}
								else
								{
									$sql .= '?';
									$this->input_parameters[] = $value;
								}
							}
						}
						// Generates a BETWEEN statement
						elseif ($between == true)
						{
							if (is_array($value))
							{
								// Checks the number of values, BETWEEN expects 2
								if (count($value) != 2)
								{
									throw new Exception('Between expects 2 values');
								}
								else
								{
									$sql .= $key . ' ';

									if ($inject_values == true)
									{
										$sql .= $value[0] . ' AND ' . $value[1];
									}
									else
									{
										$sql .= '? AND ?';
										$this->input_parameters = array_merge($this->input_parameters, $value);
									}
								}
							}
							else
							{
								throw new Exception('Between usage expects values to be in an array');
							}
						}
						else
						{
							$sql .= $key . ' ';

							// Checks if we're working with NULL values
							if ($is_true)
							{
								$sql .= 'IS TRUE';
							}
							elseif ($is_false)
							{
								$sql .= 'IS FALSE';
							}
							elseif ($is_null)
							{
								$sql .= 'IS NULL';
							}
							else
							{
								if ($inject_values == true)
								{
									$sql .= '= ' . $value;
								}
								else
								{
									$sql .= '= ?';
									$this->input_parameters[] = $value;
								}
							}
						}
					}
				}
			}
		}

		return $sql;
	}

	// }}}
	// {{{ Record Interaction Methods

	/**
	 * Count Records
	 *
	 * Counts the records
	 */
	public function count()
	{
		return count($this->records);
	}

	/**
	 * Sort Records
	 *
	 * Sorts the records by the specified index in the specified order.
	 *
	 * @param  string $index the index to be sorted on
	 * @param  string $order the direction to order
	 * @return boolean true
	 * @todo   Implement this method
	 */
	public function sort($index, $order = 'ASC')
	{
		return true;
	}

	/**
	 * Shuffle Records
	 *
	 * Sorts the records in a pseudo-random order.
	 *
	 * @return boolean true
	 * @todo   Implement this method
	 */
	public function shuffle()
	{
		return true;
	}

	/**
	 * Next Record
	 *
	 * Increment the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was next element
	 */
	public function next()
	{
		$return = (boolean)($this->record = next($this->records));

		if ($return == true)
		{
			$this->index++;
		}

		return $return;
	}

	/**
	 * Previous Record
	 *
	 * Decrement the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was previous element
	 */
	public function prev()
	{
		$return = (boolean)($this->record = prev($this->records));

		if ($return == true)
		{
			$this->index--;
		}

		return $return;
	}

	/**
	 * Reset Record
	 *
	 * Set the pointer to the first element of the record set.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function reset()
	{
		$return = (boolean)($this->record = reset($this->records));

		if ($return == true)
		{
			$this->index = 0;
		}

		return $return;
	}

	/**
	 * First Record
	 *
	 * Alias of reset(). "first" is more intuitive to me, but reset stays in
	 * line with the built in PHP functions.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function first()
	{
		return $this->reset();
	}

	/**
	 * End Record
	 *
	 * Set the pointer to the last element of the record set.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function end()
	{
		$return = (boolean)($this->record = end($this->records));

		if ($return == true)
		{
			$this->index = $this->count() - 1;
		}

		return $return;
	}

	/**
	 * Last Record
	 *
	 * Alias of end(). "last" is more intuitive to me, but end stays in line
	 * with the built in PHP functions.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function last()
	{
		return $this->end();
	}

	/**
	 * Walk Records
	 *
	 * Returns the current record and advances to the next. Built to allow for
	 * simplified code when looping through a record set.
	 *
	 * @return mixed either an array of the current record or false
	 * @todo   Does not currently support "indexed" or "list" return types
	 */
	public function walk()
	{
		// Checks if we should start iterating, solves off by one issues with next()
		if ($this->iterate == false)
		{
			$this->iterate = true;
			
			// Resets the records, saves calling reset() when walking multiple times
			$this->reset();
		}
		else
		{
			$this->next();
		}

		return $this->record;
	}

	// }}}
	// {{{ Record Manipulation Methods

	/**
	 * Commit
	 *
	 * Inserts or updates a record in the database.
	 *
	 * @return boolean results of the query
	 */
	public function commit()
	{
		// Checks if the record is actually populated
		if (count($this->record) > 0)
		{
			// Determines if it's an UPDATE or INSERT
			$update = (isset($this->record[$this->id]) && trim($this->record[$this->id]) != '');

			// Establishes the query, optionally uses DELAYED INSERTS
			if ($this->replace === true)
			{
				$sql = 'REPLACE' . ($this->delayed == true ? ' DELAYED' : '') . ' INTO ' . $this->table . ' SET ';
			}
			else
			{
				$sql = ($update === true ? 'UPDATE' : 'INSERT' . ($this->delayed == true ? ' DELAYED' : '') . ' INTO') . ' ' . $this->table . ' SET ';
			}
			$input_parameters = null;

			// Limits the columns being updated
			$record = ($update === true ? array_diff_assoc($this->record, isset($this->original[$this->index]) ? $this->original[$this->index] : array()) : $this->record);

			// Makes sure there's something to INSERT or UPDATE
			if (count($record) > 0)
			{
				// Loops through all the columns and assembles the query
				foreach ($record as $column => $value)
				{
					if ($column != $this->id)
					{
						if ($input_parameters != null)
						{
							$sql .= ', ';
						}

						$sql .= $column . ' = :' . $column;
						$input_parameters[':' . $column] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
					}
				}

				// If it's an UPDATE tack on the ID
				if ($update === true)
				{
					$sql .= ' WHERE ' . $this->id . ' = :' . $this->id . ' LIMIT 1;';
					$input_parameters[':' . $this->id] = $this->record[$this->id];
				}

				// Executes the query
				return $this->db->execute($sql, $input_parameters);
			}
		}

		return false;
	}

	/**
	 * Delete Record
	 *
	 * Deletes the current record from the database
	 *
	 * @return boolean status of the query
	 */
	public function delete()
	{
		$sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->id . ' = :' . $this->id . ' LIMIT 1;';
		$input_parameters[':' . $this->id] = $this->record[$this->id];

		return $this->db->execute($sql, $input_parameters);
	}

	// }}}
	// {{{ Utility Methods

	/**
	 * Load Parameters
	 *
	 * Loads the passed parameters back into the object.
	 *
	 * @access private
	 * @param  array $parameters key / value list
	 * @param  boolean whether or not the parameters were loaded
	 */
	private function loadParameters($parameters)
	{
		if (is_array($parameters))
		{
			$conditions = true;

			// Adds the parameters to the object
			foreach ($parameters as $key => $value)
			{
				// Clean up the variable just in case
				$key = trim(strtolower($key));

				// Assigns valid keys to the appropriate class property
				if (in_array($key, array('fields', 'table', 'joins', 'hints', 'conditions', 'group', 'having', 'order', 'limit', 'offset')))
				{
					$this->$key = $value;
					$conditions = false;
				}
			}

			// If no valid properties were found, assume it's the conditionals
			if ($conditions == true)
			{
				$this->conditions = $parameters;
			}

			return true;
		}

		return false;
	}

	/**
	 * Unescape String
	 *
	 * Assuming magic quotes is turned on, strips slashes from the string
	 *
	 * @access protected
	 * @param  string $value string to be unescaped
	 * @return string unescaped string
	 */
	protected function unescape($value)
	{
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}

		return $value;
	}

	// }}}
}

/**
 * Module Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Module Class
 *
 * This is a parent class that all PICKLES modules should be extending. Each
 * module can specify it's own meta data and whether or not a user must be
 * properly authenticated to view the page. Currently any pages without a
 * template are treated as pages being requested via AJAX and the return will
 * be JSON encoded. In the future this may need to be changed out for logic
 * that allows the requested module to specify what display type(s) it can use.
 */
class Module extends Object
{
	/**
	 * Database object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Page title
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $title = null;

	/**
	 * Meta description
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $description = null;

	/**
	 * Meta keywords (comma separated)
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $keywords = null;

	/**
	 * Secure
	 *
	 * Whether or not the page should be loaded via SSL.
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $secure = null;

	/**
	 * Private
	 *
	 * Whether or not the page can be accessed directly.
	 *
	 * @access protected
	 * @var    boolean, false by default
	 */
	protected $private = false;

	/**
	 * Security settings of the page
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $security = null;

	/**
	 * Session
	 *
	 * Whether or not a session should be established.
	 *
	 * @access protected
	 * @var    boolean, null by default
	 */
	protected $session = null;

	/**
	 * Method
	 *
	 * Request methods that are allowed to access the module.
	 *
	 * @access protected
	 * @var    string or array, null by default
	 */
	protected $method = null;

	/**
	 * Hash
	 *
	 * Whether or not to validate the security hash. Boolean true will indicate
	 * using the name of the module as the hash, a string value will use the
	 * value instead.
	 *
	 * @access protected
	 * @var    string or boolean, null by default
	 */
	protected $hash = null;

	/**
	 * Default display engine
	 *
	 * Defaults to PHP but could be set to JSON, XML or RSS. Value is
	 * overwritten by the config value if not set by the module.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $engine = DISPLAY_PHP;

	/**
	 * Default template
	 *
	 * Defaults to null but could be set to any valid template basename. The
	 * value is overwritten by the config value if not set by the module. The
	 * display engine determines what the file extension should be.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $template = 'index';

	/**
	 * Constructor
	 *
	 * The constructor does nothing by default but can be passed a boolean
	 * variable to tell it to automatically run the __default() method. This is
	 * typically used when a module is called outside of the scope of the
	 * controller (the registration page calls the login page in this manner.
	 *
	 * @param boolean $autorun optional flag to autorun __default()
	 */
	public function __construct($autorun = false)
	{
		parent::__construct();

		$this->db = Database::getInstance();

		if ($autorun === true)
		{
			$this->__default();
		}
	}

	/**
	 * Default "Magic" Method
	 *
	 * This function is overloaded by the module. The __default() method is
	 * where you want to place any code that needs to be executed at runtime.
	 * The reason the code isn't in the constructor is because the module must
	 * be instantiated before the code is executed so that the controller
	 * script is aware of the authentication requirements.
	 */
	public function __default()
	{

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
		throw new Exception('Cannot set module variables directly');
	}

	/**
	 * Magic Getter Method
	 *
	 * Attempts to load the module variable. If it's not set, will attempt to
	 * load from the config.
	 *
	 * @param  string $name name of the variable requested
	 * @return mixed value of the variable or boolean false
	 */
	public function __get($name)
	{
		if ($this->$name == null)
		{
			if (isset($this->config->pickles[$name]))
			{
				$this->$name = $this->config->pickles[$name];
			}
			else
			{
				$this->$name = false;
			}
		}

		return $this->$name;
	}

	/**
	 * Sets the Request
	 *
	 * @param  array $request data to be loaded into the request variable
	 * @return boolean whether or not the assignment was successful
	 */
	public function setRequest($request)
	{
		$backtrace = debug_backtrace();

		if ($backtrace[1]['class'] == 'Controller')
		{
			$this->request = $request;
			return true;
		}
		else
		{
			throw new Exception('Only Controller can perform setRequest()');
		}
	}
}

/**
 * Object Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Object Class
 *
 * Every instantiated class in PICKLES should be extending this class. By doing
 * so the class is automatically hooked into the profiler, and the object will
 * have access to some common components as well.
 */
class Object
{
	/**
	 * Object Instances
	 *
	 * @static
	 * @access private
	 * @var    mixed
	 */
	protected static $instances = array();

	/**
	 * Instance of the Config object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $config = null;

	/**
	 * Profiler flag
	 *
	 *
	 * @access private
	 * @var    mixed
	 */
	private $profiler = false;

	/**
	 * Constructor
	 *
	 * Establishes a Config instance for all children to enjoy
	 */
	public function __construct()
	{
		// Gets an instance of the config, unless we ARE the config
		if (get_class($this) == 'Config')
		{
			$this->config = true;
		}
		else
		{
			$this->config = Config::getInstance();
		}

		// Assigns the profiler flag
		$this->profiler = (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] != '' ? $this->config->pickles['profiler'] : false);

		// Optionally logs the constructor to the profiler
		if ($this->profiler === true || ((is_array($this->profiler) && in_array('objects', $this->profiler)) || stripos($this->profiler, 'objects') !== false))
		{
			Profiler::log($this, '__construct');
		}
	}

	/**
	 * Get Instance
	 *
	 * Gets an instance of the passed class. Allows for easy sharing of certain
	 * classes within the system to avoid the extra overhead of creating new
	 * objects each time. Also avoids the hassle of passing around variables.
	 *
	 * @static
	 * @param  string $class name of the class
	 * @return object instance of the class
	 */
	public static function getInstance($class = false)
	{
		// In < 5.3 arguments must match in child, hence defaulting $class
		if ($class == false)
		{
			return false;
		}
		else
		{
			if (!isset(self::$instances[$class]))
			{
				self::$instances[$class] = new $class();
			}

			return self::$instances[$class];
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		// Optionally logs the destructor to the profiler
		if ($this->profiler === true || ((is_array($this->profiler) && in_array('objects', $this->profiler)) || stripos($this->profiler, 'objects') !== false))
		{
			Profiler::log($this, '__destruct');
		}
	}
}

/**
 * Profiler
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Profiler Class
 *
 * The Profiler class is statically interfaced with and allows for in depth
 * profiling of a site. By default profiling is off, but can be enabled in the
 * config.ini for a site. Out of the box the profiler will report on every
 * class object in the system that extends the code Object class.
 *
 * Note: I really wanted to use PHP Quick Profiler by Ryan Campbell of
 * Particletree but it kept barking out errors when I tried to use it with
 * E_STRICT turned on. Here's a link anyway since it looks awesome:
 * http://particletree.com/features/php-quick-profiler/
 *
 * @usage <code>Profiler::log('some action you want to track');</code>
 * @usage <code>Profiler::log($object, 'methodName');</code>
 */
class Profiler
{
	/**
	 * Config
	 *
	 * Profiler configuration
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $config;

	/**
	 * Profile
	 *
	 * Array of logged events
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $profile = array();

	/**
	 * Queries
	 *
	 * Number of queries that have been logged
	 *
	 * @static
	 * @access private
	 * @var    integer
	 */
	private static $queries = 0;

	/**
	 * Timers
	 *
	 * Array of active timers
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $timers = array();

	/**
	 * Constructor
	 *
	 * Private constructor since this class is interfaced wtih statically.
	 *
	 * @access private
	 */
	private function __construct()
	{

	}

	/**
	 * Enabled
	 *
	 * Checks if the profiler is set to boolean true or if the passed type is
	 * specified in the profiler configuration value.
	 *
	 * @param  array $type type(s) to check
	 * @return boolean whether or not the type is enabled
	 */
	public static function enabled(/* polymorphic */)
	{
		// Grabs the config object if we don't have one yet
		if (self::$config == null)
		{
			$config       = Config::getInstance();
			self::$config = $config->pickles['profiler'];
		}

		// Checks if we're set to boolean true
		if (self::$config === true)
		{
			return true;
		}
		else
		{
			$types = func_get_args();

			foreach ($types as $type)
			{
				if (stripos(self::$config, $type) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Log
	 *
	 * Logs the event to be displayed later on. Due to the nature of how much
	 * of a pain it is to determine which class method called this method I
	 * opted to make the method a passable argument for ease of use. Perhaps
	 * I'll revisit in the future. Handles all elapsed time calculations and
	 * memory usage.
	 *
	 * @static
	 * @param  mixed $data data to log
	 * @param  string $method name of the class method being logged
	 */
	public static function log($data, $method = false, $type = false)
	{
		$time      = microtime(true);
		$data_type = ($data == 'timer' ? $data : gettype($data));

		// Tidys the data by type
		switch ($data_type)
		{
			case 'array':
				$log = '<pre>' . print_r($data, true) . '</pre>';
				break;

			case 'object':
				$log = '<span style="color:#666">[</span><span style="color:#777">' . get_parent_class($data) . '</span><span style="color:#666">]</span> '
					 . '<span style="color:#69c">' . get_class($data) . '</span>'
					 . ($method != '' ? '<span style="color:#666">-></span><span style="color:#4eed9e">' . $method . '</span><span style="color:#666">()</span>' : '');

				$data_type = '<span style="color:Peru">' . $data_type . '</span>';
				break;

			case 'timer':
				$log = $method;

				$data_type = '<span style="color:#6c0">' . $data_type . '</span>';
				break;

			case 'string':
			default:
				if ($type != false)
				{
					$data_type = $type;
				}

				$log = $data;
				break;
		}

		self::$profile[] = array(
			'log'     => $log,
			'type'    => $data_type,
			'time'    => $time,
			'elapsed' => $time - PICKLES_START_TIME,
			'memory'  => memory_get_usage(),
		);
	}

	/**
	 * Log Query
	 *
	 * Serves as a wrapper to get query data to the log function
	 *
	 * @static
	 * @param  string $query the query being executed
	 * @param  array $input_parameters optional prepared statement data
	 * @param  array $explain EXPLAIN data for the query
	 * @param  float $duration the speed of the query
	 */
	public static function logQuery($query, $input_parameters = false, $explain = false, $duration = false)
	{
		self::$queries++;

		$log = '';

		if ($input_parameters != 'false' && is_array($input_parameters))
		{
			$log .= '<br />';

			foreach ($input_parameters as $key => $value)
			{
				$log .= '<br /><span style="color:#a82222">' . $key . '</span> <span style="color:#666">=></span> <span style="color:#ffff7f">' . $value . '</span>';

				$query = str_replace($key, '<span style="color:#a82222">' . $key . '</span>', $query);
			}
		}

		$log = '<span style="color:#009600">' . $query . '</span>' . $log;

		if (is_array($explain))
		{
			$log .= '<br />';

			foreach ($explain as $table)
			{
				$log .= '<br /><span style="color:RoyalBlue">Possible Keys</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . ($table['possible_keys'] == '' ? '<em style="color:red">NONE</em>' : $table['possible_keys']) . '</span>'
					 . '<br /><span style="color:RoyalBlue">Key</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">'  . ($table['key'] == '' ? '<em style="color:red">NONE</em>' : $table['key']) . '</span>'
					 . '<br /><span style="color:RoyalBlue">Type</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . $table['type'] . '</span>'
					 . '<br /><span style="color:RoyalBlue">Rows</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">'  . $table['rows'] . '</span>'
					 . ($table['Extra'] != '' ? '<br /><span style="color:RoyalBlue">Extra</span> <span style="color:#666">=></span> <span style="color:DarkGoldenRod">' . $table['Extra'] . '</span>' : '');
			}
		}

		$log .= '<br /><br /><span style="color:DarkKhaki">Speed:</span> ' . number_format($duration * 100, 3) . ' ms';

		self::log($log, false, '<span style="color:DarkCyan">database</span>');
	}

	/**
	 * Timer
	 *
	 * Logs the start and end of a timer.
	 *
	 * @param  string $timer name of the timer
	 * @return boolean whether or not timer profiling is enabled
	 */
	public static function timer($timer)
	{
		if (self::enabled('timers'))
		{
			// Starts the timer
			if (!isset(self::$timers[$timer]))
			{
				self::$timers[$timer] = microtime(true);
				self::Log('timer', '<span style="color:Orchid">Started timer</span> <span style="color:Yellow">' . $timer . '</span>');
			}
			// Ends the timer
			else
			{
				self::Log('timer', '<span style="color:Orchid">Stopped timer</span> <span style="color:Yellow">' . $timer . '</span> <span style="color:#666">=></span> <span style="color:DarkKhaki">Time Elapsed:</span> ' . number_format((microtime(true) - self::$timers[$timer]) * 100, 3) . ' ms');

				unset(self::$timers[$timer]);
			}

			return true;
		}

		return false;
	}

	/**
	 * Report
	 *
	 * Generates the Profiler report that is displayed by the Controller.
	 * Contains all the HTML needed to display the data properly inline on the
	 * page. Will generally be displayed after the closing HTML tag.
	 */
	public static function report()
	{
		?>
		<style>
			#pickles-profiler
			{
				background: #212121;
				width: 800px;
				margin: 0 auto;
				margin-top: 20px;
				margin-bottom: 20px;
				-moz-border-radius: 20px;
				-webkit-border-radius: 20px;
				border-radius: 20px;
				-moz-box-shadow: 0 3px 4px rgba(0,0,0,0.5);
				-webkit-box-shadow: 0 3px 4px rgba(0,0,0,0.5);
				box-shadow: 0 3px 4px rgba(0,0,0,0.5);
				border: 6px solid #666;
				padding: 10px 20px 20px;
				font-family: monospace;
				font-size: 12px;
				text-align: left;
			}
			#pickles-profiler table
			{
				width: 100%;
			}
			#pickles-profiler table tr th, #pickles-profiler table tr td
			{
				padding: 10px;
			}
			#pickles-profiler .even
			{
				background-color: #323232;
			}
			#pickles-profiler, #pickles-profiler table tr td, #pickles-profiler table tr th
			{
				color: #efefe8;
			}
		</style>
		<div id="pickles-profiler">
			<strong style="font-size:1.5em">PICKLES Profiler</strong><br /><br />
	 		<?php
			if (count(self::$profile) == 0)
			{
				echo '<em style="line-height:18px">There is nothing to profile. This often happens when the profiler configuration is set to either "queries" or "explains" and there are no database queries on the page (common on pages that only have a template). You may want to set the profiler to boolean true to ensure you get a profile of the page.</em>';
			}
			else
			{
				$start_time = PICKLES_START_TIME;
				$peak_usage = self::formatSize(memory_get_peak_usage());
				$end_time   = self::$profile[count(self::$profile) - 1]['time']; // TODO
				$duration   = ($end_time - $start_time);

				$logs  = count(self::$profile);
				$logs .= ' Log' . ($logs == 1 ? '' : 's');

				$files  = count(get_included_files());
				$files .= ' File' . ($files == 1 ? '' : 's');

				$queries = self::$queries . ' Quer'. (self::$queries == 1 ? 'y' : 'ies');
				?>
				<table style="border-collapse:separate;border-spacing:1px;border-radius:10px;text-shadow:1px 1px 1px #000">
					<tr>
						<td style="text-align:center;background:#480000">
							<span style="font-weight:bold;">Console</span>
							<div style="color:#ff7f7f;font-size:1.2em;padding-top:10px"><?php echo $logs; ?></div>
						</td>
						<td style="text-align:center;background:#552200">
							<span style="font-weight:bold;">Load Time</span>
							<div style="color:#ffa366;font-size:1.2em;padding-top:10px"><?php echo number_format($duration * 100, 3) . ' ms / ' . ini_get('max_execution_time'); ?></div>
						</td>
						<td style="text-align:center;background:#545500">
							<span style="font-weight:bold;">Memory Usage</span>
							<div style="color:#ffff6d;font-size:1.2em;padding-top:10px"><?php echo $peak_usage . ' / ' . ini_get('memory_limit'); ?></div>
						</td>
						<td style="text-align:center;background:#004200">
							<span style="font-weight:bold;">Database</span>
							<div style="color:#7dff7d;font-size:1.2em;padding-top:10px"><?php echo $queries; ?></div>
						</td>
						<td style="text-align:center;background:#000048">
							<span style="font-weight:bold;">Includes</span>
							<div style="color:#c4c4ff;font-size:1.2em;padding-top:10px"><?php echo $files; ?></div>
						</td>
					</tr>
				</table>
				<table>
					<tr>
						<th style="text-align:left" colspan="2">Console</th>
						<th style="text-align:right">Memory</th>
						<th style="text-align:right">Time</th>
					</tr>
					<?php
					foreach (self::$profile as $key => $entry)
					{
						?>
						<tr>
							<td style="font-weight:bold;color:#999"><?php echo $entry['type']; ?></td>
							<td><?php echo $entry['log']; ?></td>
							<td style="text-align:right" nowrap="nowrap"><?php echo self::formatSize($entry['memory']); ?></td>
							<td style="text-align:right" nowrap="nowrap"><?php echo number_format($entry['elapsed'] * 100, 3); ?> ms</td>
						</tr>
						<?php
					}
					?>
				</table>
				<?php
			}
			?>
		</div>
		<br /><br />
		<?php
	}

	/**
	 * Format Size
	 *
	 * Formats the passed size into a human readable string
	 *
	 * @static
	 * @access private
	 * @param  float $filesize size of the file
	 * @return string formatted number string
	 */
	private static function formatSize($filesize)
	{
		$units = array('bytes', 'kB', 'MB', 'GB');

		return number_format(round($filesize / pow(1024, ($i = floor(log($filesize, 1024)))), 2), 2) . ' ' . $units[$i];
	}
}

/**
 * Security System for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Security Class
 *
 * Collection of static methods for handling security within a website running
 * on PICKLES. Requires sessions to be enabled.
 *
 * @usage <code>Security::login(10);</code>
 * @usage <code>Security::isLevel(SECURITY_LEVEL_ADMIN);</code>
 */
class Security
{
	/**
	 * Lookup Cache
	 *
	 * Used to minimize database lookups
	 *
	 * @static
	 * @access private
	 * @var    array
	 */
	private static $cache = array();

	/**
	 * Generate Hash
	 *
	 * Generates an SHA1 hash from the provided string. Optionally can be salted.
	 *
	 * @param  string $value value to hash
	 * @param  mixed $salts optional salt or salts
	 * @return string SHA1 has
	 */
	public static function generateHash($value, $salts = null)
	{
		// Determines which salt(s) to use
		if ($salts == null)
		{
			$config = Config::getInstance();

			if (isset($config->security['salt']) && $config->security['salt'] != null)
			{
				$salts = $config->security['salt'];
			}
			else
			{
 				$salts = array('P1ck73', 'Ju1C3');
			}
		}

		// Forces the variable to be an array
		if (!is_array($salts))
		{
			$salts = array($salts);
		}

		// Loops through the salts, applies them and calculates the hash
		$hash = $value;
		foreach ($salts as $salt)
		{
			$hash = sha1($salt . $hash);
		}

		return $hash;
	}

	/**
	 * Check Session
	 *
	 * Checks if sessions are enabled.
	 *
	 * @static
	 * @access private
	 * @return boolean whether or not sessions are enabled
	 */
	private static function checkSession()
	{
		if (session_id() == '')
		{
			throw new Exception('Sessions must be enabled to use the Security class');
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check Level
	 *
	 * Checks if a passed level is an integer and/or properly defined in the
	 * site's configuration file.
	 *
	 * @static
	 * @access private
	 * @param  mixed access level to validate
	 * @return whether ot not the access level is valid
	 */
	private static function checkLevel(&$access_level)
	{
		if (is_int($access_level))
		{
			return true;
		}
		else
		{
			$config = Config::getInstance();

			// Attempts to validate the string passed
			if (isset($config->security[$access_level]))
			{
				if (is_numeric($config->security[$access_level]))
				{
					$access_level = (int)$config->security[$access_level];
					return true;
				}
				else
				{
					throw new Exception('Level "' . $access_level . '" is not numeric in config.ini');
				}
			}
			else
			{
				throw new Exception('Level "' . $access_level . '" is not defined in config.ini');
			}
		}

		return false;
	}

	/**
	 * Login
	 *
	 * Creates a session variable containing the user ID and generated token.
	 * The token is also assigned to a cookie to be used when validating the
	 * security level. When the level value is present, the class will by pass
	 * the database look up and simply use that value when validating (the less
	 * paranoid scenario).
	 *
	 * @static
	 * @param  integer $user_id ID of the user that's been logged in
	 * @param  integer $level optional level for the user being logged in
	 * @return boolean whether or not the login could be completed
	 */
	public static function login($user_id, $level = null)
	{
		if (self::checkSession())
		{
			$token = sha1(microtime());

			$_SESSION['__pickles']['security'] = array(
				'token'   => $token,
				'user_id' => (int)$user_id,
				'level'   => $level
			);

			setcookie('pickles_security_token', $token);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Logout
	 *
	 * Clears out the security information in the session and the cookie.
	 *
	 * @static
	 * @return boolean true
	 */
	public static function logout()
	{
		if (isset($_SESSION['__pickles']['security']))
		{
			$_SESSION['__pickles']['security'] = null;
			unset($_SESSION['__pickles']['security']);

			setcookie('pickles_security_token', '', time() - 3600);
		}

		return true;
	}

	/**
	 * Get User Level
	 *
	 * Looks up the user level in the database and caches it. Cache is used
	 * for any subsequent look ups for the user. Also validates the session
	 * variable against the cookie to ensure everything is legit. If the user
	 * level is set in the session, that value will take precedence.
	 *
	 * return integer user level or false
	 */
	private static function getUserLevel()
	{
		if (self::checkSession() == true && isset($_SESSION['__pickles']['security']['user_id']))
		{
			// Checks the session against the cookie
			if (isset($_SESSION['__pickles']['security']['token'], $_COOKIE['pickles_security_token'])
				&& $_SESSION['__pickles']['security']['token'] != $_COOKIE['pickles_security_token'])
			{
				Security::logout();
			}
			elseif (isset($_SESSION['__pickles']['security']['level']) && $_SESSION['__pickles']['security']['level'] != null)
			{
				return $_SESSION['__pickles']['security']['level'];
			}
			// Hits the database to determine the user's level
			else
			{
				// Checks the session cache instead of hitting the database
				if (isset($_SESSION['__pickles']['security']['user_id'], self::$cache[(int)$_SESSION['__pickles']['security']['user_id']]))
				{
					return self::$cache[(int)$_SESSION['__pickles']['security']['user_id']];
				}
				else
				{
					// Pulls the config and defaults where necessary
					$config = Config::getInstance();

					if ($config->security === false)
					{
						$config = array();
					}
					else
					{
						$config = $config->security;
					}

					$defaults = array('login' => 'login', 'model' => 'User', 'column' => 'level');
					foreach ($defaults as $variable => $value)
					{
						if (!isset($config[$variable]))
						{
							$config[$variable] = $value;
						}
					}

					// Uses the model to pull the user's access level
					$class = $config['model'];
					$model = new $class(array('fields' => $config['column'], 'conditions' => array('id' => (int)$_SESSION['__pickles']['security']['user_id'])));

					if ($model->count() == 0)
					{
						Security::logout();
					}
					else
					{
						$constant = 'SECURITY_LEVEL_' . $model->record[$config['column']];

						if (defined($constant))
						{
							$constant = constant($constant);

							self::$cache[(int)$_SESSION['__pickles']['security']['user_id']] = $constant;

							return $constant;
						}
						else
						{
							throw new Exception('Security level constant is not defined');
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Is Level
	 *
	 * Checks the user's access level is exactly the passed level
	 *
	 * @static
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user is that level
	 */
	public static function isLevel()
	{
		$is_level = false;

		if (self::checkSession())
		{
			$arguments = func_get_args();
			if (is_array($arguments[0]))
			{
				$arguments = $arguments[0];
			}

			foreach ($arguments as $access_level)
			{
				if (self::checkLevel($access_level))
				{
					if (self::getUserLevel() == $access_level)
					{
						$is_level = true;
						break;
					}
				}
			}
		}

		return $is_level;
	}

	/**
	 * Has Level
	 *
	 * Checks the user's access level against the passed level.
	 *
	 * @static
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function hasLevel()
	{
		$has_level = false;

		if (self::checkSession())
		{
			$arguments = func_get_args();
			if (is_array($arguments[0]))
			{
				$arguments = $arguments[0];
			}

			foreach ($arguments as $access_level)
			{
				if (self::checkLevel($access_level))
				{
					if (self::getUserLevel() >= $access_level)
					{
						$has_level = true;
						break;
					}
				}
			}
		}

		return $has_level;
	}

	/**
	 * Between Level
	 *
	 * Checks the user's access level against the passed range.
	 *
	 * @static
	 * @param  integer $low access level to be checked against
	 * @param  integer $high access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function betweenLevel($low, $high)
	{
		$between_level = false;

		if (self::checkSession())
		{
			if (self::checkLevel($low) && self::checkLevel($high))
			{
				$user_level = self::getUserLevel();

				if ($user_level >= $low && $user_level <= $high)
				{
					$between_level = true;
					break;
				}
			}
		}

		return $between_level;
	}
}

/**
 * Session Handling for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Session Class
 *
 * Provides session handling via database instead of the file based session
 * handling built into PHP. Using this class requires an array to be defined
 * in place of the boolean true/false (on/off). If simply array(), the
 * datasource will default to the value in $config['pickles']['datasource'] and
 * if the table will default to "sessions". The format is as follows:
 *
 *     $config = array(
 *         'pickles' => array(
 *             'session' => array(
 *                 'datasource' => 'mysql',
 *                 'table'      => 'sessions',
 *             )
 *         )
 *     );
 *
 * In addition to the configuration variables, a table in your database must
 * be created. The [MySQL] table schema is as follows:
 *
 *     CREATE TABLE sessions (
 *         id varchar(32) COLLATE utf8_unicode_ci NOT NULL,
 *         session text COLLATE utf8_unicode_ci NOT NULL,
 *         expires_at datetime NOT NULL,
 *         PRIMARY KEY (id)
 *     ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 *
 * Note: The reason for not using a model class was to avoid a naming conflict
 * between the Session model and the Session class itself. This will eventually
 * be resolved when I abandon full 5.x support and migrate to 5.3+ (assuming
 * that ever happens).
 */
class Session extends Object
{
	/**
	 * Handler
	 *
	 * What the session is being handled by.
	 *
	 * @access private
	 * @var    string
	 */
	private $handler = false;

	/**
	 * Accessed At
	 *
	 * The UNIX timestamp of when the page was accessed.
	 *
	 * @access private
	 * @var    integer
	 */
	private $accessed_at = null;

	/**
	 * Time to Live
	 *
	 * The number of seconds the session should remain active. Corresponds to
	 * the INI variable session.gc_maxlifetime
	 *
	 * @access private
	 * @var    integer
	 */
	private $time_to_live = null;

	/**
	 * Datasource
	 *
	 * Name of the datasource, defaults to whatever the default datasource
	 * is defined to in config.php
	 *
	 * @access private
	 * @var    string
	 */
	private $datasource = null;

	/**
	 * Table
	 *
	 * Name of the database table in the aforementioned datasource that holds
	 * the session data. The expected schema is defined above.
	 *
	 * @access private
	 * @var    string
	 */
	private $table = null;

	/**
	 * Database
	 *
	 * Our database object to interact with the aforementioned datasource and
	 * table. This object is shared with other PICKLES internals.
	 *
	 * @access private
	 * @var    object
	 */
	private $db = null;

	/**
	 * Constructor
	 *
	 * All of our set up logic for the session in contained here. This object
	 * is initially instantiated from pickles.php and the session callbacks are
	 * established here. All variables are driven from php.ini and/or the site
	 * config. Once configured, the session is started automatically.
	 */
	public function __construct()
	{
		if (isset($_REQUEST['request']) == false || preg_match('/^__pickles\/(css|js)\/.+$/', $_REQUEST['request']) == false)
		{
			parent::__construct();

			// Sets up our configuration variables
			$session     = $this->config->pickles['session'];
			$datasources = $this->config->datasources;

			$datasource = false;
			$table      = 'sessions';

			if (is_array($session))
			{
				if (isset($session['handler']) && in_array($session['handler'], array('files', 'memcache', 'mysql')))
				{
					$this->handler = $session['handler'];

					if ($this->handler != 'files')
					{
						if (isset($session['datasource']))
						{
							$datasource = $session['datasource'];
						}

						if (isset($session['table']))
						{
							$table = $session['table'];
						}
					}
				}
			}
			else
			{
				if ($session === true || $session == 'files')
				{
					$this->handler = 'files';
				}
				elseif ($session == 'memcache')
				{
					$this->handler = 'memcache';
					$datasource    = 'memcached';
				}
				elseif ($session == 'mysql')
				{
					$this->handler = 'mysql';
					$datasource    = 'mysql';
				}
			}

			switch ($this->handler)
			{
				case 'files':
					ini_set('session.save_handler', 'files');
					session_start();
					break;

				case 'memcache':
					$hostname = 'localhost';
					$port     = 11211;

					if ($datasource !== false && isset($datasources[$datasource]))
					{
						$hostname = $datasources[$datasource]['hostname'];
						$port     = $datasources[$datasource]['port'];
					}

					ini_set('session.save_handler', 'memcache');
					ini_set('session.save_path',    'tcp://' . $hostname . ':' . $port . '?persistent=1&amp;weight=1&amp;timeout=1&amp;retry_interval=15');
					session_start();
					break;

				case 'mysql':
					if ($datasource !== false && isset($datasources[$datasource]))
					{
						// Sets our access time and time to live
						$this->accessed_at  = time();
						$this->time_to_live = ini_get('session.gc_maxlifetime');

						$this->datasource = $datasource;
						$this->table      = $table;

						// Gets a database instance
						$this->db = Database::getInstance($this->datasource);

						// Initializes the session
						$this->initialize();

						session_start();
					}
					else
					{
						throw new Exception('Unable to determine which datasource to use');
					}

					break;
			}
		}
	}

	/**
	 * Destructor
	 *
	 * Runs garbage collection and closes the session. I'm not sure if the
	 * garbage collection should stay as it could be accomplished via php.ini
	 * variables. The session_write_close() is present to combat a chicken
	 * and egg scenario in earlier versions of PHP 5.
	 */
	public function __destruct()
	{
		if ($this->handler == 'mysql')
		{
			$this->gc($this->time_to_live);
			session_write_close();
		}
	}

	/**
	 * Initializes the Session
	 *
	 * This method exists to combat the fact that calling session_destroy()
	 * also clears out the save handler. Upon destorying a session this method
	 * is called again so the save handler is all set.
	 */
	public function initialize()
	{
		// Sets up the session handler
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);

		register_shutdown_function('session_write_close');
	}

	/**
	 * Opens the Session
	 *
	 * Since the session is in the database, opens the database connection.
	 * This step isn't really necessary as the Database object is smart enough
	 * to open itself up upon execute.
	 */
	public function open()
	{
		session_regenerate_id();

		return $this->db->open();
	}

	/**
	 * Closes the Session
	 *
	 * Same as above, but in reverse.
	 */
	public function close()
	{
		return $this->db->close();
	}

	/**
	 * Reads the Session
	 *
	 * Checks the database for the session ID and returns the session data.
	 *
	 * @param  string $id session ID
	 * @return string serialized session data
	 */
	public function read($id)
	{
		$sql = 'SELECT session FROM `' . $this->table . '` WHERE id = ?;';

		$session = $this->db->fetch($sql, array($id));

		return isset($session[0]['session']) ? $session[0]['session'] : '';
	}

	/**
	 * Writes the Session
	 *
	 * When there's changes to the session, writes the data to the database.
	 *
	 * @param  string $id session ID
	 * @param  string $session serialized session data
	 * @return boolean whether the query executed correctly
	 */
	public function write($id, $session)
	{
		$sql = 'REPLACE INTO `' . $this->table . '` VALUES (?, ? ,?);';

		$parameters = array($id, $session, date('Y-m-d H:i:s', strtotime('+' . $this->time_to_live . ' seconds')));

		return $this->db->execute($sql, $parameters);
	}

	/**
	 * Destroys the Session
	 *
	 * Deletes the session from the database.
	 *
	 * @param  string $id session ID
	 * @return boolean whether the query executed correctly
	 */
	public function destroy($id)
	{
		$sql = 'DELETE FROM `' . $this->table . '` WHERE id = ?;';

		return $this->db->execute($sql, array($id));
	}

	/**
	 * Garbage Collector
	 *
	 * This is who you call when you got trash to be taken out.
	 *
	 * @param  integer $time_to_live number of seconds a session is active
	 * @return boolean whether the query executed correctly
	 */
	public function gc($time_to_live)
	{
		$sql = 'DELETE FROM `' . $this->table . '` WHERE expires_at < ?;';

		$parameters = array(date('Y-m-d H:i:s', $this->accessed_at - $time_to_live));

		return $this->db->execute($sql, $parameters);
	}
}

/**
 * String Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman 
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * String Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant string related manipulation.
 */
class String
{
	// {{{ Format Phone Number

	/**
	 * Format Phone Number
	 *
	 * Formats a 10 digit phone number with dashes as ###-###-####.
	 *
	 * @static
	 * @param  string $number number to format
	 * @param  string $replacement output of the string
	 * @return string formatted phone number
	 */
	public static function formatPhoneNumber($number, $replacement = '$1-$2-$3')
	{
		// Strips characters we don't need
		$number = str_replace(array('(', ')', ' ', '-', '.', '_'), '', $number);

		// Formats the number
		return preg_replace('/^(\d{3})(\d{3})(.+)$/', $replacement, $number);
	}

	// }}}
	// {{{ Generate Gravatar Hash

	/**
	 * Generate Gravatar Hash
	 *
	 * Generates a hash from the passed string that can then be used for
	 * fetching an avatar from Gravatar.com
	 *
	 * @static
	 * @param  string $string string to hash, should be an email address
	 * @return string resulting hash
	 */
	public static function generateGravatarHash($string)
	{
		// Trims whitespace, lowers the case then applies MD5
		return md5(strtolower(trim($string)));
	}

	// }}}
	// {{{ Is Empty

	/**
	 * Is Empty
	 *
	 * Checks if a string is empty. You can use the PHP function empty() but
	 * that returns true for a string of "0". Last I checked, that's not an
	 * empty string. PHP's function also doesn't apply trim() to the value
	 * to ensure it's not just a bunch of spaces.
	 *
	 * @static
	 * @param  string $value string(s) to be checked
	 * @return boolean whether or not the string is empty
	 */
	public static function isEmpty()
	{
		foreach (func_get_args() as $value)
		{
			if (trim($value) == '')
			{
				return true;
			}
		}

		return false;
	}

	// }}}
	// {{{ Random

	/**
	 * Random
	 *
	 * Generates a pseudo-random string based on the passed parameters.
	 *
	 * Note: Similar characters = 0, O, 1, I (and may be expanded)
	 *
	 * @static
	 * @param  integer $length optional length of the generated string
	 * @param  boolean $alpha optional include alpha characters
	 * @param  boolean $numeric optional include numeric characters
	 * @param  boolean $similar optional include similar characters
	 * @return string generated string
	 */
	public static function random($length = 8, $alpha = true, $numeric = true, $similar = true)
	{
		$characters = array();
		$string     = '';

		// Adds alpha characters to the list
		if ($alpha == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('A', 'Z'));
			}
			else
			{
				$characters = array_merge($characters, range('A', 'H'), range('J', 'N'), range('P', 'Z'));
			}
		}

		// Adds numeric characters to the list
		if ($numeric == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('0', '9'));
			}
			else
			{
				$characters = array_merge($characters, range('2', '9'));
			}
		}

		if (count($characters) > 0)
		{
			shuffle($characters);

			for ($i = 0; $i < $length; $i++)
			{
				$string .= $characters[$i];
			}
		}

		return $string;
	}

	// }}}
	// {{{ Truncate

	/**
	 * Truncate
	 *
	 * Truncates a string to a specified length and (optionally) adds a span to
	 * provide a rollover to see the expanded text.
	 *
	 * @static
	 * @param  string $string string to truncate
	 * @param  integer $length length to truncate to
	 * @param  boolean $hover (optional) whether or not to add the rollover
	 * @return string truncate string
	 */
	public static function truncate($string, $length, $hover = true)
	{
		if (strlen($string) > $length)
		{
			if ($hover == true)
			{
				$string = '<span title="' . $string . '" style="cursor:help">' . substr($string, 0, $length) . '...</span>';
			}
			else
			{
				$string = substr($string, 0, $length) . '...';
			}
		}

		return $string;
	}

	// }}}
	// {{{ Upper Words

	/**
	 * Upper Words
	 *
	 * Applies strtolower() and ucwords() to the passed string. The exception
	 * being email addresses which are not formatted at all.
	 *
	 * @static
	 * @param  string $string string to format
	 * @return string formatted string
	 */
	public static function upperWords($string)
	{
		// Only formats non-email addresses
		if (filter_var($string, FILTER_VALIDATE_EMAIL) == false)
		{
			$string = ucwords(strtolower($string));
		}

		return $string;
	}

	// }}}
}

?>
