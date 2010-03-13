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
		if ($this->config->disabled()) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		// Loads the default module information (if any)
		$basename = $this->config->module();

		if ($basename != null)
		{
			$module_class    = strtr($basename, '/', '_');
			$module_filename = '../modules/' . $basename . '.php';
			$css_class       = strtr($basename, '_', '-');
			$js_filename     = $basename;

			unset($basename);
		}
		
		// Attempts to override the defaults with passed information (if any)
		if (isset($_REQUEST['module']) && trim($_REQUEST['module']) != '')
		{
			$new_basename        = strtr($_REQUEST['module'], '-', '_');
			$new_module_class    = strtr($new_basename, '/', '_');
			$new_module_filename = '../modules/' . $new_basename . '.php';
			$new_css_class       = strtr($new_basename, '_', '-');
			$new_js_filename     = $new_basename;

			// File exists, proceed with override
			if (file_exists($new_module_filename))
			{
				$module_class    = $new_module_class;
				$module_filename = $new_module_filename;
				$css_class       = $new_css_class;
				$js_filename     = $new_js_filename;
			}

			unset($new_basename, $new_module_class, $new_module_filename, $new_css_class, $new_js_filename);
		}

		// Defaults the module return variable
		$module_return = null;

		// Loads the module or errors out
		if (isset($module_filename) && $module_filename != null && file_exists($module_filename))
		{
			require_once $module_filename;

			// Checks that our class exists
			if (class_exists($module_class))
			{
				$module = new $module_class;

				// Checks that our default method exists
				if (method_exists($module, '__default'))
				{
					$module_return = $module->__default();
				}
			}
		}
		else
		{
			// @todo Error handling
			// @todo Should we be creating a new generic Module?
		}
					
		// Starts up the display engine
		$display_class = 'Display_' . (isset($module->engine) ? $module->engine : DISPLAY_PHP);
		$display       = new $display_class($module->template, $module_return);

		exit('EOF');

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



		// Checks if we loaded a module file and no class was present
		if ($module['object'] != null) {

			// Potentially starts the session if it's not started already
			if ($module['object']->getSession() === true) {
				if (ini_get('session.auto_start') == 0) {
					session_start();
				}
			}

			// Potentially requests use authentication
			if ($module['object']->getAuthentication() === true) {
				if (!isset($security)) {
					$security = new Security($config, $db);
				}
				$security->authenticate();
			}

			// Checks if the display type was passed in
			if (!isset($display_type)) {
				$display_type = $module['object']->getDisplay();
			}

			// Creates a new viewer object
			$display_class = 'Display_' . $display_type;
			$display       = new $display_class($config, $error);

			// Potentially establishes caching
			$caching = $module['object']->getCaching();
			if ($caching) {
				$display->caching = $caching;
				if ($display_type == DISPLAY_SMARTY) {
					$module['object']->setSmartyObject($display->getSmartyObject());
				}
			}

			$display->prepare();

			// Potentially executes the module's logic
			if (method_exists($module['object'], '__default')) {
				$module['object']->__default();

				if ($module['object']->getCacheID()) {
					$display->cache_id = $module['object']->getCacheID();
				}
			}

			// Overrides the name and filename with the passed name
			if ($module['object']->name != null && $module['requested']['filename'] != $module['object']->name) {
				$module['requested']['filename'] = $module['object']->name;
				$module['requested']['name']     = $module['object']->name;
			}

			// Overrides the filename with the passed template
			if ($module['object']->template != null) {
				$module['requested']['filename'] = $module['object']->template;
			}

			// Overrides the shared template information with the passed shared template
			if ($module['object']->shared_template != null) {
				$module['shared']['class_name'] = $module['object']->shared_template;
				$module['shared']['filename']   = strtr($module['shared']['class_name'], '_', '/');
				$module['shared']['php_file']   = PICKLES_PATH . 'common/modules/' . $module['shared']['filename'] . '.php';
				$module['shared']['name']       = $module['shared']['filename'];
			}

			// Sets the display's properties
			$display->module_name            = $module['requested']['name'];
			$display->module_filename        = $module['requested']['filename'];
			$display->shared_module_name     = $module['shared']['name'];
			$display->shared_module_filename = $module['shared']['filename'];

			if ($this->execute_tests == true) {
				var_dump($module);
				exit('caught test');
			}

			// Loads the module data into the display to be rendered
			// @todo perhaps make this a passed variable
			$display->data = $module['object']->public;

			// Runs the requested rendering function
			$display->render($module);

			// Do some cleanup
			if (isset($security)) {
				unset($security);
			}

			unset($module, $viewer);
			unset($db, $mailer, $config, $error);
		}
	}
}

?>
