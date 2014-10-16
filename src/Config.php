<?php

/**
 * Configuration
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
 * Config Class
 *
 * Handles loading the site's configuration file (if available). At the moment
 * this class is a very skewed Singleton. The plan is to eventually extend this
 * out to support multiple configuration files, and the ability to load in
 * custom config files on the fly as well. The core of Pickles uses the class
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
    public function __construct($config_filename = false)
    {
        try
        {
            ini_set('display_errors', true);
            error_reporting(-1);

            $filename     = getcwd() . '/../../pickles.php';
            $environments = false;
            $environment  = false;
            // Why not PHP_SAPI? because I wanted it to be convenient to unit test
            $cli          = !isset($_SERVER['REQUEST_METHOD']);

            if ($config_filename)
            {
                $filename = $config_filename;
            }

            // Only require in case you want to reload the config
            require $filename;

            // Checks that we have the config array
            if (!isset($config))
            {
                throw new \Exception('Missing $config array.');
            }

            // Determines the environment
            if (!isset($config['environments']) || !is_array($config['environments']))
            {
                throw new \Exception('Environments are misconfigured.');
            }

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
                        elseif (substr($host, 0, 1) == '/'
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

            if (!$environment)
            {
                throw new \Exception('Unable to determine the environment.');
            }

            // Flattens the array based on the environment
            $config = $this->flatten($environment, $config);

            // Disables display errors in production
            if ($environment == 'production')
            {
                ini_set('display_errors', false);
            }

            // Assigns the environment
            $config['environment'] = $environment;

            // Defaults expected Pickles variables to false
            foreach (['auth', 'cache', 'profiler'] as $variable)
            {
                if (!isset($config['pickles'][$variable]))
                {
                    $config['pickles'][$variable] = false;
                }
            }

            // Assigns the config variables to the object
            foreach ($config as $variable => $value)
            {
                $this[$variable] = $value;
            }
        }
        catch (\Exception $e)
        {
            throw $e;
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
     * @param  string $file name of config to load
     * @return object self::$_instance instance of the Config class
     */
    public static function getInstance($file = false)
    {
        if (!self::$_instance || $file)
        {
            self::$_instance = new Config($file);
        }

        return self::$_instance;
    }
}

