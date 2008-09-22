<?php

/**
 * PICKLES core include file
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code base is
 * established as well as the path that Smarty will use to store the compiled
 * pages.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @usage     require_once 'Pickles.php';
 */

/**
 * @todo Allow users to override the timezone from their configuration file.
 */
if (ini_get('date.timezone') == '') {
	date_default_timezone_set('America/New_York');
}

/**
 * @todo      Change the TEMP_PATH to be named more accordingly (Smarty-centric)
 *            and ditch sys_get_temp_dir() and point to a directory inside the 
 *            PICKLES path.
 */
define('PICKLES_PATH', (phpversion() < 5.3 ? dirname(__FILE__) : __DIR__) . '/');
define('TEMP_PATH',    sys_get_temp_dir() . '/pickles/smarty/' . $_SERVER['SERVER_NAME'] . '/');

/**
 * Magic function to automatically load classes
 *
 * Determines if the system needs to load a PICKLES core class or a PICKLES
 * shared model (not to be confused with site level models).
 *
 * @param  string $class Name of the class to be loaded
 * @return boolean Return value of require_once() or false (default)
 */
function __autoload($class) {
	$file = PICKLES_PATH . 'classes/' . str_replace('_', '/', $class) . '.php';
	
	if (file_exists($file)) {
		return require_once $file;
	}
	else {
		$file = PICKLES_PATH . 'models/' . str_replace('_', '/', $class) . '.php';

		if (file_exists($file)) {
			return require_once $file;
		}
	}

	return false;
}

?>
