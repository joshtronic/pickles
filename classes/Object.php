<?php

class Object {

	protected $config = null;

	public function __construct() {
		$this->config = Config::getInstance();
	}

	public function __destruct() {
	
	}

	/*
	// @todo maybe later
	public function __get($variable) {
		if (!isset($this->data[$variable])) {
			$this->data[$variable] = null;
		}

		return $this->data[$variable];
	}
	*/

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
		$this->$variable = $value;
	}

}

?>
