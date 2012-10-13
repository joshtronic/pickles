<?php

/**
 * Common API Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Common API Interface
 *
 * Parent class that our API interface classes should be extending. Contains
 * execution of parental functions but may contain more down the road.
 */
abstract class API_Common extends Object
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
}

/**
 * Google Profanity Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Google Profanity API Interface
 */
class API_Google_Profanity extends API_Common
{
	/**
	 * Check
	 *
	 * Checks if a word is considered profanity.
	 *
	 * @usage API_Google_Profanity::check('fuck'); // returns true
	 * @param string $word word to check
	 * @return boolean whether or not the word is profanity
	 */
	public static function check($word)
	{
		$response = json_decode(file_get_contents('http://www.wdyl.com/profanity?q=' . $word), true);

		if ($response == null || !isset($response['response']) || !in_array($response['response'], array('true', 'false')))
		{
			throw new Exception('Invalid response from API.');
		}
		else
		{
			return $response['response'] == 'true';
		}
	}
}

/**
 * Tinychat Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Tinychat API Interface
 *
 * @link http://tinychat.com/developer/docs
 */
class API_Tinychat extends API_Common
{
	/**
	 * Public Key
	 *
	 * @access private
	 * @var    string
	 */
	private $public_key = null;

	/**
	 * Secret Key
	 *
	 * @access private
	 * @var    string
	 */
	private $secret_key = null;

	/**
	 * Constructor
	 *
	 * Assigns our public and secret keys from the configuration.
	 */
	public function __construct()
	{
		parent::__construct();

		if (isset($this->config->api['tinychat'], $this->config->api['tinychat']['public_key'], $this->config->api['tinychat']['secret_key']))
		{
			$this->public_key = $this->config->api['tinychat']['public_key'];
			$this->secret_key = $this->config->api['tinychat']['secret_key'];
		}
		else
		{
			throw new Exception('Unable to load TinyChat configuration.');
		}
	}

	/**
	 * Execute
	 *
	 * Constructs a valid API call, executes it and returns the results.
	 *
	 * @param string $codephrase name of the API call being called
	 * @param string $authentication post-codephrase portion of the auth string
	 * @param array $parameters key / value pairs for additional data
	 * @return array results of the API call
	 */
	private function execute($codephrase, $authentication, $parameters = null)
	{
		// Assembles and hashes the authentication token
		$authentication = md5($this->secret_key . ':' . $authentication);

		// Assembles any additional parameters
		$additional = '';

		if ($parameters && is_array($parameters))
		{
			foreach ($parameters as $key => $value)
			{
				$additional .= '&' . $key . '=' . $value;
			}
		}

		// Executes the API call
		$results = file_get_contents('http://tinychat.apigee.com/' . $codephrase . '?result=json&key=' . $this->public_key . '&auth=' . $authentication . $additional);

		return json_decode($results, true);
	}

	/**
	 * List Rooms
	 *
	 * Pulls all rooms for the API application.
	 *
	 * @return array API results
	 */
	public function listRooms()
	{
		return $this->execute('roomlist', 'roomlist');
	}

	/**
	 * Room Info
	 *
	 * Pulls the information for a room.
	 *
	 * @param string $room name of the room
	 * @param boolean $with_ip whether or not to include users IP addresses
	 * @return array API results
	 */
	public function roomInfo($room, $with_ip = false)
	{
		return $this->execute('roominfo', $room . ':roominfo', array('room' => $room, 'with_ip' => ($with_ip ? 1 : 0)));
	}

	/**
	 * Set Room Password
	 *
	 * Sets the password for the room, only users with the correct password
	 * will be able to enter.
	 *
	 * @param string $room name of the room
	 * @param string $password password to use, blank for no password
	 * @return array API results
	 */
	public function setRoomPassword($room, $password = '')
	{
		return $this->execute('setroompassword', $room . ':setroompassword', array('room' => $room, 'password' => $password));
	}

	/**
	 * Set Broadcast Password
	 *
	 * Sets the password to allow broadcasting in the room. Only users with the
	 * correct password will be able to broadcast.
	 *
	 * @param string $room name of the room
	 * @param string $password password to use, blank for no password
	 * @return array API results
	 */
	public function setBroadcastPassword($room, $password = '')
	{
		return $this->execute('setbroadcastpassword', $room . ':setbroadcastpassword', array('room' => $room, 'password' => $password));
	}

