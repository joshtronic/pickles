<?php

class store_category extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __default() {

		$category = $this->db->getRow("
			SELECT id, name, permalink, description
			FROM categories
			WHERE permalink = '{$_REQUEST['permalink']}';
		");

		$this->category = $category;
		$this->products = $this->db->getArray("
			SELECT p.*
			FROM products AS p
			INNER JOIN category_xref as c
			ON p.id = c.product_id
			WHERE c.category_id = '{$category['id']}';
		");
	}
}

?>
