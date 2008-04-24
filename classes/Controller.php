<?php

class Controller {

	public function __construct() {
		global $smarty;

		$sections = Config::get('sections');
		
		if (
			isset($_REQUEST['section']) && in_array($_REQUEST['section'], array_keys($sections))
			|| file_exists('../logic/' . $_REQUEST['section'] . ($_REQUEST['action'] ? '.' . $_REQUEST['action'] : null) . '.php')
		) {
			$section = $_REQUEST['section'];

			if (isset($_REQUEST['action'])) {
				$action = $_REQUEST['action'];
			}
		}
		else {
			$section = Config::get('default');
			$action  = '';
		}

		$file     = '../logic/' . $section . ($action ? '.' . $action : null) . '.php';
		$template = $section . ($action ? '.' . $action : null) . '.tpl';

		if (file_exists('../logic/' . $file)) {
			require_once $file; 
		}

		/*
		if (!file_exists('../templates/' . $template)) {
			$section = Config::get('default');
			$action  = '';
		
			$file     = '../logic/' . $section . ($action ? '.' . $action : null) . '.php';
			$template = $section . ($action ? '.' . $action : null) . '.tpl';

			if (file_exists('../logic/' . $file)) {
				require_once $file; 
			}

			if (!file_exists('../templates/' . $template)) {
				// This would be considered a critical error
			}
		}
		*/

		$smarty->assign('sections', $sections);
		$smarty->assign('section',  $section);
		$smarty->assign('action',   $action);
		$smarty->assign('template', $template);

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