	/**
	 * Generate HTML
	 *
	 * Creates the HTML to place a chat on a site.
	 *
	 * @todo List params...
	 * @return array API results
	 */
	public function generateHTML($room, $join = false, $nick = false, $change = false, $login = false, $oper = false, $owner = false, $bcast = false, $api = false, $colorbk = false, $tcdisplay = false, $autoop = false, $urlsuper = false, $langdefault = false)
	{
		return '
			<script type="text/javascript">
				var tinychat = {'
					. 'room: "' . $room . '",'
					. ($join        ? 'join: "auto",'                        : '')
					. ($nick        ? 'nick: "' . $nick . '",'               : '')
					. ($change      ? 'change: "none",'                      : '')
					. ($login       ? 'login: "' . $login . '",'             : '')
					. ($oper        ? 'oper: "none",'                        : '')
					. ($owner       ? 'owner: "none",'                       : '')
					. ($bcast       ? 'bcast: "restrict",'                   : '')
					. ($api         ? 'api: "' . $api . '",'                 : '')
					. ($colorbk     ? 'colorbk: "' . $colorbk . '",'         : '')
					. ($tcdisplay   ? 'tcdisplay: "vidonly",'                : '')
					/* @todo Implement $autoop, it's an array and needs validated */
					. ($urlsuper    ? 'urlsuper: "' . $urlsuper . '",'       : '')
					. ($langdefault ? 'langdefault: "' . $langdefault . '",' : '')
					. 'key: "' . $this->public_key . '"'
				. '};
			</script>
			<script src="http://tinychat.com/js/embed.js"></script>
			<div id="client"></div>
		';
	}
}

/**
 * Caching System for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Cache Class
 *
 * Wrapper class for Memcache() to allow for better error handling when the
 * Memcached server is unavailable. Designed around the syntax for Memcached()
 * to allow for an easier transistion to the aforementioned in the future. I
 * don't entirely remember specifics, but the reason for not using Memcached()
 * was due to an unexplainable bug in the version in the repository for Ubuntu
 * 10.04 LTS. Memcached() does support more of the memcached protocol and will
 * eventually be what PICKLES uses.
 *
 * Requires php5-memcache
 *
 * @link http://us.php.net/manual/en/book.memcache.php
 * @link http://packages.ubuntu.com/lucid/php5-memcache
 * @link http://www.memcached.org/
 */
class Cache extends Object
{
	/**
	 * Hostname for the Memcached Server
	 *
	 * @access private
	 * @var    string
	 */
	private $hostname = null;

	/**
	 * Port to use to connect
	 *
	 * @access private
	 * @var    integer
	 */
	private $port = null;

	/**
	 * Connection resource to Memcached
	 *
	 * @access private
	 * @var    object
	 */
	private $connection = null;

	/**
	 * Constructor
	 *
	 * Sets up our connection variables.
	 *
	 * @param string $hostname optional hostname to connect to
	 * @param string $database optional port to use
	 */
	public function __construct($hostname = null, $port = null)
	{
		parent::__construct();

		if ($this->config->pickles['cache'])
		{
			if (isset($this->config->datasources[$this->config->pickles['cache']]))
			{
				$datasource = $this->config->datasources[$this->config->pickles['cache']];

				if (isset($datasource['hostname'], $datasource['port']))
				{
					$this->hostname = $datasource['hostname'];
					$this->port     = $datasource['port'];
				}
			}
		}
	}

	/**
	 * Destructor
	 *
	 * Closes the connection when the object dies.
	 */
	public function __destruct()
	{
		if ($this->connection)
		{
			$this->connection->close();
		}
	}

	/**
	 * Get Instance
	 *
	 * Let's the parent class do all the work.
	 *
	 * @static
	 * @param  string $class name of the class to instantiate
	 * @return object self::$instance instance of the Cache class
	 */
	public static function getInstance($class = 'Cache')
	{
		return parent::getInstance($class);
	}

	/**
	 * Opens Connection
	 *
	 * Establishes a connection to the memcached server.
	 */
	public function open()
	{
		if ($this->connection === null)
		{
			$this->connection = new Memcache();
			$this->connection->connect($this->hostname, $this->port);
		}

		return true;
	}

	/**
	 * Get Key
	 *
	 * Gets the value of the key and returns it.
	 *
	 * @param  string $key key to retrieve
	 * @return mixed  value of the requested key, false if not set
	 */
	public function get($key)
	{
		if ($this->open())
		{
			return $this->connection->get($key);
		}

		return false;
	}

	/**
	 * Set Key
	 *
	 * Sets key to the specified value. I've found that compression can lead to
	 * issues with integers and can slow down the storage and retrieval of data
	 * (defeats the purpose of caching if you ask me) and isn't supported. I've
	 * also been burned by data inadvertantly being cached for infinity, hence
	 * the 5 minute default.
	 *
	 * @param  string  $key key to set
	 * @param  mixed   $value value to set
	 * @param  integer $expiration optional expiration, defaults to 5 minutes
	 * @return boolean status of writing the data to the key
	 */
	public function set($key, $value, $expire = 300)
	{
		if ($this->open())
		{
			return $this->connection->set($key, $value, 0, $expire);
		}

		return false;
	}

	/**
	 * Delete Key
	 *
	 * Deletes the specified key.
	 *
	 * @param  string $key key to delete
	 * @return boolean status of deleting the key
	 */
	public function delete($key)
	{
		if ($this->open())
		{
			return $this->connection->delete($key);
		}

		return false;
	}

	/**
	 * Increment Key
	 *
	 * Increments the value of an existing key.
	 *
	 * @param  string $key key to increment
	 * @return boolean status of incrementing the key
	 * @todo   Wondering if I should check the key and set to 1 if it's new
	 */
	public function increment($key)
	{
		if ($this->open())
		{
			return $this->connection->increment($key);
		}

		return false;
	}
}

/**
 * Configuration Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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

		// Generate a generic "site down" message if the site is set to be disabled
		if (isset($this->config->pickles['disabled']) && $this->config->pickles['disabled'] == true)
		{
			Error::fatal($_SERVER['SERVER_NAME'] . ' is currently<br />down for maintenance');
		}

		$_REQUEST['request'] = trim($_REQUEST['request']);

		// Checks the passed request for validity
		if ($_REQUEST['request'])
		{
			// Catches requests that aren't lowercase
			$lowercase_request = strtolower($_REQUEST['request']);

			if ($_REQUEST['request'] != $lowercase_request)
			{
				header('Location: ' . substr_replace($_SERVER['REQUEST_URI'], $lowercase_request, 1, strlen($lowercase_request)), true, 301);
				exit;
			}

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
		if ($module->secure == false && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
		{
			header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}
		elseif ($module->secure == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
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
					$_SESSION['__pickles']['login']['destination'] = $_REQUEST['request'] ? $_REQUEST['request'] : '/';

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
			if (!$_REQUEST['request'])
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

			// Sets meta data from the module
			$display->setMetaData(array(
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			));
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * Common Database Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Common Database Abstraction Layer
 *
 * Parent class that our database driver classes should be extending. Contains
 * basic functionality for instantiation and interfacing.
 */
abstract class Database_Common extends Object
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = null;

