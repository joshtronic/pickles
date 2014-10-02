<?php

/**
 * Database Class File for PICKLES
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
 * @todo      Drop driver, hardcode drivers based on the type
 * @todo      More assumptions for the datasource variables
 */

namespace Pickles;

/**
 * Database Class
 *
 * Database interaction all in one place. Allows for object reuse and contains
 * functions to ease interacting with databases. Common assumptions about PDO
 * attributes are baked in. Only support PDO.
 */
class Database extends Object
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
    protected $attributes = [
        \PDO::ATTR_PERSISTENT   => true,
        \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
        \PDO::NULL_EMPTY_STRING => true,
    ];

    /**
     * Driver
     *
     * @var string
     */
    public $driver = null;

    /**
     * Hostname for the server
     *
     * @var string
     */
    public $hostname = 'localhost';

    /**
     * Port number for the server
     *
     * @var integer
     */
    public $port = null;

    /**
     * UNIX socket for the server
     *
     * @var integer
     */
    public $socket = null;

    /**
     * Username for the server
     *
     * @var string
     */
    public $username = null;

    /**
     * Password for the server
     *
     * @var string
     */
    public $password = null;

    /**
     * Database name for the server
     *
     * @var string
     */
    public $database = null;

    /**
     * Whether or not to use caching
     *
     * @var boolean
     */
    public $cache = false;

    /**
     * Connection resource
     *
     * @var object
     */
    public $connection = null;

    /**
     * Results object for the executed statement
     *
     * @var object
     */
    public $results = null;

    /**
     * Get Instance
     *
     * Instantiates a new instance of the Database class or returns the
     * previously instantiated copy.
     *
     * @static
     * @param  string $datasource_name name of the datasource
     * @return object instance of the class
     */
    public static function getInstance($datasource_name = false)
    {
        $config = Config::getInstance();

        // Tries to load a datasource if one wasn't specified
        if (!$datasource_name)
        {
            if (isset($config['pickles']['datasource']))
            {
                $datasource_name = $config['pickles']['datasource'];
            }
            elseif (is_array($config['datasources']))
            {
                $datasources = $config['datasources'];

                foreach ($datasources as $name => $datasource)
                {
                    if (isset($datasource['driver']))
                    {
                        $datasource_name = $name;
                    }
                }
            }
        }

        // Attempts to validate the datasource
        if ($datasource_name)
        {
            if (!isset(self::$instances['Database'][$datasource_name]))
            {
                if (!isset($config['datasources'][$datasource_name]))
                {
                    throw new \Exception('The specified datasource is not defined in the config.');
                }

                $datasource = $config['datasources'][$datasource_name];

                if (!isset($datasource['driver']))
                {
                    throw new \Exception('The specified datasource lacks a driver.');
                }

                $datasource['driver'] = strtolower($datasource['driver']);

                // Checks the driver is legit and scrubs the name
                switch ($datasource['driver'])
                {
                    case 'pdo_mysql':
                        $attributes = [
                            'dsn'  => 'mysql:host=[[hostname]];port=[[port]];unix_socket=[[socket]];dbname=[[database]]',
                            'port' =>  3306,
                        ];
                        break;

                    case 'pdo_pgsql':
                        $attributes = [
                            'dsn'  => 'pgsql:host=[[hostname]];port=[[port]];dbname=[[database]];user=[[username]];password=[[password]]',
                            'port' =>  5432,
                        ];
                        break;

                    case 'pdo_sqlite':
                        $attributes = ['dsn' => 'sqlite:[[hostname]]'];
                        break;

                    default:
                        throw new \Exception('Datasource driver "' . $datasource['driver'] . '" is invalid');
                        break;
                }

                // Instantiates our database class
                $instance = new Database();

                // Sets our database parameters
                if (is_array($datasource))
                {
                    $datasource = array_merge($attributes, $datasource);

                    foreach ($datasource as $variable => $value)
                    {
                        $instance->$variable = $value;
                    }
                }

                // Caches the instance for possible reuse later
                self::$instances['Database'][$datasource_name] = $instance;
            }

            // Returns the instance
            return self::$instances['Database'][$datasource_name];
        }

        return false;
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
            switch ($this->driver)
            {
                case 'pdo_mysql':
                    // Resolves "Invalid UTF-8 sequence" issues when encoding as JSON
                    // @todo Didn't resolve that issue, borked some other characters though
                    //$this->attributes[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
                    break;

                case 'pdo_pgsql':
                    // This combats a bug: https://bugs.php.net/bug.php?id=62571&edit=1
                    $this->attributes[\PDO::ATTR_PERSISTENT] = false;

                    // This allows for multiple prepared queries
                    $this->attributes[\PDO::ATTR_EMULATE_PREPARES] = true;
                    break;
            }

            if (isset($this->username, $this->password, $this->database))
            {
                // Swaps out any variables with values in the DSN
                $this->dsn = str_replace(
                    ['[[hostname]]', '[[port]]', '[[socket]]', '[[username]]', '[[password]]', '[[database]]'],
                    [$this->hostname, $this->port, $this->socket, $this->username, $this->password, $this->database],
                    $this->dsn
                );

                // Strips any empty parameters in the DSN
                $this->dsn = str_replace(['host=;', 'port=;', 'unix_socket=;'], '', $this->dsn);

                // Attempts to establish a connection
                $this->connection = new \PDO(
                    $this->dsn, $this->username, $this->password, $this->attributes
                );
            }
            else
            {
                throw new \Exception('There was an error loading the database configuration.');
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
    public function execute($sql, $input_parameters = null, $explain = false)
    {
        $this->open();

        $sql = trim($sql);

        // Checks if the query is blank
        if ($sql != '')
        {
            // Establishes if we're working on an EXPLAIN
            if ($this->config['pickles']['profiler'])
            {
                $explain_results = preg_match('/^SELECT /i',  $sql);
            }
            else
            {
                $explain_results = false;
            }

            // Executes a standard query
            if ($input_parameters === null)
            {
                // Explains the query
                if ($explain_results)
                {
                    $explain_results = $this->fetch('EXPLAIN ' . $sql, null, true);
                }

                $start_time    = microtime(true);
                $this->results = $this->connection->query($sql);
            }
            // Executes a prepared statement
            else
            {
                // Explains the query
                if ($explain_results)
                {
                    $explain_results = $this->fetch('EXPLAIN ' . $sql, $input_parameters, true);
                }

                $start_time    = microtime(true);
                $this->results = $this->connection->prepare($sql);
                $this->results->execute($input_parameters);
            }

            $end_time = microtime(true);
            $duration = $end_time - $start_time;

            // Logs the information to the profiler
            if ($this->config['pickles']['profiler'] && !$explain)
            {
                Profiler::query(
                    $sql,
                    $input_parameters,
                    $this->results->fetchAll(\PDO::FETCH_ASSOC),
                    $duration,
                    $explain_results
                );
            }
        }
        else
        {
            throw new \Exception('No query to execute.');
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
    public function fetch($sql = null, $input_parameters = null, $explain = false)
    {
        $this->open();

        if ($sql !== null)
        {
            $this->execute($sql, $input_parameters, $explain);
        }

        // Pulls the results based on the type
        $results = $this->results->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }
}
