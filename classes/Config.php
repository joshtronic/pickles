<?php

class Config {

	private static $data;

	private function __construct() { }

	public static function check($site) {
		if (file_exists('/var/www/josh/common/config/' . $site . '.ini')) {
			return true;
		}
		else {
			return false;
		}
	}

	public static function load($site) {
		// @todo no hardcoded paths!
		$file = '/var/www/josh/common/config/' . $site . '.ini';
		if (file_exists($file)) {
			self::$data = parse_ini_file($file, true);
		}
		else {
			Error::addError('Unable to load the configuration file');
			return false;
		}

		return true;		
	}

	public static function get($variable, $section = null) {
		if (isset($section)) {
			if (isset(self::$data[$section][$variable])) {
				return self::$data[$section][$variable];
			}
		}
		
		if (isset(self::$data[$variable])) {
			return self::$data[$variable];
		}

		return false;
	}

	public static function enableDebug()  { self::$data['debug'] = true;  }
	public static function disableDebug() { self::$data['debug'] = false; }
	public static function getDebug()     { return self::get('debug');    }

	public static function getDisable()   { return self::get('disable');   }
	public static function getSession()   { return self::get('session');   }
	public static function getSmarty()    { return self::get('smarty');    }
	public static function getFCKEditor() { return self::get('fckeditor'); }
	public static function getMagpieRSS() { return self::get('magpierss'); }

}

?>
