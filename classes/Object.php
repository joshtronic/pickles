<?php

/**
 * Object Class File for PICKLES
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
 * Object Class
 *
 * Every instantiated class in PICKLES should be extending this class. By doing
 * so the class is automatically hooked into the profiler, and the object will
 * have access to some common components as well.
 *
 * That all being said, PICKLES does have 4 distinct class types, and not all
 * need this class. First, there are true Singleton's which extend the class of
 * the same name. There are also instantiated classes (like Module and Model)
 * which need to be instantiated to be used. Those classes need to extend this
 * class. The 3rd type of class is a static non-Singleton class. They are
 * generally seen with no parent class, and are used for performing stateless
 * actions. Now we have our 4th type of class, and this is where it gets fun!
 * The last class type is a instantiated class with Singleton tendencies. So
 * yeah, lines get blurred for the sake of building a very bendable system.
 * These "hybrid" classes extend the Object class, but can be instantiated or
 * accessed via getInstance(). Why? Well that's simple, I do that so that I can
 * share a single instance of the object within the core of PICKLES, but still
 * have the availability to create new instances of the object for use outside
 * of the core. The Config and Database classes are examples of this type.
 *
 * So guess what, I bend the rules.
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
	private static $instances = array();

	/**
	 * Instance of the Config object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $config = null;

	/**
	 * Constructor
	 *
	 * Establishes a Config instance for all children to enjoy
	 */
	public function __construct()
	{
		if (get_class($this) == 'Config')
		{
			$this->config = true;
		}
		else
		{
			$this->config = Config::getInstance();
		}

		// Optionally logs the constructor to the profiler
		if ($this->config == true || (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true))
		{
			Profiler::log($this, '__construct');
		}
	}

	/**
	 * Get Instance
	 *
	 * Gets an instance of the passed class.
	 *
	 * @static
	 * @param  string $class name of the class
	 * @return object instance of the class
	 */
	public static function getInstance($class)
	{
		if (!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class();
		}

		return self::$instances[$class];
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		// Optionally logs the destructor to the profiler
		if ($this->config == true || (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true))
		{
			Profiler::log($this, '__destruct');
		}
	}
}

?>
