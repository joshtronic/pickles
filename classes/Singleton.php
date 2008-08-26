<?php

class Singleton {

	private function __construct() { }

	public function get($variable, $array_element = null) {
		if (isset($this->$variable)) {
			if (isset($array_element)) {
				$array = $this->$variable;

				if (isset($array[$array_element])) {
					return $array[$array_element];
				}
			}
			else {
				return $this->$variable;
			}
		}

		return false;
	}

	public function set($variable, $value) {
		$this->$$variable = $value;
	}

	public function freeze() {
		$session = Session::getInstance();
		$this->timestamp = time();
		$class = get_class($this);
		$session->$class = serialize($this);
	}

	public static function thaw($class) {
		__autoload($class);

		$session = Session::getInstance();
		return unserialize($session->$class);
	}

}

?>
