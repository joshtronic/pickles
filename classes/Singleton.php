<?php

/**
 * Singleton Class File for PICKLES
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
 * Singleton Class
 *
 * This class is pretty much just a clone of Object, but with the constructor
 * set to private. Due to the nature of static naming in PHP, each Singleton
 * class will still need to have a getInstance() method unless you'd prefer to
 * pass the name of the class in when you call the parent's getInstance()
 * method directly.
 */
class Singleton
{
	/**
	 * Object Instances
	 *
	 * @static
	 * @access private
	 * @var    mixed
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * Establishes a Config instance for all children to enjoy
	 */
	protected function __construct()
	{
		// Logs the action to the profiler. This is done all the time as the
		// Config object is a Singleton and created some lovely infinite loops
		// when attempting to check the config variables.
		Profiler::log($this, '__construct');
	}

	/**
	 * Get Instance
	 *
	 * Gets an instance of the class that is passed in. Functions more like a
	 * factory since static classes only know themselves. Example, if you
	 * replaced $class with __CLASS__ you'd end up instantiating the parent
	 * Singleton class. So for now, this is how we're doing it.
	 *
	 * @static
	 * @param  string $class name of the class we want to create
	 * @return object instance of the class we requested
	 */
	public static function getInstance($class)
	{
		if (!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class();
		}

		return self::$instances[$class];
	}

	/**
	 * Clone
	 *
	 * Throws an error as the whole point of a Singleton is to have a single
	 * instance of the class at any given moment.
	 */
	public function __clone()
	{
		trigger_error('Can\'t clone a Singleton.', E_USER_ERROR);
	}

	/**
	 * Destructor
	 *
	 * This is where the destructor would be, but it was never firing. I think
	 * it had something to do with the way the instance of the object is being
	 * stored in the object itself, perhaps under those circumstances the
	 * destructor is never executed. If you know, get in touch.
	 */
}

?>
