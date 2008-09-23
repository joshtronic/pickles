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

		// Start the session if it's not started already
		/**
		 * @todo Need to make the session not so mandatory.
		 */
		if (ini_get('session.auto_start') == 0) {
			session_start();
		}

		// Load the config for the site passed in
		$this->config = Config::getInstance();
		$this->config->load($file);

		// Generate a generic "site down" message
		if ($this->config->get('disabled')) {
			exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
		}

		// Grab the passed in model or use the default
		$name = isset($_REQUEST['model']) ? str_replace('-', '_', $_REQUEST['model']) : $this->config->get('navigation', 'default');

		if ($name == 'logout') {
			Security::logout();		
		}
		else {
			// Load the model
			$file        = '../models/' . $name . '.php';
			$shared_file = '../../pickles/models/' . $name . '.php';

			if (strpos($name, '/') === false) {
				$class   = $name;
				$section = $name;
				$event   = null;
			}
			else {
				$class = str_replace('/', '_', $name);
				list($section, $event) = split('/', $name);
			}

			if (file_exists($file)) {
				require_once $file;

				if (class_exists($class)) {
					$this->model = new $class;
				}
			}
			elseif (file_exists($shared_file)) {
				if (class_exists($class)) {
					$this->model = new $class;
				}
			}
			else {
				$this->model = new Model();
			}

			if ($this->model->get('auth') == false) {
				$this->model->set('auth', $this->config->get('behavior', 'auth'));
			}
			
			if ($this->model->get('view') == false) {
				if ($this->config->get('behavior', 'view') != false) {
					$view = $this->config->get('behavior', 'view');
				}
				else {
					// Perhaps Smarty shouldn't be assumed at this point...
					$view = isset($argv) ? 'CLI' : 'Smarty';
				}

				$this->model->set('view', $view);
			}

			if ($this->model->get('auth') === true && $controller != 'CLI') {
				Security::authenticate();
			}

			$this->model->set('name',    $name);
			$this->model->set('section', $section);
			$this->model->set('event',   $event);

			$this->model->__default();

			// Load the viewer
			$this->viewer = Viewer::factory($this->model);
			$this->viewer->display();
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
