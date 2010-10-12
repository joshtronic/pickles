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
			if (is_array($config->datasources))
			{
				$datasources = $config->datasources;
				$name        = key($datasources);
			}
		}

		if ($name != null)
		{
			if (isset($config->datasources[$name]))
			{
				$datasource = $config->datasources[$name];
				$datasource['type'] = strtolower($datasource['type']);

				switch ($datasource['type'])
				{
					case 'mysql':
						if (!isset(self::$instances['Database'][$name]))
						{
							self::$instances['Database'][$name] = new Database_MySQL($datasource['hostname'], $datasource['username'], $datasource['password'], $datasource['database']);
						}

						return self::$instances['Database'][$name];
						break;
					
					default:
						throw new Exception('Datasource type "' . $datasource['type'] . '" is invalid');
						break;
				}
			}
		}

		return false;
	}
}

?>
