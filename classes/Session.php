<?php

class Session extends Singleton {

	private static $instance;

	public $id = null;

	private function __construct() {
		if (ini_get('session.auto_start') == 0) {
			session_start();
		}

		$this->id = session_id();
	}

	public static function getInstance() {
		if (!self::$instance instanceof Session) {
			self::$instance = new Session();
		}

		return self::$instance;
	}

	public function destroy() {
		// foreach ($_SESSION as $variable => $value)
		foreach (array_keys($_SESSION) as $variable) {
			session_unregister($variable);
		}

		session_destroy();
	}

	public function __clone() {
		trigger_error('Clone is not allowed for ' . __CLASS__, E_USER_ERROR);
	}

	public function __get($var) {
		if (!isset($_SESSION[$var])) {
			$_SESSION[$var] = null;
		}

		return $_SESSION[$var];
	}

	function __set($var,$val) {
		return ($_SESSION[$var] = $val);
	}

	public function __isset($var) {
		return isset($_SESSION[$var]) || isset($this->$var);
	}

	public function __destruct() {
		session_write_close();
	}
}

?>
