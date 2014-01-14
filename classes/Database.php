<?php

/**
 * Database Class File for PICKLES
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
		PDO::ATTR_PERSISTENT   => true,
		PDO::ATTR_ERRMODE      => PDO::ERRMODE_EXCEPTION,
		PDO::NULL_EMPTY_STRING => true,
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
			if (isset($config->pickles['datasource']))
			{
				$datasource_name = $config->pickles['datasource'];
			}
			elseif (is_array($config->datasources))
			{
				$datasources = $config->datasources;

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
				if (!isset($config->datasources[$datasource_name]))
				{
					throw new Exception('The specified datasource is not defined in the config.');
				}

				$datasource = $config->datasources[$datasource_name];

				if (!isset($datasource['driver']))
				{
					var_Dump($datasource);
					throw new Exception('The specified datasource lacks a driver.');
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
						throw new Exception('Datasource driver "' . $datasource['driver'] . '" is invalid');
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
			// Checks that the prefix is set
			if ($this->dsn == null)
			{
				throw new Exception('Data source name is not defined');
			}

			switch ($this->driver)
			{
				case 'pdo_mysql':
					// Resolves "Invalid UTF-8 sequence" issues when encoding as JSON
					// @todo Didn't resolve that issue, borked some other characters though
					//$this->attributes[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
					break;

				case 'pdo_pgsql':
					// This combats a bug: https://bugs.php.net/bug.php?id=62571&edit=1
					$this->attributes[PDO::ATTR_PERSISTENT] = false;

					// This allows for multiple prepared queries
					$this->attributes[PDO::ATTR_EMULATE_PREPARES] = true;
					break;
			}

			if (isset($this->username, $this->password, $this->database))
			{
				// Creates a new PDO database object (persistent)
				try
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
				$loggable_query .= ' -- ' . json_encode($input_parameters);
			}

			Log::query($loggable_query);
		}

		$sql = trim($sql);

		// Checks if the query is blank
		if ($sql != '')
		{
			// Builds out stack trace for queries
			$files = [];

			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			krsort($backtrace);

			foreach ($backtrace as $file)
			{
				if (isset($file['class'], $file['line']))
				{
					$files[] = $file['class'] . ':' . $file['line'];
				}
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

				if ($duration >= 1)
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

?>
