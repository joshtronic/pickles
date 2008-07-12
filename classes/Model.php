<?php

class Model extends Object {

	protected $db   = null;
	protected $data = array();
	protected $name = null;

	public function __construct() {
		parent::__construct();

		$this->db = DB::getInstance();
	}

	public function getAuth() {
		return $this->get('auth');
	}

	public function getData() {
		return $this->get('data');
	}

	public function getView() {
		return $this->get('view');
	}

	public function __destruct() {
		parent::__destruct();
	}

	public function __default() {

	}

}

?>
