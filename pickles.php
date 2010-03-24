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
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 * @usage     <code>require_once 'pickles.php';</code>
 */

// @todo Add options to the config to set this, and/or have it only run
//       E_STRICT display ON locally (perhaps by IP?)
ini_set('display_errors', true);
error_reporting(E_ALL | E_STRICT);

// @todo Allow users to override the timezone from their configuration file.
// Sets the timezone to avoid Smarty warnings
if (ini_get('date.timezone') == '')
{
	ini_set('date.timezone', 'America/New_York');
}

// Establishes our paths
define('PICKLES_PATH',  dirname(__FILE__) . '/');
define('SITE_PATH',     getcwd() . '/../');

define('CLASS_PATH',    PICKLES_PATH . 'classes/');

define('MODEL_PATH',    SITE_PATH . 'models/');
define('MODULE_PATH',   SITE_PATH . 'modules/');
define('TEMPLATE_PATH', SITE_PATH . 'templates/');

define('PRIVATE_PATH',  SITE_PATH . 'private/');
define('LOG_PATH',      PRIVATE_PATH . 'logs/');
define('SMARTY_PATH',   PRIVATE_PATH . 'smarty/');

// Sets up constants for the Display names
define('DISPLAY_JSON',   'JSON');
define('DISPLAY_PHP',    'PHP');
define('DISPLAY_RSS',    'RSS');
define('DISPLAY_SMARTY', 'Smarty');
define('DISPLAY_XML',    'XML');

// Creates a constant as to whether or not we have JSON available
define('JSON_AVAILABLE', function_exists('json_encode'));

/**
 * Magic function to automatically load classes
 *
 * Attempts to load a core PICKLES class or a site level data model or 
 * module class. If Smarty is being requested, will load the proper class
 * from the vendors directory
 *
 * @param  string $class Name of the class to be loaded
 * @return boolean Return value of require_once() or false (default)
 */
function __autoload($class)
{
	$loaded = false;

	if ($class == 'Smarty')
	{
		return require_once 'vendors/smarty/libs/Smarty.class.php';
	}
	else
	{
		$filename = preg_replace('/_/', '/', $class) . '.php';

		$paths = array(CLASS_PATH, MODEL_PATH, MODULE_PATH);

		foreach ($paths as $path)
		{
			if (file_exists($path . $filename))
			{
				$loaded = require_once $path . $filename;
			}
		}
	}

	return $loaded;
}

?>
