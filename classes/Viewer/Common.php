<?php

abstract class Viewer_Common extends Object {

	protected $model = null;

	public function __construct(Model $model) {
		parent::__construct();
		$this->model = $model;
	}

	abstract public function display();

}

?>
