<?php

class store_category extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __default() {

		$category = $this->db->getRow("
			SELECT id, name, permalink, description
			FROM categories
			WHERE permalink = '{$_REQUEST['permalink']}';
		");

		$this->setPublic('category', $category);

		$sql = "
			SELECT p.*
			FROM products AS p
			INNER JOIN category_xref as c
			ON p.id = c.product_id
			WHERE c.category_id = '{$category['id']}';
		";

		$this->setPublic('products', $this->db->getArray($sql));
	}
}

?>
