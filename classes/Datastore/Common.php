<?php

/**
 * Common Datastore Class File for PICKLES
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
 * Common Datastore Abstraction Layer
 *
 * Parent class that our datastore classes should be extending.
 * Contains basic functionality for instantiation and interfacing.
 */
abstract class Datastore_Common extends Object
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
	 * Database name (or number) for the server
	 *
	 * @var string or integer
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
	 * Open Connection
	 *
	 * Establishes a connection to the datastore based on the configuration
	 * options that are available in the Config object.
	 *
	 * @abstract
	 * @return   boolean true on success, throws an exception overwise
	 */
	abstract public function open();

	/**
	 * Close Connection
	 *
	 * Sets the connection to null regardless of state.
	 *
	 * @abstract
	 * @return   boolean always true
	 */
	abstract public function close();
}

?>
