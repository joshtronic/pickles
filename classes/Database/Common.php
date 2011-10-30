<?php

/**
 * Common Database Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2011, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Common Database Abstraction Layer
 *
 * Parent class that our database driver classes should be extending. Contains
 * basic functionality for instantiation and interfacing.
 */
abstract class Database_Common extends Object
{
	/**
	 * Driver
	 *
	 * @access protected
	 * @var    string
	 */
	protected $driver = null;

	/**
	 * Hostname for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $hostname = 'localhost';

	/**
	 * Port number for the server
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $port = null;

	/**
	 * UNIX socket for the server
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $socket = null;

	/**
	 * Username for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $username = null;

	/**
	 * Password for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $password = null;

	/**
	 * Database name for the server
	 *
	 * @access protected
	 * @var    string
	 */
	protected $database = null;

	/**
	 * Whether or not to use caching
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $caching = false;

	/**
	 * Connection resource
	 *
	 * @access protected
	 * @var    object
	 */
	protected $connection = null;

	/**
	 * Results object for the executed statement
	 *
	 * @access protected
	 * @var    object
	 */
	protected $results = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Checks the driver is set and available
		if ($this->driver == null)
		{
			throw new Exception('Driver name is not set');
		}
		else
		{
			if (extension_loaded($this->driver) == false)
			{
				throw new Exception('Driver "' . $this->driver . '" is not loaded');
			}
		}
	}

	/**
	 * Set Hostname
	 *
	 * @param string $hostname hostname for the database
	 */
	public function setHostname($hostname)
	{
		return $this->hostname = $hostname;
	}

	/**
	 * Set Port
	 *
	 * @param integer $port port for the database
	 */
	public function setPort($port)
	{
		return $this->port = $port;
	}

	/**
	 * Set Socket
	 *
	 * @param string $socket name of the UNIX socket
	 */
	public function setSocket($socket)
	{
		return $this->socket = $socket;
	}

	/**
	 * Set Username
	 *
	 * @param string $username username for the database
	 */
	public function setUsername($username)
	{
		return $this->username = $username;
	}

	/**
	 * Set Password
	 *
	 * @param string $password password for the database
	 */
	public function setPassword($password)
	{
		return $this->password = $password;
	}

	/**
	 * Set Database
	 *
	 * @param string $database database for the database
	 */
	public function setDatabase($database)
	{
		return $this->database = $database;
	}

	/**
	 * Set Caching
	 *
	 * @param boolean whether or not to use cache
	 */
	public function setCaching($caching)
	{
		return $this->caching = $caching;
	}

	/**
	 * Get Driver
	 *
	 * Returns the name of the driver in use. Used by the Model class to
	 * determine which path to take when interfacing with the Database object.
	 *
	 * @return string name of the driver in use
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Opens database connection
	 *
	 * Establishes a connection to the MySQL database based on the
	 * configuration options that are available in the Config object.
	 *
	 * @abstract
	 * @return   boolean true on success, throws an exception overwise
	 */
	abstract public function open();

	/**
	 * Closes database connection
	 *
	 * Sets the connection to null regardless of state.
	 *
	 * @return boolean always true
	 */
	abstract public function close();
}

?>
