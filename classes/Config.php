<?php

class Config extends Singleton {

	private static $instance;

	private function __construct() { }

	public static function getInstance() {
		if (!self::$instance instanceof Config) {
			self::$instance = new Config();
		}

		return self::$instance;
	}

	public function load($site) {
		// @todo no hardcoded paths!
		$file = '/var/www/josh/common/config/' . $site . '.xml';

		if (file_exists($file)) {
			$config_array = ArrayUtils::object2array(simplexml_load_file($file));

			if (is_array($config_array)) {
				foreach ($config_array as $variable => $value) {
					if ($value == 'true' || $value == array()) {
						$value = (bool) $value;
					}

					$this->$variable = $value == array() ? (bool) $value : $value;
				}
			}

			return true;
		}
		else {
			Error::addError('Unable to load the configuration file');
			return false;
		}
	}

}

?>
