<?php

/**
 * Object Class File for PICKLES
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
	 * @var    array
	 */
	protected static $instances = [];

	/**
	 * Instance of the Config object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $config = null;

	/**
	 * Instance of the Cache object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $cache = null;

	/**
	 * Instance of the Database object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Profiler flag
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
	public function __construct($objects = null)
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

		if ($objects)
		{
			if (!is_array($objects))
			{
				$objects = [$objects];
			}

			foreach ($objects as $object)
			{
				switch ($object)
				{
					case 'cache': $this->cache = Cache::getInstance();    break;
					case 'db':    $this->db    = Database::getInstance(); break;
				}
			}
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

?>
