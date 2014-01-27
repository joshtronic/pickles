<?php

/**
 * Common Database Class File for PICKLES
 *
 * PHP version 5.3+
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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
	 * Open Database Connection
	 *
	 * Establishes a connection to the MySQL database based on the configuration
	 * options that are available in the Config object.
	 *
	 * @abstract
	 * @return   boolean true on success, throws an exception overwise
	 */
	abstract public function open();

	/**
	 * Close Database Connection
	 *
	 * Sets the connection to null regardless of state.
	 *
	 * @abstract
	 * @return   boolean always true
	 */
	abstract public function close();
}

?>
