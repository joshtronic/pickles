<?php

class Security extends Object {
	
	static function authenticate() {
		$db = DB::getInstance();

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
				$_SESSION['user_id'] = $db->getField();
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
			// Commented out to allow navigation to the page intended
			//header('Location: /');
			//exit();
		}
	}

	static function logout() {
		$_SERVER['PHP_AUTH_USER'] = null;
		$_SESSION['user_id']      = null;
		$_SESSION['artist_id']    = null;
		$_SESSION['admin']        = false;

		session_destroy();

		header('Location: /');
	}

}

?>