	/**
	 * Hostname for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $hostname = 'localhost';

	/**
	 * Port number for the server
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $port = null;

	/**
	 * UNIX socket for the server
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $socket = null;

	/**
	 * Username for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $username = null;

	/**
	 * Password for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $password = null;

	/**
	 * Database name for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $database = null;

	/**
	 * Whether or not to use caching
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $cache = false;

	/**
	 * Connection resource
	 *
	 * @access protected
	 * @var    object
	 */
	protected $connection = null;

	/**
	 * Results object for the executed statement
	 *
	 * @access protected
	 * @var    object
	 */
	protected $results = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Checks the driver is set and available
		if ($this->driver == null)
		{
			throw new Exception('Driver name is not set');
		}
		else
		{
			if (extension_loaded($this->driver) == false)
			{
				throw new Exception('Driver "' . $this->driver . '" is not loaded');
			}
		}
	}

	/**
	 * Set Hostname
	 *
	 * @param string $hostname hostname for the database
	 */
	public function setHostname($hostname)
	{
		return $this->hostname = $hostname;
	}

	/**
	 * Set Port
	 *
	 * @param integer $port port for the database
	 */
	public function setPort($port)
	{
		return $this->port = $port;
	}

	/**
	 * Set Socket
	 *
	 * @param string $socket name of the UNIX socket
	 */
	public function setSocket($socket)
	{
		return $this->socket = $socket;
	}

	/**
	 * Set Username
	 *
	 * @param string $username username for the database
	 */
	public function setUsername($username)
	{
		return $this->username = $username;
	}

	/**
	 * Set Password
	 *
	 * @param string $password password for the database
	 */
	public function setPassword($password)
	{
		return $this->password = $password;
	}

	/**
	 * Set Database
	 *
	 * @param string $database database for the database
	 */
	public function setDatabase($database)
	{
		return $this->database = $database;
	}

	/**
	 * Set Cache
	 *
	 * @param boolean whether or not to use cache
	 */
	public function setCache($cache)
	{
		return $this->cache = $cache;
	}

	/**
	 * Get Driver
	 *
	 * Returns the name of the driver in use. Used by the Model class to
	 * determine which path to take when interfacing with the Database object.
	 *
	 * @return string name of the driver in use
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Get Cache
	 *
	 * Returns the status of caching for this datasource.
	 *
	 * @return string whether or not to use the cache
	 */
	public function getCache()
	{
		return $this->cache;
	}

	/**
	 * Opens database connection
	 *
	 * Establishes a connection to the MySQL database based on the
	 * configuration options that are available in the Config object.
	 *
	 * @abstract
	 * @return   boolean true on success, throws an exception overwise
	 */
	abstract public function open();

	/**
	 * Closes database connection
	 *
	 * Sets the connection to null regardless of state.
	 *
	 * @return boolean always true
	 */
	abstract public function close();
}

/**
 * PDO Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * PDO Abstraction Layer
 *
 * Parent class for any of our database classes that use PDO.
 */
class Database_PDO_Common extends Database_Common
{
	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn;

	/**
	 * PDO Attributes
	 *
	 * @access protected
	 * @var    string
	 */
	protected $attributes = array(
		PDO::ATTR_PERSISTENT   => true,
		PDO::ATTR_ERRMODE      => PDO::ERRMODE_EXCEPTION,
		PDO::NULL_EMPTY_STRING => true
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Checks that the prefix is set
		if ($this->dsn == null)
		{
			throw new Exception('Data source name is not defined');
		}

		// This combats a bug: https://bugs.php.net/bug.php?id=62571&edit=1
		if ($this->driver == 'pdo_pgsql')
		{
			$this->attributes[PDO::ATTR_PERSISTENT] = false;
		}
	}

	/**
	 * Opens database connection
	 *
	 * Establishes a connection to the database based on the set configuration
	 * options.
	 *
	 * @return boolean true on success, throws an exception overwise
	 */
	public function open()
	{
		if ($this->connection === null)
		{
			if (isset($this->username, $this->password, $this->database))
			{
				// Creates a new PDO database object (persistent)
				try
				{
					// Swaps out any variables with values in the DSN
					$this->dsn = str_replace(
						array('[[hostname]]', '[[port]]', '[[socket]]', '[[username]]', '[[password]]', '[[database]]'),
						array($this->hostname, $this->port, $this->socket, $this->username, $this->password, $this->database),
						$this->dsn
					);

					// Strips any empty parameters in the DSN
					$this->dsn = str_replace(array('host=;', 'port=;', 'unix_socket=;'), '', $this->dsn);

					// Attempts to establish a connection
					$this->connection = new PDO($this->dsn,	$this->username, $this->password, $this->attributes);
				}
				catch (PDOException $e)
				{
					throw new Exception($e);
				}
			}
			else
			{
				throw new Exception('There was an error loading the database configuration');
			}
		}

		return true;
	}

	/**
	 * Closes database connection
	 *
	 * Sets the connection to null regardless of state.
	 *
	 * @return boolean always true
	 */
	public function close()
	{
		$this->connection = null;
		return true;
	}

