<?php

/**
 * JSON viewer
 *
 * Displays data in JavaScript Object Notation
 *
 * @package    PICKLES
 * @subpackage Viewer
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 * @link       http://json.org/
 */
class Viewer_JSON extends Viewer_Common {

	/**
	 * Displays the data in JSON format
	 */
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
