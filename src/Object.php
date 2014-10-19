<?php

/**
 * Parent Object
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      https://github.com/joshtronic/pickles
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
     * Instance of the Mongo object
     *
     * @var object
     */
    public $mongo = null;

    /**
     * Instance of the Redis object
     *
     * @var object
     */
    public $redis = null;

    /**
     * Constructor
     *
     * Establishes a Config instance for all children to enjoy
     */
    public function __construct()
    {
        // @todo Lazy load these so we're not loading them on every instance
        $this->config = Config::getInstance();
        $this->mongo  = Mongo::getInstance();
        //$this->redis  = Redis::getInstance();

        // Optionally logs the constructor to the profiler
        if ($this->config['profiler'])
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
        if ($this->config['profiler'])
        {
            Profiler::log($this, '__destruct');
        }
    }
}

