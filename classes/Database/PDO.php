<?php

/**
 * PDO Class File for PICKLES
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
 * PDO Abstraction Layer
 *
 * Parent class for any of our database classes that use PDO.
 */
class Database_PDO extends Database_Common 
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
		PDO::NULL_EMPTY_STRING => true
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

		$loggable_query = $sql;

		if ($input_parameters != null)
		{
			$loggable_query .= ' -- ' . (JSON_AVAILABLE ? json_encode($input_parameters) : serialize($input_parameters));
		}

		Log::query($loggable_query);

		// Checks if the query is blank
		if (trim($sql) != '')
		{
			try
			{
				// Establishes if the profiler is enabled
				$profiler = (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true && preg_match('/^EXPLAIN /i', $sql) == false);

				// Executes a standard query
				if ($input_parameters === null)
				{
					// Explains the query
					if ($profiler == true)
					{
						$explain = $this->fetchAll('EXPLAIN ' . $sql);
					}

					$start_time = microtime(true);
					$this->results = $this->connection->query($sql);
				}
				// Executes a prepared statement
				else
				{
					// Explains the query
					if ($profiler == true)
					{
						$explain = $this->fetchAll('EXPLAIN ' . $sql, $input_parameters);
					}

					$start_time = microtime(true);
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
				if ($profiler == true)
				{
					Profiler::logQuery($sql, $input_parameters, isset($explain) ? $explain : false, $duration);
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
	 * Fetch a single row from the database
	 *
	 * @param  string $sql statement to be executed
	 * @param  array $input_parameters optional key/values to be bound
	 * @param  string $return_type optional type of return set
	 * @return mixed based on return type
	 */
	public function fetch($sql = null, $input_parameters = null, $return_type = null)
	{
		$this->open();

		if ($sql !== null)
		{
			$this->execute($sql, $input_parameters);
		}

		// Pulls the results based on the type
		$results = false;
		switch ($return_type)
		{
			case 'column':
				$results = $this->results->fetchColumn(0);
				break;
			case 'all':
				$results = $this->results->fetchAll(PDO::FETCH_ASSOC);
				break;
			default:
				$results = $this->results->fetch(PDO::FETCH_ASSOC);
				break;
		}

		return $results;
	}

	/**
	 * Fetch a single column from the database
	 *
	 * This method assumes you want the first column in your select. If you
	 * need 2 or more columns you should simply use fetch().
	 *
	 * @param  string $sql statement to be executed
	 * @param  array $input_parameters optional key/values to be bound
	 * @return string
	 */
	public function fetchColumn($sql = null, $input_parameters = null)
	{
		return $this->fetch($sql, $input_parameters, 'column');
	}

	/**
	 * Fetches all rows as an array
	 *
	 * @param  string $sql statement to be executed
	 * @param  array $input_parameters optional key/values to be bound
	 * @return array
	 */
	public function fetchAll($sql = null, $input_parameters = null)
	{
		return $this->fetch($sql, $input_parameters, 'all');
	}
}

?>
