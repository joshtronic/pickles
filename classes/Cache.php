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
 * eventually be what PICKLES uses. Keys are forced to be uppercase for
 * consistencies sake as I've been burned by the case sensitivity due to typos
 * in my code.
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
	 * Namespace (prefix)
	 *
	 * @access private
	 * @var    string
	 */
	private $namespace = '';

	/**
	 * Servers
	 *
	 * @access private
	 * @var    integer
	 */
	private $servers = 0;

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
	public function __construct()
	{
		parent::__construct();

		if ($this->config->pickles['cache'])
		{
			if (!is_array($this->config->pickles['cache']))
			{
				$datasources = array($this->config->pickles['cache']);
			}
			else
			{
				$datasources = $this->config->pickles['cache'];
			}

			$this->connection = new Memcache();

			foreach ($datasources as $name)
			{
				if (isset($this->config->datasources[$name]))
				{
					$datasource = $this->config->datasources[$name];

					$this->connection->addServer($datasource['hostname'], $datasource['port']);
					$this->servers++;

					if (isset($datasource['namespace']))
					{
						$this->namespace = $datasource['namespace'];
					}
				}
			}
		}

		if ($this->namespace != '')
		{
			$this->namespace .= '-';
		}
	}

	/**
	 * Destructor
	 *
	 * Closes the connection when the object dies.
	 */
	public function __destruct()
	{
		if ($this->servers)
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
	 * Get Key
	 *
	 * Gets the value of the key(s) and returns it.
	 *
	 * @param  mixed $keys key(s) to retrieve
	 * @return mixed value(s) of the requested key(s), false if not set
	 */
	public function get($keys)
	{
		set_error_handler('cacheErrorHandler');

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

		try
		{
			$return = $this->connection->get($keys);
		}
		catch (Exception $exception)
		{
			$return = false;
		}

		restore_error_handler();

		return $return;
	}

	/**
	 * Set Key
	 *
	 * Sets key to the specified value. I've found that compression can lead to
	 * issues with integers and can slow down the storage and retrieval of data
	 * (defeats the purpose of caching if you ask me) and isn't supported. I've
	 * also been burned by data inadvertantly being cached for infinity, but
	 * have had great success caching data for a full day, hence defaulting the
	 * expiration to a full day.
	 *
	 * @param  string  $key key to set
	 * @param  mixed   $value value to set
	 * @param  integer $expiration optional expiration, defaults to 1 day
	 * @return boolean status of writing the data to the key
	 */
	public function set($key, $value, $expire = Time::DAY)
	{
		set_error_handler('cacheErrorHandler');

		$key = strtoupper($key);

		try
		{
			$return = $this->connection->set(strtoupper($this->namespace . $key), $value, 0, $expire);
		}
		catch (Exception $exception)
		{
			$return = false;
		}

		restore_error_handler();

		return $return;
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
		set_error_handler('cacheErrorHandler');

		try
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

			$return = true;
		}
		catch (Exception $exception)
		{
			$return = false;
		}

		restore_error_handler();

		return $return;
	}

	/**
	 * Increment Key
	 *
	 * Increments the value of an existing key.
	 *
	 * @param  string $key key to increment
	 * @return boolean status of incrementing the key
	 * @todo   Check if it's set as Memcache() doesn't and won't inc if it doesn't exist
	 */
	public function increment($key)
	{
		set_error_handler('cacheErrorHandler');

		try
		{
			$return = $this->connection->increment(strtoupper($this->namespace . $key));
		}
		catch (Exception $exception)
		{
			$return = false;
		}

		restore_error_handler();

		return $return;
	}
}

function cacheErrorHandler($errno, $errstr, $errfile, $errline)
{
	throw new Exception($errstr);
}

?>
