<?php

/**
 * Single Entry Controller
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   pickles
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 * @usage     <code>require_once 'pickles.php';</code>;
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
class Controller extends Object {

	private $execute_tests = false;

	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logici
	 * is executed automatically via use of a constructor.
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

		// Loads the default module
		$module['requested']['name'] = $config->getDefaultModule();

		// Attempts to override the default module
		if (isset($_REQUEST['module'])) {
			if (strpos($config->templates->main, $_REQUEST['module']) !== 0) {
				$module['requested']['name'] = $_REQUEST['module'];
			}
		}

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

		// Loads the requested module's information
		$module['requested']['filename']   = strtr($module['requested']['name'], '-', '_');
		$module['requested']['php_file']   = '../modules/' . $module['requested']['filename'] . '.php';
		$module['requested']['class_name'] = strtr($module['requested']['filename'], '/', '_');

		// Establishes the shared module information
		$module['shared']['name']       = $config->getSharedModule($module['requested']['name']);
		$module['shared']['filename']   = strtr($module['shared']['name'], '-', '_');
		$module['shared']['php_file']   = PICKLES_PATH . 'common/modules/' . $module['shared']['filename'] . '.php';
		$module['shared']['class_name'] = strtr($module['shared']['filename'], '/', '_');

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
	
				// Performs a logout if requested
				/**
				 * @todo Maybe the logout shouldn't be an internal thing, what if
				 *       the user wanted to call the logout page something else? or
				 *       better yet, they want to next it, like /users/logout?
				 * @todo May want to make it work from /store/admin/logout and not
				 *       just from /
				 */
				if ($module['requested']['name'] == 'logout') {
					$security = new Security($config, $db);
					$security->logout();
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
