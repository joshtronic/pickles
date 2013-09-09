<?php

/**
 * Single Entry Controller
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
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

		// Generate a generic "site down" message if the site is set to be disabled
		if (isset($this->config->pickles['disabled']) && $this->config->pickles['disabled'] == true)
		{
			Error::fatal($_SERVER['SERVER_NAME'] . ' is currently<br>down for maintenance');
		}

		// Checks for attributes passed in the URI
		if (strstr($_REQUEST['request'], ':'))
		{
			$parts               = explode('/', $_REQUEST['request']);
			$_REQUEST['request'] = '';

			foreach ($parts as $part)
			{
				if (strstr($part, ':'))
				{
					list($variable, $value) = explode(':', $part);
					Browser::set($variable, $value);
				}
				else
				{
					$_REQUEST['request'] .= ($_REQUEST['request'] ? '/' : '') . $part;
				}
			}
		}

		$_REQUEST['request'] = trim($_REQUEST['request']);

		// Checks the passed request for validity
		if ($_REQUEST['request'])
		{
			// Catches requests that aren't lowercase
			$lowercase_request = strtolower($_REQUEST['request']);

			if ($_REQUEST['request'] != $lowercase_request)
			{
				header('Location: ' . substr_replace($_SERVER['REQUEST_URI'], $lowercase_request, 1, strlen($lowercase_request)), true, 301);
				exit;
			}

			$request = $_REQUEST['request'];
		}
		// Loads the default module information if we don't have a valid request
		else
		{
			$request = isset($this->config->pickles['module']) ? $this->config->pickles['module'] : 'home';
		}

		// Loads the module's information
		list($module_class, $module_filename, $template_basename, $css_class, $js_basename, $dot_syntax) = $this->prepareVariables($request);

		unset($request);

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
				if ($dot_syntax)
				{
					Browser::goHome();
				}
				elseif ($this->config->pickles['logging'] === true)
				{
					Log::warning('Class named ' . $module_class . ' was not found in ' . $module_filename);
				}
			}
		}

		// If a new module object wasn't created, create a generic one
		if (!isset($module))
		{
			$module = new Module();
		}

		// Determines if the module is private and should be, well, private
		if ($module->private == true)
		{
			Browser::goHome();
		}

		// Determines if we need to serve over HTTP or HTTPS
		if ($module->secure == false && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
		{
			Browser::redirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
		elseif ($module->secure == true && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == false))
		{
			Browser::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
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
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					exit('{ "status": "error", "message": "You are not properly authenticated" }');
				}
				else
				{
					// Sets variable for the destination
					$_SESSION['__pickles']['login']['destination'] = $_REQUEST['request'] ? $_REQUEST['request'] : '/';

					// Redirect to login page, potentially configured in the config, else /login
					Browser::redirect('/' . (isset($this->config->security['login']) ? $this->config->security['login'] : 'login'));
				}
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
			if (in_array($return_type, array('JSON', 'RSS', 'XML')) && in_array($return_type, $engines))
			{
				$engine = $return_type;
			}

			unset($return_type);
		}

		// Starts up the display engine
		$display_class = 'Display_' . $engine;
		$display       = new $display_class();

		// Assigns the template / template variables
		$display->setTemplateVariables($module->template, $template_basename, $css_class, $js_basename, $module->fluid);

		// Checks the templates
		$template_exists = $display->templateExists();

		// If there is no valid module or template, then redirect
		if (!$module_exists && !$template_exists)
		{
			if (!$_REQUEST['request'])
			{
				Error::fatal('Way to go, you\'ve successfully created an infinite redirect loop. Good thing I was here or you would have been served with a pretty ugly browser error.<br><br>So here\'s the deal, no templates were able to be loaded. Make sure your parent and child templates actually exist and if you\'re using non-default values, make sure they\'re defined correctly in your config.');
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

		// Gets the profiler status
		$profiler = $this->config->pickles['profiler'];

		$default_method = '__default';
		$role_method    = null;

		if (isset($_SESSION['__pickles']['security']['role']) && !String::isEmpty($_SESSION['__pickles']['security']['role']))
		{
			$role_method = '__default_' . $_SESSION['__pickles']['security']['role'];

			if (method_exists($module, $role_method))
			{
				$default_method = $role_method;
			}
		}

		// Attempts to execute the default method
		if ($default_method == $role_method || method_exists($module, $default_method))
		{
			if (isset($requested_id))
			{
				$module->setRequest(array('id' => $requested_id));
			}

			// Starts a timer before the module is executed
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module ' . $default_method);
			}

			$valid_request       = false;
			$valid_security_hash = false;
			$error_message       = 'An unexpected error has occurred.';

			// Determines if the request method is valid for this request
			if ($module->method !== false)
			{
				$methods = (is_array($module->method) ? $module->method : array($module->method));

				$request_method = $_SERVER['REQUEST_METHOD'];

				foreach ($methods as $method)
				{
					if ($request_method == strtoupper($method))
					{
						$valid_request = true;
						break;
					}
				}

				if ($valid_request == false)
				{
					$error_message = 'There was a problem with your request method.';
				}

				unset($methods, $request_method, $method);
			}
			else
			{
				$valid_request = true;
			}

			// Validates the hash if applicable
			if ($valid_request === true && $module->hash !== false)
			{
				if (isset($_REQUEST['security_hash']))
				{
					$hash_value = ($module->hash === true ? get_class($module) : $module->hash);

					if (Security::generateHash($hash_value) == $_REQUEST['security_hash'])
					{
						$valid_security_hash = true;
					}
					else
					{
						$error_message = 'Invalid security hash.';
					}

					unset($hash_value);
				}
				else
				{
					$error_message = 'Missing security hash';
				}
			}
			else
			{
				$valid_security_hash = true;
			}

			$valid_form_input = true;

			if ($valid_request === true && $valid_security_hash === true && $module->validate !== false)
			{
				$validation_errors = $module->__validate();

				if ($validation_errors !== false)
				{
					$error_message    = implode(' ', $validation_errors);
					$valid_form_input = false;
				}
			}

			/**
			 * Note to Self: When building in caching will need to let the
			 * module know to use the cache, either passing in a variable
			 * or setting it on the object
			 */
			if ($valid_request && $valid_security_hash && $valid_form_input)
			{
				$module_return = $module->$default_method();

				if (!is_array($module_return))
				{
					$module_return = $module->return;
				}
				else
				{
					$module_return = array_merge($module_return, $module->return);
				}
			}

			$display->setModuleReturn(isset($module_return) ? $module_return : array('status' => 'error', 'message' => $error_message));

			unset($error_message);

			// Stops the module timer
			if ($profiler === true || stripos($profiler, 'timers') !== false)
			{
				Profiler::timer('module ' . $default_method);
			}

			// Sets meta data from the module
			$display->setMetaData(array(
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			));
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

	/**
	 * Destructor
	 *
	 * Dumps out the Profiler's report if applicable.
	 */
	public function __destruct()
	{
		parent::__destruct();

		// Display the Profiler's report is the stars are aligned
		if ($this->config->pickles['profiler'] != false && $this->passthru == false)
		{
			Profiler::report();
		}
	}

	/**
	 * Prepare Variables
	 *
	 * Processes the request variable and creates all the variables that the
	 * Controller needs to load the page.
	 *
	 * @param  string $basename the requested page
	 * @return array the resulting variables
	 */
	public function prepareVariables($basename)
	{
		$dot_syntax = strpos($basename, '.') !== false;

		if ($dot_syntax)
		{
			list($basename, $action) = explode('.', $basename, 2);
			$action                  = str_replace('.', '_', $action);
		}

		// Sets up all of our variables
		$module_class      = strtr($basename, '/', '_');
		$module_filename   = SITE_MODULE_PATH . $basename . '.php';
		$template_basename = $basename;
		$css_class         = $module_class;
		$js_basename       = $basename;

		if (isset($action))
		{
			$module_class      .= '_' . $action;
			$template_basename .= '/' . $action;
			$css_class         .= '_' . $action;
			$js_basename       .= '/' . $action;
		}

		// Scrubs class names with hyphens
		if (strpos($module_class, '-') !== false)
		{
			$module_class = preg_replace('/(-(.{1}))/e', 'strtoupper("$2")', $module_class);
		}

		return array($module_class, $module_filename, $template_basename, $css_class, $js_basename, $dot_syntax);
	}
}

?>
