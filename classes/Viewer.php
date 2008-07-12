<?php

class Viewer {

	private function __construct() { }

	public static function factory(Model $model) {
		$class = 'Viewer_' . $model->getView();
		return new $class($model);
	}

}

?>
