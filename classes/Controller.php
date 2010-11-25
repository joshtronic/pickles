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
	 * Pass Thru
	 *
	 * Whether or not the page being loaded is simple a pass thru for an
	 * internal PICKLES file. The point of this variable is to suppress the
	 * profiler report in the destructor.
	 *
	 * @access private
	 * @var    boolean
	 */
	private $passthru = false;

	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic is
	 * executed automatically via use of a constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (isset($_REQUEST['request']))
		{
			// Catches requests to PICKLES core files and passes them through
			if (preg_match('/^__pickles\/(css|js)\/.+$/', $_REQUEST['request']))
			{
				// Checks that the file exists
				$file = str_replace('__pickles', PICKLES_PATH, $_REQUEST['request']);
				if (file_exists($file))
				{
					// Sets the pass thru flag and dumps the data
					$this->passthru = true;
					exit(file_get_contents($file));
				}
			}
			// Catches requests to the __shared directory
			if (preg_match('/^__shared/', $_REQUEST['request']))
			{
				header('Location: /');
				exit;
			}
		}

		// Generate a generic "site down" message if the site is set to be disabled
		if (isset($this->config->pickles['disabled']) && $this->config->pickles['disabled'] == true)
		{
			Error::fatal($_SERVER['SERVER_NAME'] . ' is currently<br />down for maintenance');
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
			$is_authenticated = false;

			if (is_array($module->security))
			{
				$module_security      = $module->security;
				$security_check_class = 'isLevel';

				// Checks the type and validates it
				if (isset($module_security['type']))
				{
					$security_check_type = strtoupper($module_security['type']);

					if (in_array($security_check_type, array('IS', 'HAS', 'BETWEEN')))
					{
						$security_check_class = $security_check_type;
					}

					unset($security_check_type, $module_security['type']);
				}

				$module_security_levels = array();

				// If there's a level(s) key use it
				foreach (array('level', 'levels') as $security_level_key)
				{
					if (isset($module_security[$security_level_key]))
					{
						if (is_array($module_security[$security_level_key]))
						{
							array_merge($module_security_levels, $module_security[$security_level_key]);
						}
						else
						{
							$module_security_levels[] = $module_security[$security_level_key];
						}

						unset($module_security[$security_level_key]);
					}
				}

				// Assume everything left in the array is a level and add it to the array
				array_merge($module_security_levels, $module_security);

				$security_level_count = count($module_security_levels);

				switch ($security_check_class)
				{
					case 'BETWEEN':
						if ($security_level_count >= 2)
						{
							$is_authenticated = Security::betweenLevel($module_security_levels[0], array_pop($module_security_levels));
						}
						break;

					case 'HAS':
						if ($security_level_count > 0)
						{
							$is_authenticated = Security::hasLevel($module_security_levels);
						}
						break;
					
					case 'IS':
						if ($security_level_count > 0)
						{
							$is_authenticated = Security::isLevel($module_security_levels);
						}
						break;
				}
			}
			else
			{
				$is_authenticated = Security::isLevel($module->security);
			}

			if ($is_authenticated == false)
			{
				// Sets variable for the destination
				$_SESSION['__pickles']['login']['destination'] = $_REQUEST['request'];

				// Redirect to login page, potentially configured in the config, else /login
				header('Location: /' . (isset($this->config->security['login']) ? $this->config->security['login'] : 'login'));

				exit;
			}
		}

		// Validates the rendering engine
		$engines = is_array($module->engine) ? array_values($module->engine) : array($module->engine);
		$engines = array_combine($engines, $engines);
		$engine  = current($engines);

		// Possibly overrides the engine with the passed return type
		if (isset($return_type))
		{
			$return_type = strtoupper($return_type);

			// Validates the return type against the module
			// @todo Update RSS class and add RSS back to the list of possible return types
			if (in_array($return_type, array('JSON', 'XML')) && in_array($return_type, $engines))
			{
				$engine = $return_type;
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

		// If there is no valid module or template, then redirect
		if (!$module_exists && !$template_exists)
		{
			if (!isset($_REQUEST['request']))
			{
				Error::fatal('Way to go, you\'ve successfully created an infinite redirect loop. Good thing I was here or you would have been served with a pretty ugly browser error.<br /><br />So here\'s the deal, no templates were able to be loaded. Make sure your parent and child templates actually exist and if you\'re using non-default values, make sure they\'re defined correctly in your config.');
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

		// Gets the profiler status
		$profiler = $this->config->pickles['profiler'];

		// Attempts to execute the default method
		if (method_exists($module, '__default'))
		{
			if (isset($requested_id))
			{
				$module->setRequest(array('id' => $requested_id));
			}

			// Sets meta data from the module
			$display->setMetaData(array(
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			));

			// Starts a timer before the module is executed
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module __default');
			}

			/**
			 * Note to Self: When building in caching will need to let the
			 * module know to use the cache, either passing in a variable
			 * or setting it on the object
			 */
			$display->setModuleReturn($module->__default());
			
			// Stops the module timer
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module __default');
			}
		}

		// Starts a timer for the display rendering
		if ($profiler === true || stripos($profiler, 'timers') !== false)
		{
			Profiler::timer('display render');
		}

		// Renders the content
		$display->render();
		
		// Steps the display timer
		if ($profiler === true || stripos($profiler, 'timers') !== false)
		{
			Profiler::timer('display render');
		}
	}

	public function __destruct()
	{
		parent::__destruct();

		// Display the Profiler's report is the stars are aligned
		if ($this->config->pickles['profiler'] != false && $this->passthru == false)
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
