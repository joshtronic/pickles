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
 * configuration loaded.  Loads modules, serves up user authentication when the
 * module asks for it, and loads the viewer that the module requested. Default
 * values are present to make things easier on the user.
 *
 * @usage <code>new Controller($config);</code>
 */
class Controller extends Object
{
	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic is
	 * executed automatically via use of a constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		// Generate a generic "site down" message
		if ($this->config->site['disabled'])
		{
			Error::fatal($_SERVER['SERVER_NAME'] . ' is currently<br />down for maintenance');
		}

		// Ack, not sure what page to load, throw an error
		if (!isset($_REQUEST['request']) && (empty($this->config->module['default']) || $this->config->module['default'] == null))
		{
			Error::fatal('Unable complete this request because no URI was provided and there is no default module specified in config.ini');
		}
		// Loads the requested module's information
		if (isset($_REQUEST['request']) && trim($_REQUEST['request']) != '')
		{
			$request   = explode('/', $_REQUEST['request']);
			$last_part = end($request);

			// Checks if a return type was passed in
			if (strpos($last_part, '.') !== false)
			{
				list($last_part, $return_type) = explode('.', $last_part);
			}

			// Checks if an ID (integer) was passed in
			if (preg_match('/^\d*$/', $last_part) == 1)
			{
				$requested_id = $last_part;
				array_pop($request);
			}
			else
			{
				$request[key($request)] = $last_part;
			}

			unset($last_part);

			$request = implode('/', $request);

			$basename          = strtr($request, '-', '_');
			$module_class      = strtr($basename, '/', '_');
			$module_filename   = SITE_MODULE_PATH . $basename . '.php';
			$template_basename = $basename;
			$css_class         = str_replace(array('_', '/', ' '), '-', $basename);
			$js_basename       = $basename;

			unset($request);
		}
		// Loads the default module information (if any)
		else
		{
			$basename          = $this->config->module['default'];
			$module_class      = strtr($basename, '/', '_');
			$module_filename   = SITE_MODULE_PATH . $basename . '.php';
			$template_basename = $basename;
			$css_class         = str_replace(array('_', '/', ' '), '-', $basename);
			$js_basename       = $basename;
		}

		unset($basename);

		$module_exists = (isset($module_filename) && $module_filename != null && file_exists($module_filename));

		// Instantiates an instance of the module
		if ($module_exists)
		{
			require_once $module_filename;

			// Checks that our class exists
			if (class_exists($module_class))
			{
				$module = new $module_class;
			}
			else
			{
				Log::warning('Class named ' . $module_class . ' was not found in ' . $module_filename);
			}
		}

		// If a new module object wasn't created, create a generic one
		if (!isset($module))
		{
			$module = new Module();
		}

		// Establishes the session
		if (ini_get('session.auto_start') == 0)
		{
			if ($module->session)
			{
				session_start();
			}
			else
			{
				session_write_close();
			}
		}

		// Validates the rendering engine
		if (isset($return_type))
		{
			if (in_array(strtolower($return_type), array('json', 'rss', 'xml')))
			{
				$engine = strtoupper($return_type);
			}

			unset($return_type);
		}

		// Defaults the rendering engine
		if (!isset($engine))
		{
			$engine = $module->engine;
		}

		// Starts up the display engine
		$display_class = 'Display_' . $engine;
		$display       = new $display_class($module->template, $template_basename);

		// If there's no valid module or template redirect
		// @todo The == 1 portion needs to be refactored as it's not mandatory to use a parent template
		if (!$module_exists && (!$display->templateExists() || $display->templateExists() == 1))
		{
			header('Location: /', 404);
		}

		$module_return = null;

		// Attempts to execute the default method
		if (method_exists($module, '__default'))
		{
			if (isset($requested_id))
			{
				$module->setRequest(array('id' => $requested_id));
			}

			/**
			 * Note to Self: When building in caching will need to let the
			 * module know to use the cache, either passing in a variable
			 * or setting it on the object
			 */
			$module_return = $module->__default();
		}

		$display->prepare($css_class, $js_basename, $module_return);

		// Renders the content
		$display->render();
	}
}

?>
