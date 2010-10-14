<?php

/**
 * Database Class File for PICKLES
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
	 * Get instance
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
				
				$datasource['type'] = strtolower($datasource['type']);

				switch ($datasource['type'])
				{
					// MongoDB
					case 'mongo':
						// Assembles the server string
						$server = 'mongodb://';

						if (isset($datasource['username']))
						{
							$server .= $datasource['username'];

							if (isset($datasource['password']))
							{
								$server .= ':' . $datasource['password'];
							}

							$server .= '@';
						}

						$server .= $datasource['hostname'] . ':' . $datasource['port'] . '/' . $datasource['database'];

						// Attempts to connect
						try
						{
							$instance = new Mongo($server, array('persist' => 'pickles'));

							// If we have database and collection, attempt to assign them
							if (isset($datasource['database']))
							{
								$instance = $instance->$datasource['database'];

								if (isset($datasource['collection']))
								{
									$instance = $instance->$datasource['collection'];
								}
							}
						}
						catch (Exception $exception)
						{
							throw new Exception('Unable to connect to Mongo database');
						}
						break;
				
					// PDO Types
					case 'mysql':
					case 'postgresql':
					case 'sqlite':
						if (!isset(self::$instances['Database'][$name]))
						{
							$datasource['type'] = str_replace('sql', 'SQL', ucwords($datasource['type']));

							$class = 'Database_PDO_' . $datasource['type'];

							$instance = new $class();
						
							if (isset($datasource['hostname']))
							{
								$instance->setHostname($datasource['hostname']);
							}

							if (isset($datasource['port']))
							{
								$instance->setPort($datasource['port']);
							}

							if (isset($datasource['socket']))
							{
								$instance->setSocket($datasource['socket']);
							}

							if (isset($datasource['username']))
							{
								$instance->setUsername($datasource['username']);
							}

							if (isset($datasource['password']))
							{
								$instance->setPassword($datasource['password']);
							}

							if (isset($datasource['database']))
							{
								$instance->setDatabase($datasource['database']);
							}
						}
						break;
					
					default:
						throw new Exception('Datasource type "' . $datasource['type'] . '" is invalid');
						break;
				}

				if (isset($instance))
				{
					self::$instances['Database'][$name] = $instance;
				}

				return self::$instances['Database'][$name];
			}
		}

		return false;
	}
}

?>
