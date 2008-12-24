<?php

/**
 * Controller Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Controller Class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads modules, serves up user authentication when
 * the module asks for it, and loads the viewer that the module has requested.
 * Default values are present to make things easier on the user.
 *
 * @usage <code>new Controller($config);</code>
 */
class Controller extends Object {

	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic is
	 * executed automatically via use of a constructor.
	 *
	 * @param mixed Config object or filename (optional)
	 */
	public function __construct($config = null) {
		parent::__construct();
		
		// Creates the core objects that don't need a Config object
		$error = new Error();

		// Check the passed config variables object type
		if (is_object($config)) {
			if ($config instanceof Config === false) {
				$error->addWarning('Passed object is not an instance of Config');
				$config = null;
			}
		}

		// Config filename to be loaded
		$filename = null;

		// Checks if the config value is a filename
		if (is_string($config)) {
			if (file_exists($config)) {
				$filename = $config;
			}
			else {
				$error->addWarning('Passed config filename does not exist');
			}
		}
		else {
			$config = null;
		}

		// If no Config object is passed (or it's cleared), create a new one from assumptions
		if ($config == null) {
			$config = new Config();
		}
		else {
			$config = new Config($filename);
		}

		unset($filename);

		// Creates all the other core objects we need to pass around
		$db     = new DB($config, $error);
		$mailer = new Mailer($config, $error);

		// Generate a generic "site down" message
		if ($config->getDisabled()) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		// Grab the passed in module or use the default
		$module_name = isset($_REQUEST['module']) ? strtr($_REQUEST['module'], '-', '_') : $config->getDefaultModule();

		/**
		 * @todo Maybe the logout shouldn't be an internal thing, what if the
		 *       user wanted to call the logout page something else? or better
		 *       yet, they want to next it, like /users/logout?
		 */
		if ($module_name == 'logout') {
			$security = new Security($config, $db);
			$security->logout();
		}
		else {
			// Loads the requested module's information
			$module_filename = $module_name;
			$module_file     = '../modules/' . $module_filename . '.php';
			$module_class    = strtr($module_filename, '/', '_');
			$module_name     = split('_', $module_class);

			// Establishes the shared module information
			$shared_module_class    = $config->getSharedModule($module_class);
			$shared_module_filename = strtr($shared_module_class, '_', '/');
			$shared_module_file     = PICKLES_PATH . 'common/modules/' . $shared_module_filename . '.php';
			$shared_module_name     = split('_', $shared_module_class);

			// Tries to load the site level module
			if (file_exists($module_file)) {
				require_once $module_file;

				if (class_exists($module_class)) {
					$module = new $module_class($config, $db, $mailer, $error);
				}
			}
			// Tries to load the shared module
			else if (file_exists($shared_module_file) && $shared_module_name != false) {
				require_once $shared_module_file;

				if (class_exists($shared_module_class)) {
					$module = new $shared_module_class($config, $db, $mailer, $error);
				}
			}
			// Loads the stock module
			else {
				$module = new Module($config, $db, $mailer, $error);
			}

			// Checks if we loaded a module file and no class was present
			if ($module != null) {

				// Potentially starts the session if it's not started already
				if ($module->getSession() === true) {
					if (ini_get('session.auto_start') == 0) {
						session_start();
					}
				}

				// Potentially requests use authentication
				if ($module->getAuthentication() === true) {
					if (!isset($security)) {
						$security = new Security($config, $db);
					}
					$security->authenticate();
				}

				// Creates a new viewer object
				$display_type  = $module->getDisplay();
				$display_class = 'Display_' . $display_type;
				$display = new $display_class($config, $error);

				// Potentially establishes caching
				$caching = $module->getCaching();
				if ($caching) {
					$display->caching = $caching;

					if ($display_type == DISPLAY_SMARTY) {
						$module->setSmartyObject($display->getSmartyObject());
					}
				}

				$display->prepare();

				// Potentially executes the module's logic
				if (method_exists($module, '__default')) {
					$module->__default();

					if ($module->getCacheID()) {
						$display->cache_id = $module->getCacheID();
					}

					if (isset($mailer->message)) {
						$status = $mailer->send();
						$module->type    = $status['type'];
						$module->message = $status['message'];
					}
				}

				// If the loaded module has a name, use it to override
				if ($module->name != null && $module_filename != $module->name) {
					$module_filename = $module->name;
					$module_file     = '../modules/' . $module_filename . '.php';
					$module_class    = strtr($module_filename, '/', '_');
					$module_name     = split('_', $module_class);
				}

				// Sets the display's properties
				$display->module_filename        = $module_filename;
				$display->module_name            = $module_name;
				$display->shared_module_filename = $shared_module_filename;
				$display->shared_module_name     = $shared_module_name;


				// Loads the module data into the display to be rendered
				/**
				 * @todo perhaps make this a passed variable
				 */
				$display->data = $module->getData();

				// Runs the requested rendering function
				$display->render();

				// Do some cleanup
				if (isset($security)) {
					unset($security);
				}

				unset($module, $viewer);
				unset($db, $mailer, $config, $error);
			}
		}
	}
}

?>
