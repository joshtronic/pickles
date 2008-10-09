<?php

class store_category extends store {

	public function __default() {
		$category = $this->db->getRow('
			SELECT id, name, permalink, description
			FROM categories
			WHERE permalink = "' . $_REQUEST['permalink'] . '";
		');

		$this->data['category'] = $category;
	}
}

?>
