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
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
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

// Sets up constants for the Display names
define('DISPLAY_JSON', 'JSON');
define('DISPLAY_PHP',  'PHP');
define('DISPLAY_RSS',  'RSS');
define('DISPLAY_XML',  'XML');

// Creates a constant as to whether or not we have JSON available
define('JSON_AVAILABLE', function_exists('json_encode'));

// }}}

// {{{ Defaults some important configuration options

// Error reporting is not modified initially
// Feel free to uncomment these lines if you want error reporting on before the config is loaded
// ini_set('display_errors', true);
// error_reporting(-1);

// Sets the error and exception handlers
set_error_handler('__handleError');
set_exception_handler('__handleException');

// Defaults timezone to UTC if not set
if (ini_get('date.timezone') == '')
{
	ini_set('date.timezone', 'Etc/UTC');
}

// }}}

// {{{ Loads the configuration file and sets any configuration options

// Loads the base config
$config = Config::getInstance();

// Configures any available PHP configuration options
if (isset($config->php['display_errors']))
{
	ini_set('display_errors', (boolean)$config->php['display_errors']);
}

if (isset($config->php['error_reporting']))
{
	error_reporting($config->php['error_reporting']);
}

// Sets the timezone
if (isset($config->php['date.timezone']))
{
	ini_set('date.timezone', $config->php['date.timezone']);
}

// Sets the error handler
if (isset($config->php['error_handler']))
{
	set_error_handler($config->php['error_handler']);
}

// Sets the exception handler
if (isset($config->php['exception_handler']))
{
	set_exception_handler($config->php['exception_handler']);
}

// }}}

// {{{ Auto[magical] Loader

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

// }}}

// {{{ Error Handler

/**
 * Error handling function that thinks it's magical
 *
 * Catches errors (warnings and the like) and throws it back out as an
 * ErrorException. This really helps trapping complex errors that need a ton of
 * sanity checks, just try / catch and you're good. Also, this isn't a magic
 * function, but I opted to use the __ prefix to help avoid a naming collision
 * since namespace support is 5.3+ and PICKLES strives to be 5.0+ compatible.
 *
 * Keep in mind that fatal errors cannot and will not be handled.
 * 
 * @param  integer $errno the level of the error raised
 * @param  string  $errstr the error message
 * @param  string  $errfile filename that the error was raised in
 * @param  integer $errline line number the error was raised at
 * @param  array   $errcontext array of every vairable that existed in scope
 * @return ErrorException not really returned, but worth documenting
 */
function __handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
	// Handle hacktastic @ error suppression. Seriously, don't ever use @
	if (error_reporting() === 0)
	{
		return false;
	}

	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// }}}

// {{{ Exception Handler

/**
 * Top level exception handling function
 *
 * Catches uncaught exceptions and displays them.
 *
 * @param object $exception the exception
 */
function __handleException($exception)
{
	?>
	<style>
		#pickles-exception
		{
			background: #212121;
			width: 800px;
			margin: 0 auto;
			margin-top: 20px;
			margin-bottom: 20px;
			border-radius: 20px;
			-moz-border-radius: 20px;
			-webkit-border-radius: 20px;
			box-shadow: 0 3px 4px #000;
			-moz-box-shadow: 0 3px 4px #000;
			-webkit-box-shadow: 0 3px 4px #000;
			border: 6px solid #666;
			padding: 10px 20px 20px;
			font-family: monospace;
			font-size: 12px;
			text-align: left;
		}
		#pickles-exception table
		{
			width: 100%;
		}
		#pickles-exception table tr th, #pickles-exception table tr td
		{
			padding: 10px;
		}
		#pickles-exception .even
		{
			background-color: #323232;
		}
		#pickles-exception, #pickles-exception table tr td, #pickles-exception table tr th
		{
			color: #efefe8;
		}
	</style>
	<div id="pickles-exception">
		<strong style="font-size:1.5em">Uncaught Exception</strong><br /><br />
		<table style="border-collapse:separate;border-spacing:1px;border-radius:10px;text-shadow:1px 1px 1px #000;text-align:center">
			<tr><td style="background-color:#480000;padding:10px">
				<div style="font-size:1.5em;font-style:italic"><?php echo $exception->getMessage(); ?></div>
				<div style="font-size:1.2em"><?php echo $exception->getFile(); ?> on line <?php echo $exception->getLine(); ?></div>
				</td>
			</tr>
		</table>
		
		<table>
			<tr>
				<th style="text-align:left" colspan="2">Trace</th>
				<th>File</th>
				<th style="text-align:right">Line</th>
			</tr>
			<?php
			$trace = $exception->getTrace();
			rsort($trace);

			foreach ($trace as $key => $data)
			{
				$method = '';
				
				if (isset($data['class']))
				{
					$method .= $data['class'] . $data['type'];
				}
				
				$method .= $data['function'] . '()';
				?>
				<tr>
					<td style="font-weight:bold;color:#999"><?php echo $key + 1; ?>.</td>
					<td><?php echo $method; ?></td>
					<td><?php echo isset($data['file']) ? $data['file'] : __FILE__; ?></td>
					<td style="text-align:right"><?php echo isset($data['line']) ? $data['line'] : '0'; ?></td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
	<br /><br />
	<?php
}

// }}}

?>
