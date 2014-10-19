<?php

/**
 * Mongo Abstraction Layer
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

class Mongo extends Object
{
    public static function getInstance($class = 'Mongo')
    {
        $config = Config::getInstance();

        if (!isset(self::$instances['Mongo']))
        {
            if (!isset($config['mongo'], $config['mongo']['database']))
            {
                throw new \Exception('The “mongo” datasource is not defined in the configuration.', 500);
            }

            $mongo = $config['mongo'];

            // Defaults to the local server on the default port
            if (!isset($mongo['server']))
            {
                $mongo['server'] = 'mongodb://localhost:27017';
            }

            // Instantiates our Mongo client
            $instance = new \MongoClient($mongo['server']);
            $instance = $instance->$mongo['database'];

            // Caches the instance for possible reuse later
            self::$instances['Mongo'] = $instance;
        }

        // Returns the instance
        return self::$instances['Mongo'];
    }
}

