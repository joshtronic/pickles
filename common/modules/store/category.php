<?php

class store_category extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __default() {
		$category = $this->db->getRow('
			SELECT id, name, permalink, description
			FROM categories
			WHERE permalink = "' . $_REQUEST['permalink'] . '";
		');

		$this->category = $category;
	}
}

?>
