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
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

namespace Pickles;

/**
 * Config Class
 *
 * Handles loading the site's configuration file (if available). At the moment
 * this class is a very skewed Singleton. The plan is to eventually extend this
 * out to support multiple configuration files, and the ability to load in
 * custom config files on the fly as well. The core of PICKLES uses the class
 * as a Singleton so we're not loading the configuration multiple times per
 * page load.
 */
class Config extends \ArrayObject
{
    private static $_instance = false;

    /**
     * Constructor
     *
     * Calls the parent constructor and loads the passed file.
     */
    public function __construct()
    {
        ini_set('display_errors', true);
        error_reporting(-1);

        $filename     = getcwd() . '/../../pickles.php';
        $environments = false;
        $environment  = false;
        $cli          = PHP_SAPI == 'cli';

        // Only require in case you want to reload the config
        require $filename;

        // Checks that we have the config array
        if (!isset($config))
        {
            throw new \Exception('Missing $config array.');
        }

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
                if ($cli && $_SERVER['argc'] < 2)
                {
                    throw new \Exception('You must pass an environment (e.g. php script.php <environment>)');
                }

                // Loops through the environments and looks for a match
                foreach ($config['environments'] as $name => $hosts)
                {
                    if (!is_array($hosts))
                    {
                        $hosts = [$hosts];
                    }

                    // Tries to determine the environment name
                    foreach ($hosts as $host)
                    {
                        if ($cli)
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
                                || (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == $host))
                            {
                                $environment = $name;
                                break;
                            }
                            // Fuzzy match
                            elseif (substr($host,0,1) == '/'
                                && (preg_match($host, $_SERVER['SERVER_NAME'], $matches) > 0
                                || preg_match($host, $_SERVER['HTTP_HOST'], $matches) > 0))
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

            // Flattens the array based on the environment
            $config = $this->flatten($environment, $config);

            // Restore environments value
            if ($environments != false)
            {
                $config['environments'] = $environments;
            }

            // Sets the environment if it's not set already
            if (!isset($config['environment']))
            {
                $config['environment'] = $environment;
            }

            // Disable display errors in production
            if ($environment == 'production')
            {
                ini_set('display_errors', false);
            }

            // Defaults expected Pickles options to false
            $this['pickles'] = [
                'cache'    => false,
                'profiler' => false,
            ];

            // Assigns the config variables to the object
            foreach ($config as $variable => $value)
            {
                $this[$variable] = $value;
            }
        }
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
    public function flatten($environment, $array)
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
    public static function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new Config();
        }

        return self::$_instance;
    }
}

