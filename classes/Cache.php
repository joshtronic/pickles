<?php

/**
 * Caching System for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2012, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Cache Class
 *
 * Wrapper class for Memcache() to allow for better error handling when the
 * Memcached server is unavailable. Designed around the syntax for Memcached()
 * to allow for an easier transistion to the aforementioned in the future. I
 * don't entirely remember specifics, but the reason for not using Memcached()
 * was due to an unexplainable bug in the version in the repository for Ubuntu
 * 10.04 LTS. Memcached() does support more of the memcached protocol and will
 * eventually be what PICKLES uses.
 *
 * Requires php5-memcache
 *
 * @link http://us.php.net/manual/en/book.memcache.php
 * @link http://packages.ubuntu.com/lucid/php5-memcache
 * @link http://www.memcached.org/
 */
class Cache extends Object
{
	/**
	 * Hostname for the Memcached Server
	 *
	 * @access private
	 * @var    string
	 */
	private $hostname = null;

	/**
	 * Port to use to connect
	 *
	 * @access private
	 * @var    integer
	 */
	private $port = null;

	/**
	 * Connection resource to Memcached
	 *
	 * @access private
	 * @var    object
	 */
	private $connection = null;

	/**
	 * Constructor
	 *
	 * Sets up our connection variables.
	 *
	 * @param string $hostname optional hostname to connect to
	 * @param string $database optional port to use
	 */
	public function __construct($hostname = null, $port = null)
	{
		parent::__construct();

		if ($this->config->pickles['cache'])
		{
			if (isset($this->config->datasources[$this->config->pickles['cache']]))
			{
				$datasource = $this->config->datasources[$this->config->pickles['cache']];

				if (isset($datasource['hostname'], $datasource['port']))
				{
					$this->hostname = $datasource['hostname'];
					$this->port     = $datasource['port'];
				}
			}
		}
	}

	/**
	 * Destructor
	 *
	 * Closes the connection when the object dies.
	 */
	public function __destruct()
	{
		if ($this->connection)
		{
			$this->connection->close();
		}
	}

	/**
	 * Get Instance
	 *
	 * Let's the parent class do all the work.
	 *
	 * @static
	 * @param  string $class name of the class to instantiate
	 * @return object self::$instance instance of the Cache class
	 */
	public static function getInstance($class = 'Cache')
	{
		return parent::getInstance($class);
	}

	/**
	 * Opens Connection
	 *
	 * Establishes a connection to the memcached server.
	 */
	public function open()
	{
		if ($this->connection === null)
		{
			$this->connection = new Memcache();
			$this->connection->connect($this->hostname, $this->port);
		}

		return true;
	}

	/**
	 * Get Key
	 *
	 * Gets the value of the key and returns it.
	 *
	 * @param  string $key key to retrieve
	 * @return mixed  value of the requested key, false if not set
	 */
	public function get($key)
	{
		if ($this->open())
		{
			return $this->connection->get($key);
		}

		return false;
	}

	/**
	 * Set Key
	 *
	 * Sets key to the specified value. I've found that compression can lead to
	 * issues with integers and can slow down the storage and retrieval of data
	 * (defeats the purpose of caching if you ask me) and isn't supported. I've
	 * also been burned by data inadvertantly being cached for infinity, hence
	 * the 5 minute default.
	 *
	 * @param  string  $key key to set
	 * @param  mixed   $value value to set
	 * @param  integer $expiration optional expiration, defaults to 5 minutes
	 * @return boolean status of writing the data to the key
	 */
	public function set($key, $value, $expire = 300)
	{
		if ($this->open())
		{
			return $this->connection->set($key, $value, 0, $expire);
		}

		return false;
	}

	/**
	 * Delete Key
	 *
	 * Deletes the specified key.
	 *
	 * @param  string $key key to delete
	 * @return boolean status of deleting the key
	 */
	public function delete($key)
	{
		if ($this->open())
		{
			return $this->connection->delete($key);
		}

		return false;
	}

	/**
	 * Increment Key
	 *
	 * Increments the value of an existing key.
	 *
	 * @param  string $key key to increment
	 * @return boolean status of incrementing the key
	 * @todo   Wondering if I should check the key and set to 1 if it's new
	 */
	public function increment($key)
	{
		if ($this->open())
		{
			return $this->connection->increment($key);
		}

		return false;
	}
}

?>
