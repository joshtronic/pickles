<?php

class Viewer_JSON extends Viewer_Common {

	public function display() {
        header('Content-type: application/json; charset=utf-8');

        if (!function_exists('json_encode')) {
            echo '{ "type" : "error", "message" : "json_encode() not found" }';
        } else {
            echo json_encode($this->model->getData());
        }

	}

}

?>
