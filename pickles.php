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
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2014, Joshua Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 * @usage     <code>require_once 'pickles.php';</code>
 */

// {{{ PICKLES Constants

// @todo Finish reworking constants to be part of the Config object
if (!defined('SITE_PATH'))
{
	// Establishes our site paths, sanity check is to allow vfsStream in our tests
	define('SITE_PATH', getcwd() . '/../');
}

if (!defined('SITE_CLASS_PATH'))
{
	define('SITE_CLASS_PATH',    SITE_PATH . 'classes/');
	define('SITE_MODEL_PATH',    SITE_PATH . 'models/');
	// @todo The following 2 constants are being used in sites will need to update them before removing
	define('SITE_MODULE_PATH',   SITE_PATH . 'modules/');
	define('SITE_TEMPLATE_PATH', SITE_PATH . 'templates/');

	define('PRIVATE_PATH', SITE_PATH    . 'private/');
	define('LOG_PATH',     PRIVATE_PATH . 'logs/');
}

// }}}
// {{{ Defaults some important configuration options

// Turns on error before the config is loaded to help catch parse errors
ini_set('display_errors', true);
error_reporting(-1);

// Defaults timezone to UTC if not set
if (ini_get('date.timezone') == '')
{
	ini_set('date.timezone', 'Etc/UTC');
}

// Sets the session variables
ini_set('session.cache_expire',   86400);
ini_set('session.entropy_file',   '/dev/urandom');
ini_set('session.entropy_length', 512);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor',     1000);
ini_set('session.hash_function',  1);

// }}}
// {{{ Auto[magical] Loader

/**
 * Magic function to automatically load classes
 *
 * Attempts to load a core PICKLES class or a site level data model or
 * module class.
 *
 * @param  string $class Name of the class to be loaded
 * @return boolean Return value of require_once() or false (default)
 */
function __autoload($class)
{
	$loaded   = false;
	$filename = preg_replace('/_/', '/', $class) . '.php';

	$pickles_path  = dirname(__FILE__) . '/';
	$pickles_paths = [
		'class'  => $pickles_path . 'classes/',
		'vendor' => $pickles_path . 'vendors/',
	];

	if ($class == 'AYAH')
	{
		$loaded = require_once $pickles_paths['vendor'] . 'ayah/' . strtolower($filename);
	}
	else
	{
		// Path as the key, boolean value is whether ot not to convert back to hyphenated
		$paths = [
			$pickles_paths['class'] => false,
			SITE_CLASS_PATH         => false,
			SITE_MODEL_PATH         => false,
			SITE_MODULE_PATH        => true,
		];

		foreach ($paths as $path => $hyphenated)
		{
			// Converts the filename back to hypenated
			if ($hyphenated == true)
			{
				$filename = strtolower(preg_replace('/([A-Z]{1})/', '-$1', $filename));;
			}

			if (file_exists($path . $filename))
			{
				$loaded = require_once $path . $filename;
				break;
			}
		}
	}

	return $loaded;
}

spl_autoload_register('__autoload');

// }}}
// {{{ Loads the configuration file and sets any configuration options

// Loads the base config
$config = Config::getInstance();

// Injects PICKLES variables into the config
$config->data['pickles']['path'] = dirname(__FILE__) . '/';

// Configures any available PHP configuration options
if (is_array($config->php) && count($config->php))
{
	foreach ($config->php as $variable => $value)
	{
		ini_set($variable, $value);
	}
}

// Starts session handling (old)
if (isset($config->pickles['session']))
{
	if (session_id() == '' && $config->pickles['session'] !== false)
	{
		new Session();
	}
}

// Starts session handling (new)
if (isset($config->pickles['sessions']))
{
	if (session_id() == '' && $config->pickles['sessions'] !== false)
	{
		new Session();
	}
}

// }}}
// {{{ Defaults some internals for ease of use

if (!isset($_REQUEST['request']))
{
	$_REQUEST['request'] = 'home';
}

// }}}

?>
