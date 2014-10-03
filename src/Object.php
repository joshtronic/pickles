<?php

/**
 * Parent Object
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      http://picklesphp.com
 * @package   Pickles
 */

namespace Pickles;

/**
 * Object Class
 *
 * Every instantiated class in Pickles should be extending this class. By doing
 * so the class is automatically hooked into the profiler, and the object will
 * have access to some common components as well.
 */
class Object
{
    /**
     * Object Instances
     *
     * @static
     * @var    array
     */
    public static $instances = [];

    /**
     * Instance of the Config object
     *
     * @var object
     */
    public $config = null;

    /**
     * Instance of the Cache object
     *
     * @var object
     */
    public $cache = null;

    /**
     * Instance of the Database object
     *
     * @var object
     */
    public $db = null;

    /**
     * Constructor
     *
     * Establishes a Config instance for all children to enjoy
     */
    public function __construct($objects = null)
    {
        $this->config = Config::getInstance();

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

        // Optionally logs the constructor to the profiler
        if ($this->config['pickles']['profiler'])
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
        if ($class)
        {
            $class = 'Pickles\\' . $class;

            if (!isset(self::$instances[$class]))
            {
                self::$instances[$class] = new $class();
            }

            return self::$instances[$class];
        }

        return false;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        // Optionally logs the destructor to the profiler
        if ($this->config['pickles']['profiler'])
        {
            Profiler::log($this, '__destruct');
        }
    }
}

