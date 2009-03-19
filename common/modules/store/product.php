<?php

class store_product extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __default() {

		$sql = "
			SELECT *
			FROM products
			WHERE product_id = '{$_REQUEST['id']}';
		";
		$this->setPublic('product', $this->db->getArray($sql));
	}
}

?>