	/**
	 * Executes an SQL Statement
	 *
	 * Executes a standard or prepared query based on passed parameters. All
	 * queries are logged to a file as well as timed and logged in the
	 * execution time is over 1 second.
	 *
	 * @param  string $sql statement to execute
	 * @param  array $input_parameters optional key/values to be bound
	 * @return integer ID of the last inserted row or sequence number
	 */
	public function execute($sql, $input_parameters = null)
	{
		$this->open();

		if ($this->config->pickles['logging'] === true)
		{
			$loggable_query = $sql;

			if ($input_parameters != null)
			{
				$loggable_query .= ' -- ' . (JSON_AVAILABLE ? json_encode($input_parameters) : serialize($input_parameters));
			}

			Log::query($loggable_query);
		}

		$sql = trim($sql);

		// Checks if the query is blank
		if ($sql != '')
		{
			$files     = array();
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			krsort($backtrace);

			foreach ($backtrace as $file)
			{
				$files[] = $file['class'] . ':' . $file['line'];
			}

			$sql .= "\n" . '/* [' . implode('|', $files) . '] */';

			try
			{
				// Establishes if we're working on an EXPLAIN
				if (Profiler::enabled('explains') == true)
				{
					$explaining = preg_match('/^EXPLAIN /i', $sql);
					$selecting  = preg_match('/^SELECT /i',  $sql);
				}
				else
				{
					$explaining = null;
					$selecting  = null;
				}

				// Executes a standard query
				if ($input_parameters === null)
				{
					// Explains the query
					if ($selecting == true && $explaining == false)
					{
						$explain = $this->fetch('EXPLAIN ' . $sql);
					}

					$start_time    = microtime(true);
					$this->results = $this->connection->query($sql);
				}
				// Executes a prepared statement
				else
				{
					// Explains the query
					if ($selecting == true && $explaining == false)
					{
						$explain = $this->fetch('EXPLAIN ' . $sql, $input_parameters);
					}

					$start_time    = microtime(true);
					$this->results = $this->connection->prepare($sql);
					$this->results->execute($input_parameters);
				}

				$end_time = microtime(true);
				$duration = $end_time - $start_time;

				if ($this->config->pickles['logging'] === true && $duration >= 1)
				{
					Log::slowQuery($duration . ' seconds: ' . $loggable_query);
				}

				// Logs the information to the profiler
				if ($explaining == false && Profiler::enabled('explains', 'queries'))
				{
					Profiler::logQuery($sql, $input_parameters, (isset($explain) ? $explain : false), $duration);
				}
			}
			catch (PDOException $e)
			{
				throw new Exception($e);
			}
		}
		else
		{
			throw new Exception('No query to execute');
		}

		return $this->connection->lastInsertId();
	}

	/**
	 * Fetch records from the database
	 *
	 * @param  string $sql statement to be executed
	 * @param  array $input_parameters optional key/values to be bound
	 * @param  string $return_type optional type of return set
	 * @return mixed based on return type
	 */
	public function fetch($sql = null, $input_parameters = null)
	{
		$this->open();

		if ($sql !== null)
		{
			$this->execute($sql, $input_parameters);
		}

		// Pulls the results based on the type
		$results = $this->results->fetchAll(PDO::FETCH_ASSOC);

		return $results;
	}
}

/**
 * MySQL Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * MySQL Database Abstraction Layer
 */
class Database_PDO_MySQL extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'pdo_mysql';

	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn = 'mysql:host=[[hostname]];port=[[port]];unix_socket=[[socket]];dbname=[[database]]';

	/**
	 * Default port
	 *
	 * @access proceted
	 * @var    integer
	 */
	protected $port = 3306;
}

/**
 * PostgreSQL Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * PostgreSQL Database Abstraction Layer
 */
class Database_PDO_PostgreSQL extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'pdo_pgsql';

	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn = 'pgsql:host=[[hostname]];port=[[port]];dbname=[[database]];user=[[username]];password=[[password]]';

	/**
	 * Default port
	 *
	 * @access proceted
	 * @var    integer
	 */
	protected $port = 5432;
}

/**
 * SQLite Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * SQLite Database Abstraction Layer
 */
class Database_PDO_SQLite extends Database_PDO_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'pdo_sqlite';

	/**
	 * DSN format
	 *
	 * @access protected
	 * @var    string
	 */
	protected $dsn = 'sqlite:[[hostname]]';
}

/**
 * Database Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Get Instance
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

					if (isset($datasource['cache']))
					{
						$instance->setCache($datasource['cache']);
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Date Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant date related manipulation.
 */
class Date
{
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
		return Time::age($date);
	}
}

/**
 * Common Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Common Display Class
 *
 * This is the parent class class that each display class should be
 * extending and executing parent::render()
 */
abstract class Display_Common extends Object
{
	/**
	 * Template Extension
	 *
	 * @access protected
	 * @var    string $extension file extension for the template files
	 */
	protected $extension = null;

	/**
	 * Parent Template
	 *
	 * @access protected
	 * @var    string
	 */
	protected $parent_template = null;

	/**
	 * Child (sub) Template
	 *
	 * @access protected
	 * @var    string
	 */
	protected $child_template = null;

	/**
	 * CSS Class Name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $css_class = '';

	/**
	 * Javascript [Path and] Basename
	 *
	 * @access protected
	 * @var    array
	 */
	protected $js_basename = '';

	/**
	 * Meta Data
	 *
	 * @access protected
	 * @var    array
	 */
	protected $meta_data = null;

