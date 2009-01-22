<?php

class store_product extends store {
	
	protected $display = DISPLAY_SMARTY;

	public function __default() {

		$this->product = $this->db->getArray("
			SELECT *
			FROM products
			WHERE product_id = '{$_REQUEST['id']}';
		");
	}
}

?>
