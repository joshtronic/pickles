<?php

class Config extends Singleton {

	private static $instance;

	public $timestamp = null;

	private function __construct() { }

	public static function getInstance() {
		$session = Session::getInstance();
		
		$class = __CLASS__;

		if (isset($session->$class)) {
			self::$instance = Singleton::thaw($class);
		}
		else if (!self::$instance instanceof $class) {
			self::$instance = new $class();
		}

		return self::$instance;
	}

	public function load($file) {
		$load = true;
		if (isset($this->file)) {
			if (file_exists($this->file) && isset($this->timestamp)) {
				if (filemtime($this->file) < $this->timestamp) {
					$load = false;
				}
			}
		}
		
		if ($load) {
			if (file_exists($file)) {
				$this->file = $file;

				$config_array = ArrayUtils::object2array(simplexml_load_file($file));

				if (is_array($config_array)) {
					foreach ($config_array as $variable => $value) {
						if ($value == 'true' || $value == array()) {
							$value = (bool) $value;
						}

						$this->$variable = $value == array() ? (bool) $value : $value;
					}
				}

				$this->freeze();

				return true;
			}
			else {
				Error::addError('Unable to load the configuration file');
				return false;
			}
		}
	}

}

?>
