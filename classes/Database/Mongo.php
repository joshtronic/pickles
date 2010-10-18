<?php

/**
 * Mongo Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License 
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Mongo Database Abstraction Layer
 */
class Database_Mongo extends Database_Common
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = 'mongo';

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
			// Assembles the server string
			$server = 'mongodb://';

			if (isset($this->username))
			{
				$server .= $this->username;

				if (isset($this->password))
				{
					$server .= ':' . $this->password;
				}

				$server .= '@';
			}

			$server .= $this->hostname . ':' . $this->port . '/' . $this->database;

			// Attempts to connect
			try
			{
				$this->connection = new Mongo($server, array('persist' => 'pickles'));

				// If we have database and collection, attempt to assign them
				if (isset($this->database))
				{
					$this->connection = $this->connection->selectDB($this->database);
				}
			}
			catch (Exception $exception)
			{
				throw new Exception('Unable to connect to Mongo database');
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
		try
		{
			$this->connection->close();
		}
		catch (Exception $exception)
		{
			// Trapping error
		}

		$this->connection = null;

		return true;
	}
	
	/**
	 * Fetch a single row from the database
	 */
	public function fetch($collection, $query, $fields = null, $return_type = null)
	{
		$this->open();

		// Pulls the results based on the type
		$results = false;
		if ($return_type == 'all')
		{
			$results = $this->connection->$collection->find($query, $fields);
		}
		else
		{
			$results = $this->connection->$collection->findOne($query, $fields);
		}

		return $results;
	}

	/**
	 * Fetches all rows as an array
	 */
	public function fetchAll($collection, $query, $fields = null)
	{
		return $this->fetch($collection, $query, $fields, 'all');
	}
}

?>
