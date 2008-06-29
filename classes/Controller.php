<?php

class Controller {

	public function __construct() {
		global $smarty;

		$section = $action = null;

		// Set up the section and action from the _REQUEST values
		if (isset($_REQUEST['section'])) {
			// Check for section.action.php
			if (isset($_REQUEST['action']) && file_exists('../logic/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.php')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// Else check for section.php
			else if (file_exists('../logic/' . $_REQUEST['section'] . '.php')) {
				$section = $_REQUEST['section'];
			}
			// Else check for section.action.tpl
			else if (isset($_REQUEST['action']) && file_exists('../templates/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.tpl')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// Else check for section.tpl
			else if (file_exists('../templates/' . $_REQUEST['section'] . '.tpl')) {
				$section = $_REQUEST['section'];
			}
		}

		// Determine if we're on an admin page
		$is_admin = preg_match('/^admin\./', $section);

		// Check that the user is authenticated
		if ($is_admin && !isset($_SESSION['user_id'])) {
			$section = 'admin';
			$action  = null;
		}

		// If we've come this far without a section, use the default
		if (!isset($section)) {
			$section = Config::get('default');
		}

		// Check that the logic script exists and if so, load it
		$file = '../logic/' . $section . ($action ? '.' . $action : null) . '.php';
		if (file_exists($file)) {
			require_once $file; 
		}

		// Check if we're accessing an admin sub section and load the logic script
		if ($section != 'admin' && $is_admin) {
			$template = $section . '.tpl';

			$file = '../logic/' . $section . '.php';

			if (file_exists($file)) {
				require_once $file;
			}

			$section = 'admin';
		}
		// Else, just define the template
		else {
			$template = $section . ($action ? '.' . $action : null) . '.tpl';
		}

		// Load the main navigation from the config
		$navigation = Config::get('navigation');

		// Add the admin section if we're authenticated
		if (isset($_SESSION['user_id'])) {
			$navigation['admin'] = 'Admin';

			if ($section == 'admin') {
				$smarty->assign('admin', Config::get('admin'));
			}
		}

		// Pass all of our controller values to Smarty
		$smarty->assign('navigation', $navigation);
		$smarty->assign('section',    $section);
		$smarty->assign('action',     $action);
		$smarty->assign('template',   $template);

		if (isset($_SESSION)) {
			$smarty->assign('session', $_SESSION);
		}

		// Load it up!
		header('Content-type: text/html; charset=UTF-8');
		// @todo
		$smarty->display(isset($_REQUEST['ajax']) ? '/var/www/josh/common/smarty/templates/ajax.tpl' : 'index.tpl');
	}

	private function authenticate() {
		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$from = '
				FROM user
				WHERE email = "' . $_SERVER['PHP_AUTH_USER'] . '"
				AND password = "' . md5($_SERVER['PHP_AUTH_PW']) . '"
				AND admin = 1;
			';

			DB::execute('SELECT COUNT(id) ' . $from);
			if (DB::getField() != 0) {
				DB::execute('SELECT id ' . $from);
				$_SESSION['user_id'] = DB::getField();
			}
			else {
				$_SESSION['user_id'] = null;
			}
		}

		if (!isset($_SESSION['user_id'])) {
			header('WWW-Authenticate: Basic realm="Site Admin"');
			header('HTTP/1.0 401 Unauthorized');
			exit('No shirt, no shoes, no salvation. Access denied.');
		}
		else {
			header('Location: /');
			exit();
		}
	}

}

?>
