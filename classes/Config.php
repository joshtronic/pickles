<?php

class Config {

	private static $data;

	private function __construct() { }

	public static function load($site) {
		// @todo no hardcoded paths!
		$file = "/var/www/josh/common/config/{$site}.ini";
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

	public static function enableDebug()  { self::$data['debug'] = true; }
	public static function disableDebug() { self::$data['debug'] = false; }
	public static function getDebug()     { return self::get('debug'); }

	public static function getDisable()   { return self::get('disable'); }
	public static function getSession()   { return self::get('session'); }
	public static function getSmarty()    { return self::get('smarty');  }
	public static function getFCKEditor() { return self::get('fckeditor'); }

}

?>
