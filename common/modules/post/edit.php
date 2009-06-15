<?php

class post_edit extends post_new {

	public function __default() {

		if (isset($_REQUEST['id'])) {
			$this->setPublic('post', $this->db->getRow('SELECT post_id, title, body, posted_at, hidden FROM posts WHERE post_id = "' . $_REQUEST['id'] . '";'));
		}
	}
}

?>
