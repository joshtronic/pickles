<?php

/**
 * PDO Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
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
		PDO::NULL_EMPTY_STRING => true,
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
			$files = array();

			// Ubuntu 10.04 is a bit behind on PHP 5.3.x and the IGNORE_ARGS
			// constant doesn't exist. To conserve memory, the backtrace will
			// Only be used on servers running PHP 5.3.6 or above.
			if (version_compare(PHP_VERSION, '5.3.6', '>='))
			{
				$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				krsort($backtrace);

				foreach ($backtrace as $file)
				{
					if (isset($file['class'], $file['line']))
					{
						$files[] = $file['class'] . ':' . $file['line'];
					}
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
