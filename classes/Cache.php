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
	 * Cache handler
	 *
	 * @access private
	 * @var    string memcached or redis
	 */
	private $handler = null;

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
	 * Sets up our variables
	 */
	public function __construct()
	{
		parent::__construct();

		if ($this->config->pickles['cache'])
		{
			if (isset($this->config->datasources[$this->config->pickles['cache']]))
			{
				$datasource = $this->config->datasources[$this->config->pickles['cache']];

				if (!isset($datasource['type']))
				{
					throw new Exception('You must specify the datasource\'s type');
				}

				$this->hostname = isset($datasource['hostname']) ? $datasource['hostname'] : 'localhost';

				switch ($datasource['type'])
				{
					case 'memcache':
					case 'memcached':
						$this->handler = 'memcached';
						$this->port    = isset($datasource['port']) ? $datasource['port'] : 11211;
						break;

					case 'redis':
						$this->handler  = 'redis';
						$this->port     = isset($datasource['port'])     ? $datasource['port']     : 6379;
						$this->database = isset($datasource['database']) ? $datasource['database'] : 0;
						break;

					default:
						throw new Exception('The specified datasource type "' . $datasource['type'] . '" is unsupported.');
				}

				if (isset($datasource['namespace']) && $datasource['namespace'] != '')
				{
					$this->namespace = $datasource['namespace'] . ':';
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
			switch ($this->handler)
			{
				case 'memcached':
					$this->connection = new Memcache();
					break;

				case 'redis':
					$this->connection = new Redis();
					break;
			}
		}

		$connected = $this->connection->connect($this->hostname, $this->port);

		if ($connected && $this->database != 0)
		{
			$this->connection->select($this->database);
		}

		return $connected;
	}

	/**
	 * Set Key
	 *
	 * Sets key to the specified value.
	 *
	 * @param  string  $key key to set
	 * @param  mixed   $value value to set
	 * @param  integer $expiration optional expiration, defaults to 5 minutes
	 * @return boolean status of writing the data to the key
	 */
	public function set($key, $value, $expire = 300)
	{
		$key = strtolower($key);

		if ($this->open())
		{
			switch ($this->handler)
			{
				case 'memcached':
					return $this->connection->set(strtolower($this->namespace . $key), $value, 0, $expire);
					break;

				case 'redis':
					if (is_array($value))
					{
						$value = 'JSON:' . json_encode($value);
					}

					return $this->connection->set(strtolower($this->namespace . $key), $value, $expire);
					break;
			}
		}

		return false;
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
			// Namespaces keys
			if (is_array($keys))
			{
				foreach ($keys as $index => $key)
				{
					$keys[$index] = strtolower($this->namespace . $key);
				}
			}
			else
			{
				$keys = strtolower($this->namespace . $keys);
			}

			switch ($this->handler)
			{
				case 'memcached':
					return $this->connection->get($keys);
					break;

				case 'redis':
					if (is_array($keys))
					{
						$values = $this->connection->mGet($keys);

						foreach ($values as $index => $value)
						{
							if (substr($value, 0, 5) == 'JSON:')
							{
								$values[$index] = json_decode(substr($value, 5), true);
							}
						}

						return $values;
					}
					else
					{
						$value = $this->connection->get($keys);

						if (substr($value, 0, 5) == 'JSON:')
						{
							$value = json_decode(substr($value, 5), true);
						}

						return $value;
					}
					break;
			}
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
				$this->connection->delete(strtolower($this->namespace . $key));
			}

			return true;
		}

		return false;
	}

	/**
	 * Change
	 *
	 * Increment / decrement a variable by a value
	 *
	 * @param string $direction increment or decrement
	 * @param string $key key to increment
	 * @param integer $value increment by value
	 * @return mixed new value or false if unable to connect
	 */
	public function change($direction, $key, $value = 1)
	{
		if ($this->handler == 'redis')
		{
			switch ($direction)
			{
				case 'increment': $direction = 'incr'; break;
				case 'decrement': $direction = 'decr'; break;
			}

			if ($value > 1)
			{
				$direction .= 'By';
			}
		}

		$key = strtolower($this->namespace . $key);

		if ($this->open())
		{
			// Memcache::*crement() doesn't create the key
			if ($this->handler == 'memcached')
			{
				if ($this->connection->add($key, $value) === false)
				{
					return $this->connection->$direction($key, $value);
				}
				else
				{
					return $value;
				}
			}
			else
			{
				return $this->connection->$direction($key, $value);
			}
		}

		return false;
	}

	/**
	 * Increment Key
	 *
	 * Increments the value of an existing key.
	 *
	 * @param  string $key key to increment
	 * @param  integer $value increment by value
	 * @return mixed new value or false if unable to connect
	 */
	public function increment($key, $value = 1)
	{
		return $this->change('increment', $key, $value);
	}

	/**
	 * Decrement Key
	 *
	 * Decrements the value of an existing key.
	 *
	 * @param  string $key key to decrement
	 * @param  integer $value decrement by value
	 * @return mixed new value or false if unable to connect
	 */
	public function decrement($key, $value = 1)
	{
		return $this->change('decrement', $key, $value);
	}
}

?>
