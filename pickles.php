<?php

/**
 * PICKLES core include file
 *
 * This is the file that you include on the page you're instantiating the
 * controller from (typically index.php).  The path to the PICKLES code base
 * is established as well as the path that Smarty will use to store the
 * compiled pages.
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 * @usage     <code>require_once 'Pickles.php'</code>;
 */

/**
 * @todo Allow users to override the timezone from their configuration file.
 */
// Sets the timezone to avoid Smarty warnings
if (ini_get('date.timezone') == '') {
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

/**
 * Magic function to automatically load classes
 *
 * Determines if the system needs to load a PICKLES core class or
 * a PICKLES shared module (not to be confused with site level modules).
 *
 * @param  string $class Name of the class to be loaded
 * @return boolean Return value of require_once() or false (default)
 */
function __autoload($class) {

	// @todo Not sure this will come up, but right now core classes can only be nested a single directory deep (e.g. /classes/Display/Smarty.php)
	$filename = preg_replace('/_/', '/', $class, 1) . '.php';

	$class_file  = PICKLES_PATH . 'classes/' . $filename;
	$module_file = PICKLES_PATH . 'common/modules/' . $filename;

	// Loads the class file
	if (file_exists($class_file)) {
		return require_once $class_file;
	}
	// Loads the shared module
	else if (file_exists($module_file)) {
		return require_once $module_file;
	}
	// Loads Smarty
	else if ($class == 'Smarty') {
		return require_once 'contrib/smarty/libs/Smarty.class.php';
	}
	// Loads nothing
	else {
		return false;
	}
}

?>
