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

// {{{ PICKLES Constants

// Grabs the start time in case we're profiling
define('PICKLES_START_TIME', microtime(true));

// Establishes our PICKLES paths
define('PICKLES_PATH',       dirname(__FILE__) . '/');
define('PICKLES_CLASS_PATH', PICKLES_PATH . 'classes/');

// Establishes our site paths
define('SITE_PATH', getcwd() . '/../');

define('SITE_CLASS_PATH',    SITE_PATH . 'classes/');
define('SITE_MODEL_PATH',    SITE_PATH . 'models/');
define('SITE_MODULE_PATH',   SITE_PATH . 'modules/');
define('SITE_TEMPLATE_PATH', SITE_PATH . 'templates/');

define('PRIVATE_PATH', SITE_PATH . 'private/');
define('LOG_PATH',     PRIVATE_PATH . 'logs/');
define('SMARTY_PATH',  PRIVATE_PATH . 'smarty/');

// Sets up constants for the Display names
define('DISPLAY_JSON', 'JSON');
define('DISPLAY_PHP',  'PHP');
define('DISPLAY_RSS',  'RSS');
define('DISPLAY_XML',  'XML');

// Creates a constant as to whether or not we have JSON available
define('JSON_AVAILABLE', function_exists('json_encode'));

// }}}

// Error reporting is not modified initially
// Feel free to uncomment these lines if you want error reporting on before the config is loaded
ini_set('display_errors', true);
error_reporting(-1);

// Sets the error handler
set_error_handler('__handleError');

// Defaults timezone to UTC if not set
if (ini_get('date.timezone') == '')
{
	ini_set('date.timezone', 'Etc/UTC');
}

// Loads the base config
$config = Config::getInstance();

// Determines the environment
if ($config->environment != false && is_array($config->environment))
{
	$environment = false;

	// Loops through the environments and tries to match on IP or name
	foreach ($config->environment as $name => $host)
	{
		if ((preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $host) && $_SERVER['SERVER_ADDR'] == $host) || $_SERVER['SERVER_NAME'] == $host)
		{
			// Tries to load the environment config
			$environment_config = SITE_PATH . $name . '.ini';
			if (file_exists($environment_config))
			{
				$config->load($environment_config, true);
			}
			break;
		}
	}
}

// Configures any available PHP configuration options
if (isset($config->php['display_error']))
{
	ini_set('display_errors', (boolean)$config->php['display_error']);
}

if (isset($config->php['error_reporting']))
{
	error_reporting($config->php['error_reporting']);
}

// Sets the timezone to avoid warnings
if (isset($config->php['date.timezone']))
{
	ini_set('date.timezone', $config->php['date.timezone']);
}

// Sets the error handler
if (isset($config->php['error_handler']))
{
	set_error_handler($config->php['error_handler']);
}

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

	$filename = preg_replace('/_/', '/', $class) . '.php';

	$paths = array(PICKLES_CLASS_PATH, SITE_CLASS_PATH, SITE_MODEL_PATH, SITE_MODULE_PATH);

	foreach ($paths as $path)
	{
		if (file_exists($path . $filename))
		{
			$loaded = require_once $path . $filename;
		}
	}

	return $loaded;
}

/**
 * Error handling function that thinks it's magical
 *
 * Catches errors (warnings and the like) and throws it back out as an
 * ErrorException. This really helps trapping complex errors that need a ton of
 * sanity checks, just try / catch and you're good. Also, this isn't a magic
 * function, but I opted to use the __ prefix to help avoid a naming collision
 * since namespace support is 5.3+ and PICKLES strives to be 5.0+ compatible.
 *
 * @param  integer        $number error number
 * @param  string         $string error string (message)
 * @param  string         $file name of the file with the error
 * @param  integer        $line line number the error occurred on
 * @param  array          $context variables that were in play
 * @return ErrorException not really returned, but worth documenting
 */
function __handleError($number, $string, $file, $line, array $context)
{
	// Handle hacktastic @ error suppression. Seriously, don't ever use @
	if (error_reporting() === 0)
	{
		return false;
	}

	throw new ErrorException($string, 0, $number, $file, $line);
}

?>
