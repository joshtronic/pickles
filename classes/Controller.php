<?php

/**
 * Single Entry Controller
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
 * Controller Class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads modules, serves up user authentication when
 * the module asks for it, and loads the viewer that the module requested.
 * Default values are present to make things easier on the user.
 *
 * @usage <code>new Controller($config);</code>
 */
class Controller extends Object
{
	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic
	 * is executed automatically via use of a constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Generate a generic "site down" message
		if ($this->config->site['disabled']) {
			// @todo migrate all the markup into an HTML class to easily generate these kinds of pages
			exit('
				<!DOCTYPE html>
				<html>
					<head>
						<title>' . $_SERVER['SERVER_NAME'] . '</title>
						<style>
							html{background:#eee;font-family:Verdana;width:100%}
							body{background:#fff;padding:20px;-moz-border-radius:20px;-webkit-border-radius:20px;width:550px;margin:0 auto;margin-top:100px;text-align:center}
							h1{font-size:1.5em;color:#3a6422;text-shadow:#070d04 1px 1px 1px;margin:0}
						</style>
					</head>
					<body>
						<h1>' . $_SERVER['SERVER_NAME'] . ' is currently down for maintenance</h1>
					</body>
				</html>
			');
		}

		// Loads the requested module's information
		if (isset($_REQUEST['module']) && trim($_REQUEST['module']) != '')
		{
			$basename          = strtr($_REQUEST['module'], '-', '_');
			$module_class      = strtr($basename, '/', '_');
			$module_filename   = MODULE_PATH . $basename . '.php';
			$template_basename = $basename;
			$css_class         = strtr($basename, '_', '-');
			$js_basename       = $basename;
		}
		// Loads the default module information (if any)
		else
		{
			$basename          = $this->config->module['default'];
			$module_class      = strtr($basename, '/', '_');
			$module_filename   = MODULE_PATH . $basename . '.php';
			$template_basename = $basename;
			$css_class         = strtr($basename, '_', '-');
			$js_basename       = $basename;
		}

		unset($basename);

		$module_exists = (isset($module_filename) && $module_filename != null && file_exists($module_filename));

		// Instantiates an instance of the module
		if ($module_exists)
		{
			require_once $module_filename;

			// Checks that our class exists
			// @todo Probably should throw a warning here?
			if (class_exists($module_class))
			{
				$module = new $module_class;
			}
		}

		// If a new module object wasn't created, create a generic one
		if (!isset($module))
		{
			$module = new Module();
		}

		// Establishes the session
		// @todo If ->session == false and .auto_start == 1 should I 86 the sesson?
		if ($module->session)
		{
			if (ini_get('session.auto_start') == 0)
			{
				session_start();
			}
		}

		// Starts up the display engine
		$display_class = 'Display_' . $module->engine;
		$display       = new $display_class($module->template, $template_basename);

		// If there's no valid module or template redirect
		// @todo can cause infinite loop...
		if (!$module_exists && !$display->templateExists())
		{
			header('Location: /', 404);
		}
			
		$module_return = null;

		// Attempts to execute the default method
		if (method_exists($module, '__default'))
		{
			// @todo When building in caching will need to let the module know to use the cache, either passing in a variable or setting it on the object
			$module_return = $module->__default();
		}
			
		$display->prepare($css_class, $js_basename, $module_return);

		// Renders the content
		$display->render();

		/*
		// @todo Get this logic uncommented
		// Checks if we have a display type passed in
		if (strpos($module['requested']['name'], '.') !== false) {
			list($module['requested']['name'], $display_type) = explode('.', $module['requested']['name']);

			// Checks for validity, only JSON, RSS and XML can be passed in
			switch (strtolower($display_type)) {
				case 'json':
				case 'rss':
				case 'xml':
					$display_type = strtoupper($display_type);
					break;

				default:
					// @todo Add conditional for the environment
					if ($display_type == 'test') {
						$this->execute_tests = true;
					}

					unset($display_type);
					break;
			}
		}
		*/
	}
}

?>
