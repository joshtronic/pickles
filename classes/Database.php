<?php

/**
 * Database Class File for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Database Factory
 *
 * Generic class to simplify connecting to a database. All database objects
 * should be created by this class to future proof against any internal changes
 * to PICKLES.
 */
class Database extends Object
{
	/**
	 * Constructor
	 *
	 * Attempts to get an instance of the passed database type or attempts to
	 * use a default specified in the config.
	 *
	 * @param string $name optional name of the connection to use
	 */
	public function __construct(String $name = null)
	{
		parent::__construct();

		return Database::getInstance($name);
	}

	/**
	 * Get Instance
	 *
	 * Looks up the datasource using the passed name and gets an instance of
	 * it. Allows for easy sharing of certain classes within the system to
	 * avoid the extra overhead of creating new objects each time. Also avoids
	 * the hassle of passing around variables (yeah I know, very global-ish)
	 *
	 * @static
	 * @param  string $name name of the datasource
	 * @return object instance of the class
	 */
	public static function getInstance($name = null)
	{
		$config = Config::getInstance();

		// Checks if we have a default
		if ($name == null)
		{
			// Checks the config for a default
			if (isset($config->pickles['datasource']))
			{
				$name = $config->pickles['datasource'];
			}
			// Tries to use the first defined datasource
			elseif (is_array($config->datasources))
			{
				$datasources = $config->datasources;
				$name        = key($datasources);
			}
		}

		// If we have a name try to set up a connection
		if ($name != null)
		{
			if (isset($config->datasources[$name]))
			{
				$datasource = $config->datasources[$name];

				if (!isset($datasource['driver']))
				{
					return false;
				}

				$datasource['driver'] = strtolower($datasource['driver']);

				if (!isset(self::$instances['Database'][$name]))
				{
					// Checks the driver is legit and scrubs the name
					switch ($datasource['driver'])
					{
						case 'pdo_mysql':  $class = 'PDO_MySQL';      break;
						case 'pdo_pgsql':  $class = 'PDO_PostgreSQL'; break;
						case 'pdo_sqlite': $class = 'PDO_SQLite';     break;

						default:
							throw new Exception('Datasource driver "' . $datasource['driver'] . '" is invalid');
							break;
					}

					// Instantiates our database class
					$class    = 'Database_' . $class;
					$instance = new $class();

					// Sets our database parameters
					if (is_array($datasource))
					{
						foreach ($datasource as $variable => $value)
						{
							$instance->$variable = $value;
						}
					}
				}

				// Caches the instance for possible reuse later
				if (isset($instance))
				{
					self::$instances['Database'][$name] = $instance;
				}

				// Returns the instance
				return self::$instances['Database'][$name];
			}
		}

		return false;
	}
}

?>
