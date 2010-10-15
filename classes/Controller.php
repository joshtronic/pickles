<?php

/**
 * Single Entry Controller
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

		// Generate a generic "site down" message if the site is set to be disabled
		if (isset($this->config->pickles['disabled']) && $this->config->pickles['disabled'] == true)
		{
			throw new Exception($_SERVER['SERVER_NAME'] . ' is currently<br />down for maintenance');
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

			list($basename, $module_class, $module_filename, $template_basename, $css_class, $js_basename) = $this->prepareVariables(implode('/', $request));

			unset($last_part, $request);
		}
		// Loads the default module information (if any)
		else
		{
			$default_module = isset($this->config->pickles['module']) ? $this->config->pickles['module'] : 'home';

			list($basename, $module_class, $module_filename, $template_basename, $css_class, $js_basename) = $this->prepareVariables($default_module);
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

		// Validates security level
		if ($module->security !== false)
		{
			// @todo If no type is set, default to isLevel (safer)
			// @todo If array is present and no type set, validate against each level there
			// @todo Is array is present under type, validate against each level accordingly
			if (Security::isLevel($module->security) == false)
			{
				// @todo Redirect to login page, potentially configured in the config, else /login
				// @todo Set variable for the destination, perhaps $_SESSION['__pickles']['login']['destination']
				exit;
			}
		}

		// Validates the rendering engine
		// @todo Need to validate against the module's return type(s)
		$engine = $module->engine;

		if (isset($return_type))
		{
			if (in_array(strtolower($return_type), array('json', 'rss', 'xml')))
			{
				$engine = strtoupper($return_type);
			}

			unset($return_type);
		}

		// Starts up the display engine
		$display_class = 'Display_' . $engine;
		$display       = new $display_class();

		// Assigns the template / template variables
		$display->setTemplateVariables($module->template, $template_basename, $css_class, $js_basename);

		// Checks the templates
		$template_exists = $display->templateExists();

		// If there's no valid module or template redirect
		if (!$module_exists && !$template_exists)
		{
			if (!isset($_REQUEST['request']))
			{
				throw new Exception('Way to go, you\'ve successfully created an infinite redirect loop. Good thing I was here or you would have been served with a pretty ugly browser error.<br /><br />So here\'s the deal, no templates were able to be loaded. Make sure your parent and child templates actually exist and if you\'re using non-default values, make sure they\'re defined correctly in your config.');
			}
			else
			{
				$redirect_url = '/';

				if (isset($this->config->pickles['404']) && $_REQUEST['request'] != $this->config->pickles['404'])
				{
					$redirect_url .= $this->config->pickles['404'];
				}

				header('Location: ' . $redirect_url, 404);
				exit;
			}
		}

		$meta_data     = null;
		$module_return = null;

		$profiler = (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true);

		// Attempts to execute the default method
		if (method_exists($module, '__default'))
		{
			if (isset($requested_id))
			{
				$module->setRequest(array('id' => $requested_id));
			}

			if ($profiler)
			{
				Profiler::log($module, '__default');
			}

			// Sets meta data from the module
			$display->setMetaData(array(
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			));

			/**
			 * Note to Self: When building in caching will need to let the
			 * module know to use the cache, either passing in a variable
			 * or setting it on the object
			 */
			$display->setModuleReturn($module->__default());
		}

		if ($profiler)
		{
			Profiler::log($display, 'render');
		}

		// Renders the content
		$display->render();
	}

	public function __destruct()
	{
		parent::__destruct();

		if (isset($this->config->pickles['profiler']) && $this->config->pickles['profiler'] == true)
		{
			Profiler::report();
		}
	}

	public function prepareVariables($request)
	{
		$basename          = strtr($request, '-', '_');
		$module_class      = strtr($basename, '/', '_');
		$module_filename   = SITE_MODULE_PATH . $basename . '.php';
		$template_basename = $basename;
		$css_class         = str_replace(array('_', '/', ' '), '-', $basename);
		$js_basename       = $basename;

		return array($basename, $module_class, $module_filename, $template_basename, $css_class, $js_basename);
	}
}

?>
