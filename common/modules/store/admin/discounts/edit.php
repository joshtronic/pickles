<?php

class store_admin_discounts_edit extends store_admin {

	protected $display = array(DISPLAY_SMARTY, DISPLAY_JSON);

	public function __default() {
		
		$sql = '
			SELECT c.id, CONCAT(a.last_name, ", ", a.first_name) AS name
			FROM customers AS c
			INNER JOIN addresses AS a
			ON c.shipping_address_id = a.id;
		';
		$this->setPublic('customers',  $this->flattenArray($this->db->getArray($sql)));
		$this->setPublic('categories', $this->flattenArray($this->db->getArray('SELECT id, name FROM categories;')));
		$this->setPublic('products',   $this->flattenArray($this->db->getArray('SELECT id, name FROM products;')));

		$this->setPublic('applied_to_options',  array('ORDER' => 'Order', 'PRODUCT' => 'Product', 'SHIPPING' => 'Shipping'));
		$this->setPublic('amount_type_options', array('FLAT' => 'Flat $ Amount', 'PERCENT' => '% of Applied To' ));

		if (isset($_REQUEST['id'])) {
			$discount = $this->db->getRow('SELECT * FROM discounts WHERE id = "' . $_REQUEST['id'] . '" ORDER BY sequence DESC LIMIT 1;');
			$this->setPublic('discount', $discount);

			$sql = '
				SELECT * 
				FROM discount_rules
				WHERE discount_id = "' . $discount['id'] . '"
				AND sequence = "' . $discount['sequence'] . '"
				ORDER BY id;
			';
			$this->setPublic('rules', $this->db->getArray($sql));
			
			$sql = '
				SELECT *
				FROM discount_xref
				WHERE discount_id = "' . $discount['id'] . '"
				AND sequence = "' . $discount['sequence'] . '"
				ORDER BY id;
			';
			$xrefs = $this->db->getArray($sql);

			$xrefs_grouped = array('CUSTOMER' => array(), 'CATEGORY' => array(), 'PRODUCT' => array());

			if (is_array($xrefs)) {
				foreach ($xrefs as $xref) {
					// @todo There's currently no code to handle exclusions
					if ($xref['eligible'] == 'Y') {
						$xrefs_grouped[$xref['type']][] = $xref['xref_id'];
					}
				}
			}

			$this->setPublic('xrefs', $xrefs_grouped);
		}
	}

	private function flattenArray($array) {
		$formatted_array = array();

		foreach ($array as $temp) {
			$formatted_array[$temp['id']] = $temp['name'];
		}

		return $formatted_array;
	}
}

?>
