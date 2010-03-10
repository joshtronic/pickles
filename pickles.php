<?php

/**
 * Core PICKLES Include File
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code
 * base is established as well as the path that Smarty will use to store
 * the compiled pages.
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   pickles
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 * @usage     <code>require_once 'pickles.php';</code>;
 */

// @todo Allow users to override the timezone from their configuration file.
// Sets the timezone to avoid Smarty warnings
if (ini_get('date.timezone') == '')
{
	ini_set('date.timezone', 'America/New_York');
}

// Establishes our paths
define('SITE_PATH',    getcwd() . '/');
define('PICKLES_PATH', dirname(__FILE__) . '/');
define('VAR_PATH',     PICKLES_PATH . 'var/' . $_SERVER['SERVER_NAME'] . '/');
define('LOG_PATH',     VAR_PATH . 'logs/');
define('SMARTY_PATH',  VAR_PATH . 'smarty/');

// Sets up constants for the Display names
define('DISPLAY_JSON',   'JSON');
define('DISPLAY_PHP',    'PHP');
define('DISPLAY_RSS',    'RSS');
define('DISPLAY_SMARTY', 'Smarty');
define('DISPLAY_XML',    'XML');

/**
 * Magic function to automatically load classes
 *
 * Determines if the system needs to load a PICKLES core class or
 * a PICKLES shared module (not to be confused with site level modules).
 *
 * @param  string $class Name of the class to be loaded
 * @return boolean Return value of require_once() or false (default)
 */
function __autoload($class)
{
	$filename = preg_replace('/_/', '/', $class) . '.php';

	$class_file  = PICKLES_PATH . 'classes/' . $filename;
	$module_file = PICKLES_PATH . 'common/modules/' . $filename;
	$local_file  = $_SERVER['DOCUMENT_ROOT'] . '/../modules/' . $filename;
	$test_file   = $_SERVER['DOCUMENT_ROOT'] . '/../tests/'   . str_replace('Test', '', $filename);

	// Loads the class file
	if (file_exists($class_file))
	{
		return require_once $class_file;
	}
	// Loads the shared module
	elseif (file_exists($module_file))
	{
		return require_once $module_file;
	}
	// Loads the local module
	elseif (file_exists($local_file))
	{
		return require_once $local_file;
	}
	// Loads Smarty
	elseif ($class == 'Smarty')
	{
		return require_once 'vendors/smarty/libs/Smarty.class.php';
	}
	// Loads a test class
	elseif (preg_match('/Test$/', $class) && file_exists($test_file))
	{
		return require_once $test_file;
	}
	// Loads nothing
	else
	{
		return false;
	}
}

?>
