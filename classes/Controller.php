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
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
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
		#$module_name = isset($_REQUEST['module']) ? strtr($_REQUEST['module'], '-', '_') : $config->getDefaultModule();
		$module['requested']['name'] = isset($_REQUEST['module']) ? $_REQUEST['module'] : $config->getDefaultModule();

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
					unset($display_type);
					break;
			}
		}

		/**
		 * @todo Maybe the logout shouldn't be an internal thing, what if the
		 *       user wanted to call the logout page something else? or better
		 *       yet, they want to next it, like /users/logout?
		 * @todo May want to make it work from /store/admin/logout and not just from /
		 */
		if ($module['requested']['name'] == 'logout') {
			$security = new Security($config, $db);
			$security->logout();
		}
		else {
			// Loads the requested module's information
			$module['requested']['filename']   = strtr($module['requested']['name'], '-', '_');
			$module['requested']['php_file']   = '../modules/' . $module['requested']['filename'] . '.php';
			$module['requested']['class_name'] = strtr($module['requested']['filename'], '/', '_');

			// Establishes the shared module information
			// @todo Bug with requested modules with a dash in the name (store-locator == shared module 'store')
			$module['shared']['class_name'] = $config->getSharedModule($module['requested']['class_name']);
			$module['shared']['filename']   = strtr($module['shared']['class_name'], '_', '/');
			$module['shared']['php_file']   = PICKLES_PATH . 'common/modules/' . $module['shared']['filename'] . '.php';
			$module['shared']['name']       = $module['shared']['filename'];

			// Tries to load the site level module
			if (file_exists($module['requested']['php_file'])) {
				require_once $module['requested']['php_file'];

				if (class_exists($module['requested']['class_name'])) {
					$module['object'] = new $module['requested']['class_name']($config, $db, $mailer, $error);
				}
			}
			// Tries to load the shared module
			else if (file_exists($module['shared']['php_file']) && $module['shared']['name'] != false) {
				require_once $module['shared']['php_file'];

				if (class_exists($module['shared']['class_name'])) {
					$module['object'] = new $module['shared']['class_name']($config, $db, $mailer, $error);
				}
			}
			// Loads the stock module
			else {
				$module['object'] = new Module($config, $db, $mailer, $error);
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

				// If the loaded module has a name, use it to override
				if ($module['object']->name != null && $module['requested']['filename'] != $module['object']->name) {
					$module['requested']['filename'] = $module['object']->name;
					$module['requested']['name']     = $module['object']->name;
				}

				if ($module['object']->template != null) {
					$module['requested']['filename'] = $module['object']->template;
				}

				// Sets the display's properties
				$display->module_name            = $module['requested']['name'];
				$display->module_filename        = $module['requested']['filename'];
				$display->shared_module_name     = $module['shared']['name'];
				$display->shared_module_filename = $module['shared']['filename'];

				// Loads the module data into the display to be rendered
				/**
				 * @todo perhaps make this a passed variable
				 */
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
}

?>
