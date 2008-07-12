<?php

class Controller extends Object {

	private $model  = null;
	private $viewer = null;

	private $session = null;

	public function __construct($controller) {
		parent::__construct();
	

		// Establish the session
		if ($controller != 'CLI') {
			$this->session = Session::getInstance();
		}
		
		// Grab the passed in model or use the default
		$name = isset($_REQUEST['model']) ? $_REQUEST['model'] : $this->config->get('navigation', 'default');

		// Load the model
		$file = '../models/' . $name . '.php';
		if (file_exists($file)) {
			require_once $file;

			if (strpos($name, '/') === false) {
				$class   = $name;
				$section = $name;
				$event  = null;
			}
			else {
				$class = str_replace('/', '_', $name);
				list($section, $event) = split('/', $name);
			}

			if (class_exists($class)) {
				$this->model = new $class;

				if ($this->model->get('auth') === true) {
					Security::authenticate();
				}

				$this->model->set('name',    $name);
				$this->model->set('section', $section);
				$this->model->set('event',   $event);

				$this->model->__default();
			}
			else {
				// @todo
				exit();
			}

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

		// Add the admin section if we're authenticated
		if (isset($_SESSION['user_id']) || isset($_SESSION['artist_id'])) {
			if (Config::get('menu', 'admin') == 'true') {
				$navigation['admin'] = 'Admin';
			}
	}

	*/

}

?>
