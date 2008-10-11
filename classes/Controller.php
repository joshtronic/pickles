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
 * configuration loaded.  Loads models, serves up user authentication when
 * the model asks for it, and loads the viewer that the model has requested.
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
	 * @param object Config object
	 */
	public function __construct(Config $config) {
		parent::__construct($config);

		$logger = new Logger();
		$error  = new Error($config, $logger);
		$db     = new DB($config, $error);
		$mailer = new Mailer($config, $error);

		// Generate a generic "site down" message
		if ($config->getDisabled()) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		// Grab the passed in model or use the default
		$model_name = isset($_REQUEST['model']) ? str_replace('-', '_', $_REQUEST['model']) : $config->getDefaultModel();

		/**
		 * @todo Maybe the logout shouldn't be an internal thing, what if the
		 *       user wanted to call the logout page something else? or better
		 *       yet, they want to next it, like /users/logout?
		 */
		if ($model_name == 'logout') {
			$security = new Security($config, $db);
			$security->logout();
		}
		else {
			// Loads the requested model's information
			$model_file = '../models/' . $model_name . '.php';

			/**
			 * @todo Rename "section" to something like "current" or "selected"
			 * @todo Figure out WTF "event" is being used for
			 */
			if (strpos($model_name, '/') === false) {
				$class   = $model_name;
				$section = $model_name;
				$event   = null;
			}
			else {
				$class = str_replace('/', '_', $model_name);
				list($section, $event) = split('/', $model_name);
			}

			// Establishes the shared model information
			$shared_model_name = $config->getSharedModel($model_name);
			$shared_model_file = PICKLES_PATH . 'models/' . $shared_model_name . '.php';

			// Tries to load the site level model
			if (file_exists($model_file)) {
				require_once $model_file;

				if (class_exists($class)) {
					$model = new $class($config, $db, $mailer);
				}
			}
			// Tries to load the shared model
			else if (file_exists($shared_model_file) && $shared_model_name != false) {
				if (strpos($shared_model_name, '/') === false) {
					$class = $shared_model_name;
				}
				else {
					$class = str_replace('/', '_', $shared_model_name);
				}

				if (class_exists($class)) {
					$model = new $class($config, $db, $mailer);
				}
			}
			// Loads the stock model
			else {
				$model = new Model($config, $db, $mailer);
			}

			// Checks if we loaded a model file and no class was present
			if ($model != null) {

				// Potentially starts the session if it's not started already
				if ($model->getSession() === true) {
					if (ini_get('session.auto_start') == 0) {
						session_start();
					}
				}

				// Potentially requests use authentication
				if ($model->getAuthentication() === true) {
					if (!isset($security)) {
						$security = new Security($config, $db);
					}
					$security->authenticate();
				}

				// Potentially executes the model's logic
				if (method_exists($model, '__default')) {
					$model->__default();

					if (isset($mailer->message)) {
						$status = $mailer->send();
						$model->type    = $status['type'];
						$model->message = $status['message'];
					}
				}

				// Creates a new viewer object
				$viewer_class = 'Viewer_' . $model->getViewer();
				$viewer = new $viewer_class($config, null);

				// Sets the viewers properties
				$viewer->model_name  = $model_name;
				$viewer->shared_name = $shared_model_name;
				$viewer->section     = $section;
				$viewer->data        = $model->getData();

				// Runs the requested viewer's display function
				$viewer->display();
			}
		}
	}
}

?>
