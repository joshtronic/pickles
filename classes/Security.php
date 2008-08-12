<?php

class Security extends Object {
	
	static function authenticate() {
		$db      = DB::getInstance();
		$session = Session::getInstance();

		if (isset($_SERVER['PHP_AUTH_USER'])) {
			$from = '
				FROM users
				WHERE email = "' . $_SERVER['PHP_AUTH_USER'] . '"
				AND password = "' . md5($_SERVER['PHP_AUTH_PW']) . '"
				AND admin = 1;
			';

			$db->execute('SELECT COUNT(id) ' . $from);
			if ($db->getField() != 0) {
				$db->execute('SELECT id ' . $from);
				$session->user_id = $db->getField();
			}
			else {
				$session->user_id = null;
			}
		}

		if (!isset($session->user_id)) {
			header('WWW-Authenticate: Basic realm="Site Admin"');
			header('HTTP/1.0 401 Unauthorized');
			exit('No shirt, no shoes, no salvation. Access denied.');
		}
		else {
			// Commented out to allow navigation to the page intended
			//header('Location: /');
			//exit();
		}
	}

	static function logout() {
		$session = Session::getInstance();
		$session->destroy();

		unset($_SERVER['PHP_AUTH_USER']);
		unset($_SERVER['PHP_AUTH_PW']);

		header('Location: /');
	}

}

?>
