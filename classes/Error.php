<?php

class Error {

	private static $errors;
	private static $warnings;

	public static function instance() {
		static $object;

		if (!is_object($object)) {
			$object = new Error();
		}

		return $object;
	}

	public static function addError($message) {
		self::$errors[] = $message;
		return true;
	}

	public static function addWarning($message) {
		self::$warnings[] = $message;
		return true;
	}

	public static function getError() {
		return self::$errors;
	}

	public static function getWarning() {
		return self::$warnings;
	}

	public static function isError() {
		if (is_array(self::getError()) || is_array(self::getWarning())) {
			return true;
		}

		return false;
	}

	public function display() {
		if (self::isError()) {
			if (self::getError()) {
				foreach (self::getError() as $error) {
					echo "{$error}<br />";
				}
			}

			if (self::getWarning()) {
				foreach (self::getWarning() as $error) {
					echo "{$warning}<br />";
				}
			}
			
			self::$errors = self::$warnings = null;
			return true;
		}

		return false;
	}

}

?>