	/**
	 * Module Return Data
	 *
	 * @access protected
	 * @var    array
	 */
	protected $module_return = null;

	/**
	 * Constructor
	 *
	 * Gets those headers working
	 */
	public function __construct()
	{
		parent::__construct();

		// Obliterates any passed in PHPSESSID (thanks Google)
		if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false)
		{
			list($request_uri, $phpsessid) = explode('?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $request_uri);
			exit;
		}
		else
		{
			// XHTML compliancy stuff
			ini_set('arg_separator.output', '&amp;');
			ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

			header('Content-type: text/html; charset=UTF-8');
		}
	}

	/**
	 * Set Template
	 *
	 * Sets the template file based on passed template type. Adds path and
	 * extension if applicable.
	 *
	 * @param string $template template file's basename
	 * @param string $type template file's type (either parent or child)
	 */
	private function setTemplate($template, $type)
	{
		if ($template != null)
		{
			$template_name = $type . '_template';
			$template_path = SITE_TEMPLATE_PATH . ($type == 'parent' ? '__shared/' : '');
			$template_file = $template_path . $template . ($this->extension != false ? '.' . $this->extension : '');

			if (file_exists($template_file))
			{
				$this->$template_name = $template_file;
			}
		}
	}

	/**
	 * Set Template Variables
	 *
	 * Sets the variables used by the templates
	 *
	 * @param string $parent_template parent template
	 * @param string $child_template child (sub) template
	 * @param string $css_class name of the CSS class for the module
	 * @param string $js_basename basename for the javascript file for the module
	 */
	public function setTemplateVariables($parent_template, $child_template, $css_class, $js_basename)
	{
		$this->setTemplate($parent_template, 'parent');
		$this->setTemplate($child_template,  'child');

		$this->css_class   = $css_class;
		$this->js_basename = $js_basename;
	}

	/**
	 * Set Meta Data
	 *
	 * Sets the meta data from the module so the display class can use it
	 *
	 * @param array $meta_data key/value array of data
	 */
	public function setMetaData($meta_data)
	{
		$this->meta_data = $meta_data;
	}

	/**
	 * Set Module Return
	 *
	 * Sets the return data from the module so the display class can display it
	 *
	 * @param array $module_return key / value pairs for the data
	 */
	public function setModuleReturn($module_return)
	{
		$this->module_return = $module_return;
	}

	/**
	 * Template Exists
	 *
	 * Checks the templates for validity, not required by every display type so
	 * the return defaults to true.
	 *
	 * @return boolean whether or not the template exists
	 */
	public function templateExists()
	{
		return true;
	}

	/**
	 * Rendering Method
	 *
	 * @abstract
	 */
	abstract public function render();
}

/**
 * JSON Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * JSON Display
 *
 * Displays data in JavaScript Object Notation.
 */
class Display_JSON extends Display_Common
{
	/**
	 * Renders the data in JSON format
	 */
	public function render()
	{
		echo Convert::toJSON($this->module_return);
	}
}

/**
 * PHP Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * PHP Display
 *
 * Displays the associated PHP templates for the Model.
 */
class Display_PHP extends Display_Common
{
	/**
	 * Template Extension
	 *
	 * I know there's some controversy amoungst my peers concerning the
	 * usage of the .phtml extension for these PHP template files. If you
	 * would prefer .php or .tpl extensions, feel free to void your
	 * warranty and change it here.
	 *
	 * @access protected
	 * @var    string $extension file extension for the template files
	 */
	protected $extension = 'phtml';

	/**
	 * Template Exists
	 *
	 * @return integer the number of templates defined
	 */
	public function templateExists()
	{
		if ($this->parent_template != null)
		{
			return file_exists($this->parent_template) && file_exists($this->child_template);
		}
		else
		{
			return file_exists($this->child_template);
		}
	}

	/**
	 * Renders the PHP templated pages
	 */
	public function render()
	{
		if ($this->templateExists())
		{
			// Starts up the buffer
			ob_start();

			// Puts the class variables in local scope of the template
			$__config    = $this->config;
			$__meta      = $this->meta_data;
			$__module    = $this->module_return;
			$__css_class = $this->css_class;
			$__js_file   = $this->js_basename;

			// Creates (possibly overwritten) objects
			$form_class    = (class_exists('CustomForm')    ? 'CustomForm'    : 'Form');
			$dynamic_class = (class_exists('CustomDynamic') ? 'CustomDynamic' : 'Dynamic');

			$__form    = new $form_class();
			$__dynamic = new $dynamic_class();

			// Loads the template
			if ($this->parent_template != null)
			{
				if ($this->child_template == null)
				{
					$__template = $this->parent_template;
				}
				else
				{
					$__template = $this->child_template;
				}

				require_once $this->parent_template;
			}
			elseif ($this->child_template != null)
			{
				$__template = $this->child_template;

				require_once $__template;
			}

			// Grabs the buffer contents and clears it out
			$buffer = ob_get_clean();

			// Kills any whitespace and HTML comments
			$buffer = preg_replace(array('/^[\s]+/m', '/<!--.*-->/U'), '', $buffer);

			// Note, this doesn't exit in case you want to run code after the display of the page
			echo $buffer;
		}
		else
		{
			echo Convert::toJSON($this->module_return);
		}
	}
}

/**
 * RSS Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * RSS Display
 *
 * Displays data as an RSS formatted XML string.
 */
class Display_RSS extends Display_Common
{
	// {{{ Feed Defaults

	/**
	 * RSS Version
	 *
	 * @access private
	 * @var    string
	 */
	private $version = '2.0';

