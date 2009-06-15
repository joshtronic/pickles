<?php

class post_delete extends Module {

	public function __default() {
		
		if (isset($_REQUEST['id']) && trim($_REQUEST['id']) != '') {
			$this->db->delete('posts', array('post_id' => $_REQUEST['id']));

			header('Location: /');
		}
		else {
			// @todo Throw error
		}
	}
}

?>
