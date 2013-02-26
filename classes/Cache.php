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
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Cache Class
 *
 * Wrapper class for interfacing with Redis and Memcached as key/value
 * stores for caching data. Wrapper provides graceful failover when a
 * caching server is not available. The syntax is designed around the API
 * for Memcached() to provide a consistent experience across datastores.
 * Support for Memcached is currently using the Memcache() library due to
 * some bugginess with Memcached(). The plan is to migrate to Memcached()
 * in the future. Redis interaction is done via phpredis. Keys are
 * optionally namespaced and keys are forced to lowercase for consistency.

 * @link http://www.memcached.org/
 * @link http://us.php.net/manual/en/book.memcache.php
 * @link http://packages.ubuntu.com/lucid/php5-memcache
 * @link http://redis.io
 * @link https://github.com/nicolasff/phpredis
 */
class Cache extends Object
{
	/**
	 * Hostname for the Memcached Server
	 *
	 * @access private
	 * @var    string
	 */
	private $hostname = 'localhost';

	/**
	 * Port to use to connect
	 *
	 * @access private
	 * @var    integer
	 */
	private $port = null;

	/**
	 * Database to use (Redis-only)
	 *
	 * @access private
	 * @var    integer
	 */
	private $database = 0;

	/**
	 * Namespace (prefix)
	 *
	 * @access private
	 * @var    string
	 */
	private $namespace = '';

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

				var_dump($datasource);
				exit;

				foreach (array('hostname', 'port', 'database', 'namespace') as $variable)
				{
					if (isset($datasource[$variable]))
					{
						$this->$variable = $datasource[$variable];
					}
				}
			}
		}

		if ($this->namespace != '')
		{
			$this->namespace .= ':';
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
	 * Gets the value of the key(s) and returns it.
	 *
	 * @param  mixed $keys key(s) to retrieve
	 * @return mixed value(s) of the requested key(s), false if not set
	 */
	public function get($keys)
	{
		if ($this->open())
		{
			if (is_array($keys))
			{
				foreach ($keys as $index => $key)
				{
					$keys[$index] = strtoupper($this->namespace . $key);
				}
			}
			else
			{
				$keys = strtoupper($this->namespace . $keys);
			}

			return $this->connection->get($keys);
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
		$key = strtoupper($key);

		if ($this->open())
		{
			return $this->connection->set(strtoupper($this->namespace . $key), $value, 0, $expire);
		}

		return false;
	}

	/**
	 * Delete Key
	 *
	 * Deletes the specified key(s).
	 *
	 * @param  mixed $keys key(s) to delete
	 * @return boolean status of deleting the key
	 */
	public function delete($keys)
	{
		if ($this->open())
		{
			if (!is_array($keys))
			{
				$keys = array($keys);
			}

			// Memcache() doesn't let you pass an array to delete all records the same way you can with get()
			foreach ($keys as $key)
			{
				$this->connection->delete(strtoupper($this->namespace . $key));
			}

			return true;
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
			return $this->connection->increment(strtoupper($this->namespace . $key));
		}

		return false;
	}
}

?>
