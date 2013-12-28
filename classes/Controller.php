<?php

/**
 * Single Entry Controller
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Joshua Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Joshua Sherman
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
	 * Constructor
	 *
	 * To save a few keystrokes, the Controller is executed as part of the
	 * constructor instead of via a method. You either want the Controller or
	 * you don't.
	 */
	public function __construct()
	{
		parent::__construct();

		// Generate a generic "site down" message if the site is set to be disabled
		// @todo Clean this up to be just a single sanity check
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

		// Catches requests that aren't lowercase
		$lowercase_request = strtolower($_REQUEST['request']);

		if ($_REQUEST['request'] != $lowercase_request)
		{
			// @todo Rework the Browser class to handle the 301 (perhaps redirect301()) to not break other code
			header('Location: ' . substr_replace($_SERVER['REQUEST_URI'], $lowercase_request, 1, strlen($lowercase_request)), true, 301);
			exit;
		}

		// Grabs the requested page
		$request = $_REQUEST['request'];

		// Loads the module's information
		$module_class    = strtr($request, '/', '_');
		$module_filename = SITE_MODULE_PATH . $request . '.php';
		$module_exists   = isset($module_filename) && $module_filename != null && file_exists($module_filename);

		// Attempts to instantiate a module instance
		if ($module_exists)
		{
			// @todo Is this redundant because of our autoloader?
			require_once $module_filename;

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
		if ($module->security)
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

					if (in_array($security_check_type, ['IS', 'HAS', 'BETWEEN']))
					{
						$security_check_class = $security_check_type;
					}

					unset($module_security['type']);
				}

				$module_security_levels = [];

				// If there's a level(s) key use it
				foreach (['level', 'levels'] as $security_level_key)
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
						if ($security_level_count)
						{
							$is_authenticated = Security::hasLevel($module_security_levels);
						}
						break;

					case 'IS':
						if ($security_level_count)
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

			if (!$is_authenticated)
			{
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					exit('{"status": "error", "message": "You are not properly authenticated, try logging out and back in."}');
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

		// Gets the profiler status
		$profiler = $this->config->pickles['profiler'];
		$profiler = $profiler === true || stripos($profiler, 'timers') !== false;

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
			// Starts a timer before the module is executed
			if ($profiler)
			{
				Profiler::timer('module ' . $default_method);
			}

			$valid_request       = false;
			$valid_security_hash = false;
			$error_message       = 'An unexpected error has occurred.';

			// Determines if the request method is valid for this request
			if ($module->method)
			{
				if (!is_array($module->method))
				{
					$module->method = [$module->method];
				}

				$request_method = $_SERVER['REQUEST_METHOD'];

				foreach ($module->method as $method)
				{
					if ($request_method == strtoupper($method))
					{
						$valid_request = true;
						break;
					}
				}

				if (!$valid_request)
				{
					$error_message = 'There was a problem with your request method.';
				}
			}
			else
			{
				$valid_request = true;
			}

			// Validates the hash if applicable
			if ($valid_request && $module->hash)
			{
				if (isset($_REQUEST['security_hash']))
				{
					// @todo Does this need to be === ?
					$hash_value = $module->hash === true ? get_class($module) : $module->hash;

					if (Security::generateHash($hash_value) == $_REQUEST['security_hash'])
					{
						$valid_security_hash = true;
					}
					else
					{
						$error_message = 'Invalid security hash.';
					}
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

			if ($valid_request && $valid_security_hash && $module->validate)
			{
				$validation_errors = $module->__validate();

				if ($validation_errors)
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
					$module_return = $module->return_data;
				}
				else
				{
					$module_return = array_merge($module_return, $module->return_data);
				}
			}

			// Stops the module timer
			if ($profiler)
			{
				Profiler::timer('module ' . $default_method);
			}

			// @todo Set this in the module and use $module->return and rename module->return to module->data?
			$module->return = ['template', 'json'];

			// Checks if we have any templates
			$templates = [
				SITE_TEMPLATE_PATH . '__shared/' . $module->template . '.phtml',
				SITE_TEMPLATE_PATH . $_REQUEST['request'] . '.phtml',
			];

			$module->template = [];
			$child_exists     = file_exists($templates[1]);
			$template_exists  = false;

			if (file_exists($templates[0]) && $child_exists)
			{
				$module->template = $templates;
				$template_exists  = true;
			}
			elseif ($child_exists)
			{
				$module->template = $templates[1];
				$template_exists  = true;
			}

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

					// @todo Add redirect(url, code) and clean this up
					header('Location: ' . $redirect_url, 404);
					exit;
				}
			}

			$display            = new Display();
			$display->return    = $module->return;
			$display->templates = $module->template;
			$display->module    = isset($module_return) ? $module_return : ['status' => 'error', 'message' => $error_message];

			// @todo Check for $module->meta variable first, then remove entirely when sites are updated
			$display->meta      = [
				'title'       => $module->title,
				'description' => $module->description,
				'keywords'    => $module->keywords
			];
		}

		// Starts a timer for the display rendering
		if ($profiler)
		{
			Profiler::timer('display render');
		}

		// Renders the content
		$output = $display->render();

		echo $output;

		// Stops the display timer
		if ($profiler)
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

		// Display the Profiler's report if the stars are aligned
		if ($this->config->pickles['profiler'])
		{
			Profiler::report();
		}
	}
}

?>
