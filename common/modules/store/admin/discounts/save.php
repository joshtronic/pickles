<?php

class store_admin_discounts_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {
			
		$discount = array(
			'name'               => $_REQUEST['name'],
			'coupon'             => $_REQUEST['coupon'],
			'description'        => $_REQUEST['description'],
			'all_customers'      => 'Y',
			'all_categories'     => 'N',
			'all_products'       => 'N',
			'combinable'         => 'N',
			'valid_from'         => $_REQUEST['valid_from_Year'] . '-' . $_REQUEST['valid_from_Month'] . '-' . $_REQUEST['valid_from_Day'],
			'valid_through'      => $_REQUEST['valid_through_Year'] . '-' . $_REQUEST['valid_through_Month'] . '-' . $_REQUEST['valid_through_Day'],
			'max_customer_usage' => $_REQUEST['max_customer_usage'], 
			'max_order_usage'    => $_REQUEST['max_order_usage'],
			'usage_count'        => $_REQUEST['usage_count'] == '' ? '0' : $_REQUEST['usage_count'], // @TODO zero is quoted to get around a bug.
			'remaining_usages'   => $_REQUEST['remaining_usages'] == 'unlimited' ? null : $_REQUEST['remaining_usages_count']
		);

		// Updates the existing discount
		if (isset($_REQUEST['id'])) {

			// Checks for changes @todo

			// Increments the sequence number
			$sequence = $this->db->getField('SELECT MAX(sequence) + 1 FROM discounts WHERE id = "' . $_REQUEST['id'] . '";');

			// Inserts row into the discount table
			$discount['id']       = $_REQUEST['id'];
			$discount['sequence'] = $sequence;
			$this->db->insert('discounts', $discount);

			$verb = 'updating';
			$past = 'updated';
		}
		// Adds a brand new discount
		else {
			$discount['id']       = $this->db->insert('discounts', $discount);
			$discount['sequence'] = '0';

			$verb = 'adding';
			$past = 'added';
		}

		// Inserts one or more rows into the discount_rules table
		$discount_rules = array(
			'discount_id'  => $discount['id'],
			'sequence'     => $discount['sequence'],
			'applied_to'   => $_REQUEST['applied_to'],
			'amount'       => $_REQUEST['amount'],
			'amount_type'  => $_REQUEST['amount_type'],
			'min_subtotal' => $_REQUEST['min_subtotal'],
			'min_items'    => $_REQUEST['min_items'],
			'max_discount' => $_REQUEST['max_discount']
		);
		$this->db->insert('discount_rules', $discount_rules);

		/*
		$this->setPublic('status',  'Error');
		$this->setPublic('message', print_r($_REQUEST, true));
		return false;
		*/

		// Inserts one or more rows into the discount_xref table
		foreach ($_REQUEST['products'] as $product_id) {
			$discount_xref = array(
				'discount_id'  => $discount['id'],
				'sequence'     => $discount['sequence'],
				'type'         => 'PRODUCT',
				'xref_id'      => $product_id,
				'eligible'     => 'Y',
				'exclusion'    => 'N'
			);
			$this->db->insert('discount_xref', $discount_xref);
		}
	
		if ($this->error->getErrors()) {
			$this->setPublic('status',  'Error');
			$this->setPublic('message', 'There was an error ' . $verb . ' the discount (' . implode('. ', $this->error->getErrors()) . '.)');
			return false;
		}
		else {
			$this->setPublic('status',  'Success');
			$this->setPublic('message', 'The new discount has been ' . $past . ' successfully.');
		}
	}
}

?>
