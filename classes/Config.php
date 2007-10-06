<?php

class Config {

	private static $data;

	public static function load($site) {
		// @todo no hardcoded paths!
		$file = "/home/websites/111110/common/config/{$site}.ini";
		if (file_exists($file)) {
			self::$data =& parse_ini_file($file, true);
		}
		else {
			Error::addError('Unable to load the configuration file');
			return false;
		}

		return true;		
	}

	public static function get($variable, $section = null) {
		return isset($section) ? self::$data[$section][$variable] : self::$data[$variable];
	}

	public static function enableDebug() {
		self::$data['debug'] = true;
	}

	public static function disableDebug() {
		self::$data['debug'] = false;
	}

	public static function getDebug() {
		return self::get('debug');
	}

}

?>
