<?php

// @todo Possibly remove the conditionals for the CLI view

class Controller extends Object {

	private $model  = null;
	private $viewer = null;

	private $session = null;
	
	/*
	protected $config = null;
	private $controller = null;
	*/

	public function __construct($site, $controller = 'Web') {

		parent::__construct();

		// Establish the session
		$this->session = Session::getInstance();

		// Load the config for the site passed in
		$this->config = Config::getInstance();
		$this->config->load($site);

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
			$file = '../models/' . $name . '.php';

			if (strpos($name, '/') === false) {
				$class   = $name;
				$section = $name;
				$event  = null;
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

		//var_dump($name, $this->session, $_SESSION, $_SERVER);
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
