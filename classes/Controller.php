<?php

class Controller {

	public function __construct() {
		global $smarty;

		$section = $action = null;

		if (isset($_REQUEST['section'])) {
			// Check for section.action.php
			if (isset($_REQUEST['action']) && file_exists('../logic/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.php')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// else check for section.php
			else if (file_exists('../logic/' . $_REQUEST['section'] . '.php')) {
				$section = $_REQUEST['section'];
			}
			// Check for section.action.tpl
			else if (isset($_REQUEST['action']) && file_exists('../templates/' . $_REQUEST['section'] . '.' . $_REQUEST['action'] . '.tpl')) {
				$section = $_REQUEST['section'];
				$action  = $_REQUEST['action'];
			}
			// else check for section.tpl
			else if (file_exists('../templates/' . $_REQUEST['section'] . '.tpl')) {
				$section = $_REQUEST['section'];
			}
		}

		if (!isset($section)) {
			$section = Config::get('default');
		}

		$file     = '../logic/' . $section . ($action ? '.' . $action : null) . '.php';
		$template = $section . ($action ? '.' . $action : null) . '.tpl';

		if (file_exists('../logic/' . $file)) {
			require_once $file; 
		}

		$smarty->assign('navigation', Config::get('navigation'));
		$smarty->assign('section',    $section);
		$smarty->assign('action',     $action);
		$smarty->assign('template',   $template);

		header('Content-type: text/html; charset=UTF-8');
		$smarty->display('index.tpl');
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
