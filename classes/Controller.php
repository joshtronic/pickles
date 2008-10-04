<?php

/**
 * Controller class
 *
 * The heavy lifter of PICKLES, makes the calls to get the session and
 * configuration loaded.  Loads models, serves up user authentication when the
 * model asks for it, and loads the viewer that the model has requested.  Default
 * values are present to make things easier on the user.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @usage     new Controller(); or new Controller('/path/to/config.xml');
 * @todo      Possibly remove the conditionals for the CLI view
 */
class Controller extends Object {

	/**
	 * Private objects
	 */
	private $model   = null;
	private $viewer  = null;
	
	/*
	protected $config = null;
	private $controller = null;
	*/

	/**
	 * Constructor
	 *
	 * To make life a bit easier when using PICKLES, the Controller logic is
	 * executed automatically via use of a constructor.
	 * 
	 * @params string $file File name of the configuration file to be loaded
	 * @params string $controller Type of controller to create (Web or CLI)
	 * @todo   Need to internally document the process better
	 */
	public function __construct($file = '../config.xml', $controller = 'Web') {
		parent::__construct();

		// Load the config for the site passed in
		$config = Config::getInstance();
		$config->load($file);

		// Generate a generic "site down" message
		if ($config->disabled === true) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		// Grab the passed in model or use the default
		$model_name = isset($_REQUEST['model']) ? str_replace('-', '_', $_REQUEST['model']) : $config->getDefaultModel();

		if ($model_name == 'logout') {
			Security::logout();		
		}
		else {
			// Load the model
			$model_file = '../models/' . $model_name . '.php';

			if (strpos($model_name, '/') === false) {
				$class   = $model_name;
				$section = $model_name;
				$event   = null;
			}
			else {
				$class = str_replace('/', '_', $model_name);
				list($section, $event) = split('/', $model_name);
			}

			$shared_model_name = $config->getSharedModel($model_name);
			$shared_model_file = PICKLES_PATH . 'models/' . $shared_model_name . '.php';

			if (file_exists($model_file)) {
				require_once $model_file;

				if (class_exists($class)) {
					$this->model = new $class;
				}
			}
			else if (file_exists($shared_model_file) && $shared_model_name != false) {
				if (strpos($shared_model_name, '/') === false) {
					$class   = $shared_model_name;
					//$section = $shared_model_name;
					//$event   = null;
				}
				else {
					$class = str_replace('/', '_', $shared_model_name);
					//list($section, $event) = split('/', $shared_model_name);
				}

				if (class_exists($class)) {
					$this->model = new $class;
				}
			}
			else {
				$this->model = new Model();
			}

			if ($this->model != null) {
				// Start the session if it's not started already
				if ($this->model->getSession() === true) {
					if (ini_get('session.auto_start') == 0) {
						session_start();
					}
				}

				if ($this->model->getAuthentication() === true && $controller != 'CLI') {
					Security::authenticate();
				}

				$this->model->set('name',        $model_name);
				$this->model->set('shared_name', $shared_model_name);
				$this->model->set('section',     $section);

				// Execute the model's logic
				if (method_exists($this->model, '__default')) {
					$this->model->__default();
				}

				// Load the viewer
				$this->viewer = Viewer::factory($this->model);
				$this->viewer->display();
			}
		}
	}

	/*

		if ((isset($_REQUEST['section']) && $_REQUEST['section'] == 'admin')) {
		}
		
		// Check if we're accessing an admin sub section and load the logic script
		if (isset($_REQUEST['section']) && $_REQUEST['section'] != 'admin' && $is_admin) {
			if ($_REQUEST['section'] == 'admin.logout') {
				Session::logout();
			}

	*/
}

?>
