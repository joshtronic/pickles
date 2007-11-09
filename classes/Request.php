<?php

class Request {

    private static $request;

	public static function load() {
        if (is_array($_REQUEST)) {
			foreach ($_REQUEST as $key => $value) {
				self::$request[$key] = $value;
				unset($_REQUEST[$key]);
			}
		}

		return true;
	}

	public static function get($variable) {
		if (isset(self::$request[$variable])) {
			return self::$request[$variable];	
		}

		return false;
	}

}

?>