	/**
	 * Date Format
	 *
	 * @access private
	 * @var    string
	 */
	private $date_format = 'r';

	// }}}

	// {{{ Channel Defaults

	/**
	 * Title
	 *
	 * @access private
	 * @var    string
	 */
	private $title = '';

	/**
	 * Link
	 *
	 * @access private
	 * @var    string
	 */
	private $link = '';

	/**
	 * Description
	 *
	 * @access private
	 * @var    string
	 */
	private $description = '';

	/**
	 * Language
	 *
	 * @access private
	 * @var    string
	 */
	private $language = 'en-us';

	/**
	 * Generator
	 *
	 * @access private
	 * @var    string
	 */
	private $generator = 'https://github.com/joshtronic/pickles';

	// }}}

	/**
	 * Renders the data in RSS format
	 */
	public function render()
	{
		// Throws off the syntax highlighter otherwise
		echo '<' . '?xml version="1.0" ?' . '><rss version="' . $this->version . '"><channel>';

		// Loops through the passable channel variables
		$channel = array();
		foreach (array('title', 'link', 'description', 'language') as $variable)
		{
			if (isset($this->module_return[$variable]))
			{
				$this->$variable = $this->module_return[$variable];
			}

			$channel[$variable] = $this->$variable;
		}

		$channel['generator'] = $this->generator;

		// Loops through the items
		$items      = '';
		$build_date = '';
		if (isset($this->module_return['items']) && is_array($this->module_return['items']))
		{
			foreach ($this->module_return['items'] as $item)
			{
				// Note: time is the equivalent to pubDate, I just don't like camel case variables
				$publish_date = date($this->date_format, is_numeric($item['time']) ? $item['time'] : strtotime($item['time']));

				if ($build_date == '')
				{
					$build_date = $publish_date;
				}

				if (isset($item['link']))
				{
					$item['guid'] = $item['link'];
				}

				$item['pubDate'] = $publish_date;

				unset($item['time']);

				$items .= Convert::arrayToXML($item);
			}
		}

		$channel['pubDate']       = $build_date;
		$channel['lastBuildDate'] = $build_date;

		echo Convert::arrayToXML($channel) . $items . '</channel></rss>';
	}
}

/**
 * XML Display Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * XML Display
 *
 * Displays data in XML format.
 */
class Display_XML extends Display_Common
{
	/**
	 * Renders the data in XML format
	 */
	public function render()
	{
		echo Convert::arrayToXML($this->module_return);
	}
}

