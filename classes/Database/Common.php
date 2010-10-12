<?php

/**
 * Common Database Class File for PICKLES
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
 * Common Database Abstraction Layer
 *
 * Parent class that our database driver classes should be extending. Contains
 * basic functionality for instantiation and interfacing.
 */
abstract class Database_Common extends Object
{
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
