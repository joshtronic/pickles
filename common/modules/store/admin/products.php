<?php

class store_admin_products extends store_admin {

	public function __default() {
		$sql = '
			SELECT DISTINCT id, sku, name, price, in_stock
			FROM products
			ORDER BY sequence DESC;
		';

		$this->setPublic('products', $this->db->getArray($sql));
	}
}

?>