/**
 * Dynamic Content Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
			$query_string = '';

			// Checks for ? and extracts query string
			if (strstr($reference, '?'))
			{
				list($reference, $query_string) = explode('?', $reference);
			}

			// Adds the dot so the file functions can find the file
			$file = '.' . $reference;

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
					throw new Exception('Supplied reference does not exist (' . $reference . ')');
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

			if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)) && $this->config->pickles['minify'] === true)
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

					if (is_writable($path) && (!file_exists($minified_filename) || filemtime($original_filename) > filemtime($minified_filename)) && extension_loaded('curl') && $this->config->pickles['minify'] === true)
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
		$config = Config::getInstance();

		if ($config->pickles['logging'] === true)
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
				<a href="https://github.com/joshtronic/pickles" target="_blank">Powered by PICKLES</a>
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Columns
	 *
	 * Mapping of key columns for the table
	 *
	 * @access protected
	 * @var    array
	 */
	protected $columns = array(
		'id'         => 'id',
		'created_at' => 'created_at',
		'created_id' => 'created_id',
		'updated_at' => 'updated_at',
		'updated_id' => 'updated_id',
		'deleted_at' => 'deleted_at',
		'deleted_id' => 'deleted_id',
		'is_deleted' => 'is_deleted',
	);

	/**
	 * Cache Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $cache = null;

	/**
	 * Whether or not to use cache
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $use_cache = false;

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
	 * Insert Priority
	 *
	 * Defaults to false (normal priority) but can be set to "low" or "high"
	 *
	 * @access protected
	 * @var    string
	 */
	protected $priority = false;

	/**
	 * Delayed Insert
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $delayed = false;

	/**
	 * Ignore Unique Index
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $ignore = false;

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

	/**
	 * Snapshot
	 *
	 * Snapshot of the object properties
	 *
	 * @access private
	 * @var    array
	 */
	private $snapshot = array();

	/**
	 * MySQL?
	 *
	 * Whether or not we're using MySQL
	 *
	 * @access private
	 * @var    boolean
	 */
	private $mysql = false;

	/**
	 * PostgreSQL?
	 *
	 * Whether or not we're using PostgreSQL
	 *
	 * @access private
	 * @var    boolean
	 */
	private $postgresql = false;

	// }}}
	// {{{ Class Constructor

	/**
	 * Constructor
	 *
	 * Creates a new (empty) object or populates the record set.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function __construct($type_or_parameters = null, $parameters = null)
	{
		// Errors if a table is not set. You're welcome, Geoff.
		if ($this->table == false)
		{
			throw new Exception('You must set the table variable');
		}

		// Runs the parent constructor so we have the config
		parent::__construct();

		// Gets an instance of the cache and database
		// @todo Datasource has no way of being set
		$this->db         = Database::getInstance($this->datasource != '' ? $this->datasource : null);
		$this->caching    = $this->db->getCache();
		$this->mysql      = ($this->db->getDriver() == 'pdo_mysql');
		$this->postgresql = ($this->db->getDriver() == 'pdo_pgsql');

		if ($this->caching)
		{
			$this->cache = Cache::getInstance();
		}

		// Takes a snapshot of the [non-object] object properties
		foreach ($this as $variable => $value)
		{
			if (!in_array($variable, array('db', 'cache', 'config', 'snapshot')))
			{
				$this->snapshot[$variable] = $value;
			}
		}

		return $this->execute($type_or_parameters, $parameters);
	}

	// }}}
	// {{{ Database Execution Methods

	/**
	 * Execute
	 *
	 * Potentially populates the record set from the passed arguments.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function execute($type_or_parameters = null, $parameters = null)
	{
		// Resets internal properties
		foreach ($this->snapshot as $variable => $value)
		{
			$this->$variable = $value;
		}

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
				$this->loadParameters(array($this->columns['id'] => $type_or_parameters));
				$cache_key = 'PICKLES-' . $this->datasource . '-' . $this->table . '-' . $type_or_parameters;
			}
			elseif (ctype_digit((string)$parameters))
			{
				$this->loadParameters(array($this->columns['id'] => $parameters));
			}

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

			$query_database = true;

			if (isset($cache_key))
			{
				//$cached = $this->cache->get($cache_key);
			}

			if (isset($cached) && $cached)
			{
				$this->records = $cached;
			}
			else
			{
				$this->records = $this->db->fetch(implode(' ', $this->sql), (count($this->input_parameters) == 0 ? null : $this->input_parameters));

				if (isset($cache_key))
				{
					//$this->cache->set($cache_key, $this->records);
				}
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
			$update = (isset($this->record[$this->columns['id']]) && trim($this->record[$this->columns['id']]) != '');

			// Starts to build the query, optionally sets PRIORITY, DELAYED and IGNORE syntax
			if ($this->replace === true && $this->mysql)
			{
				$sql = 'REPLACE';

				if (strtoupper($this->priority) == 'LOW')
				{
					$sql .= ' LOW_PRIORITY';
				}
				elseif ($this->delayed == true)
				{
					$sql .= ' DELAYED';
				}

				$sql .= ' INTO ' . $this->table . ' SET ';
			}
			else
			{
				if ($update === true)
				{
					$sql = 'UPDATE';
				}
				else
				{
					$sql = 'INSERT';

					// PRIORITY syntax takes priority over DELAYED
					if ($this->mysql)
					{
						if ($this->priority !== false && in_array(strtoupper($this->priority), array('LOW', 'HIGH')))
						{
							$sql .= ' ' . strtoupper($this->priority) . '_PRIORITY';
						}
						elseif ($this->delayed == true)
						{
							$sql .= ' DELAYED';
						}

						if ($this->ignore == true)
						{
							$sql .= ' IGNORE';
						}
					}

					$sql .= ' INTO';
				}

				$sql .= ' ' . $this->table . ($update === true ? ' SET ' : ' ');
			}

			$input_parameters = null;

			// Limits the columns being updated
			$record = ($update === true ? array_diff_assoc($this->record, isset($this->original[$this->index]) ? $this->original[$this->index] : array()) : $this->record);

			// Makes sure there's something to INSERT or UPDATE
			if (count($record) > 0)
			{
				$insert_fields = array();

				// Loops through all the columns and assembles the query
				foreach ($record as $column => $value)
				{
					if ($column != $this->columns['id'])
					{
						if ($update === true)
						{
							if ($input_parameters != null)
							{
								$sql .= ', ';
							}

							$sql .= $column . ' = ?';
						}
						else
						{
							$insert_fields[] = $column;
						}

						$input_parameters[] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
					}
				}

				// If it's an UPDATE tack on the ID
				if ($update === true)
				{
					if ($this->columns['updated_at'] != false)
					{
						if ($input_parameters != null)
						{
							$sql .= ', ';
						}

						$sql                .= $this->columns['updated_at'] . ' = ?';
						$input_parameters[]  = Time::timestamp();
					}

					if ($this->columns['updated_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						if ($input_parameters != null)
						{
							$sql .= ', ';
						}

						$sql                .= $this->columns['updated_id'] . ' = ?';
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
					}

					$sql                .= ' WHERE ' . $this->columns['id'] . ' = ?' . ($this->mysql ? ' LIMIT 1' : '') . ';';
					$input_parameters[]  = $this->record[$this->columns['id']];

					if ($this->caching)
					{
						//$this->cache->delete('PICKLES-' . $this->datasource . '-' . $this->table . '-' . $this->record[$this->columns['id']]);
					}
				}
				else
				{
					if ($this->columns['created_at'] != false)
					{
						$insert_fields[]    = $this->columns['created_at'];
						$input_parameters[] = Time::timestamp();
					}

					if ($this->columns['created_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						$insert_fields[]    = $this->columns['created_id'];
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
					}

					$sql .= '(' . implode(', ', $insert_fields) . ') VALUES (' . implode(', ', array_fill(0, count($input_parameters), '?')) . ')';

					// PDO::lastInsertId() doesn't work so we return the ID with the query
					if ($this->postgresql)
					{
						$sql .= ' RETURNING ' . $this->columns['id'];
					}

					$sql .= ';';
				}

				// Executes the query
				if ($this->postgresql && $update === false)
				{
					$results = $this->db->fetch($sql, $input_parameters);

					return $results[0][$this->columns['id']];
				}
				else
				{
					return $this->db->execute($sql, $input_parameters);
				}
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
		if (isset($this->record[$this->columns['id']]))
		{
			// Logical deletion
			if ($this->columns['is_deleted'])
			{
				$sql              = 'UPDATE ' . $this->table . ' SET ' . $this->columns['is_deleted'] . ' = ?';
				$input_parameters = array('1');

				if ($this->columns['deleted_at'])
				{
					$sql                .= ', ' . $this->columns['deleted_at'] . ' = ?';
					$input_parameters[]  = Time::timestamp();
				}

				if ($this->columns['deleted_id'] && isset($_SESSION['__pickles']['security']['user_id']))
				{
					$sql                .= ', ' . $this->columns['deleted_id'] . ' = ?';
					$input_parameters[]  = $_SESSION['__pickles']['security']['user_id'];
				}

				$sql .= ' WHERE ' . $this->columns['id'] . ' = ?';
			}
			// For reals deletion
			else
			{
				$sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->columns['id'] . ' = ?' . ($this->mysql ? ' LIMIT 1' : '') . ';';
			}

			$input_parameters[] = $this->record[$this->columns['id']];

			return $this->db->execute($sql, $input_parameters);
		}
		else
		{
			return false;
		}
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Cache Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $cache = null;

	/**
	 * Database Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Page Title
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $title = null;

	/**
	 * Meta Description
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $description = null;

	/**
	 * Meta Keywords (comma separated)
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
	 * Security Settings
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
	 * Default Display Engine
	 *
	 * Defaults to PHP but could be set to JSON, XML or RSS. Value is
	 * overwritten by the config value if not set by the module.
	 *
	 * @access protected
	 * @var    string, null by default
	 */
	protected $engine = DISPLAY_PHP;

	/**
	 * Default Template
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

		$this->cache = Cache::getInstance();
		$this->db    = Database::getInstance();

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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Generates an SHA1 hash from the provided string. Salt optional.
	 *
	 * @param  string $source value to hash
	 * @param  mixed $salts optional salt or salts
	 * @return string SHA1 hash
	 */
	public static function generateHash($source, $salts = null)
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
		$hash = $source;

		foreach ($salts as $salt)
		{
			$hash = sha1($salt . $hash);
		}

		return $hash;
	}

	/**
	 * SHA-256
	 *
	 * Generates an SHA-256 hash from the provided string.
	 *
	 * @param  string $source value to hash
	 * @return string SHA1 hash
	 */
	public static function sha256($source)
	{
		return hash('sha256', $source);
	}

	/**
	 * Generate SHA-256 Hash
	 *
	 * Generates an SHA-256 hash from the provided string and salt. Borrowed the
	 * large iteration logic from fCryptography::hashWithSalt() as, and I quote,
	 * "makes rainbow table attacks infesible".
	 *
	 * @param  string $source value to hash
	 * @param  mixed $salt value to use as salt
	 * @return string SHA-256 hash
	 * @link   https://github.com/flourishlib/flourish-classes/blob/master/fCryptography.php
	 */
	public static function generateSHA256Hash($source, $salt)
	{
		$sha256 = sha1($salt . $source);

		for ($i = 0; $i < 1000; $i++)
		{
			$sha256 = Security::sha256($sha256 . (($i % 2 == 0) ? $source : $salt));
		}

		return $sha256;
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
		if (!IS_CLI)
		{
			parent::__construct();

			// Sets up our configuration variables
			$session     = $this->config->pickles['session'];
			$datasources = $this->config->datasources;

			$datasource = false;
			$table      = 'sessions';

			if (is_array($session))
			{
				if (isset($session['handler']) && in_array($session['handler'], array('files', 'mysql')))
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
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
				$characters = array_merge($characters, range('a', 'z'));
			}
			else
			{
				$characters = array_merge($characters, range('a', 'h'), range('j', 'n'), range('p', 'z'));
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
				$string .= $characters[array_rand($characters)];
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

/**
 * Time Utility Collection
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Time Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant time and date related manipulation.
 */
class Time
{
	// {{{ Intervals (in seconds)

	/**
	 * Minute
	 *
	 * Seconds in a minute
	 *
	 * @var integer
	 */
	const MINUTE = 60;

	/**
	 * Hour
	 *
	 * Seconds in an hour (minute * 60)
	 *
	 * @var integer
	 */
	const HOUR = 3600;

	/**
	 * Day
	 *
	 * Seconds in a day (hour * 24)
	 *
	 * @var integer
	 */
	const DAY = 86400;

	/**
	 * Week
	 *
	 * Seconds in a week (day * 7)
	 *
	 * @var integer
	 */
	const WEEK = 604800;

	/**
	 * Month
	 *
	 * Seconds in a month (day * 30)
	 *
	 * @var integer
	 */
	const MONTH = 2592000;

	/**
	 * Quarter
	 *
	 * Seconds in a quarter (day * 90)
	 *
	 * @var integer
	 */
	const QUARTER = 7776000;

	/**
	 * Year
	 *
	 * Seconds in a year (day * 365)
	 *
	 * @var integer
	 */
	const YEAR = 31536000;

	/**
	 * Decade
	 *
	 * Seconds in a decade (year * 10)
	 *
	 * @var integer
	 */
	const DECADE = 315360000;

	/**
	 * Century
	 *
	 * Seconds in a decade (decade * 10)
	 *
	 * @var integer
	 */
	const CENTURY = 3153600000;

	// }}}

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

	/**
	 * Timestamp
	 *
	 * Current Universal Time in the specified format.
	 *
	 * @static
	 * @param  string $format format of the timestamp
	 * @return string $timestamp formatted timestamp
	 */
	public static function timestamp($format = 'Y-m-d H:i:s')
	{
		return gmdate($format);
	}
}

?>
