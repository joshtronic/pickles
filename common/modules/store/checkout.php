<?php

class store_checkout extends store {

	public function __default() {

		// Adds the shipping information into the database
		$shipping_address = array(
			'company'    => $_REQUEST['shipping_company'],
			'first_name' => $_REQUEST['shipping_first_name'],
			'last_name'  => $_REQUEST['shipping_last_name'],
			'email'      => $_REQUEST['shipping_email'],
			'phone'      => $_REQUEST['shipping_phone']['npa'] . $_REQUEST['shipping_phone']['nxx'] . $_REQUEST['shipping_phone']['station'],
			'fax'        => $_REQUEST['shipping_fax']['npa'] . $_REQUEST['shipping_fax']['nxx'] . $_REQUEST['shipping_fax']['station'],
			'address1'   => $_REQUEST['shipping_address1'],
			'address2'   => $_REQUEST['shipping_address2'],
			'city'       => $_REQUEST['shipping_city'],
			'state'      => $_REQUEST['shipping_state'],
			'zip_code'   => $_REQUEST['shipping_zip_code'],
			'country'    => 'US'
		);

		$shipping_address['hash'] = md5(implode('', $shipping_address));

		if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$shipping_address['hash']}';")) {
			$shipping_address_id = $this->db->getField("SELECT id FROM addresses WHERE hash = '{$shipping_address['hash']}';");
		}
		else {
			$shipping_address_id = $this->db->insert('addresses', $shipping_address);
			$shipping_address    = $this->db->getRow("SELECT * FROM addresses WHERE address_id = '{$shipping_address_id}';");
		}

		// Adds the billing information into the database
		if (isset($_REQUEST['billing_same_as_shipping']) && $_REQUEST['billing_same_as_shipping'] == 'on') {
			$billing_address_id = $shipping_address_id;
			$billing_address    = $shipping_address;
		}
		else {
			$billing_address = array(
				'company'    => $_REQUEST['billing_company'],
				'first_name' => $_REQUEST['billing_first_name'],
				'last_name'  => $_REQUEST['billing_last_name'],
				'email'      => $_REQUEST['billing_email'],
				'phone'      => $_REQUEST['billing_phone']['npa'] . $_REQUEST['billing_phone']['nxx'] . $_REQUEST['billing_phone']['station'],
				'fax'        => $_REQUEST['billing_fax']['npa'] . $_REQUEST['billing_fax']['nxx'] . $_REQUEST['billing_fax']['station'],
				'address1'   => $_REQUEST['billing_address1'],
				'address2'   => $_REQUEST['billing_address2'],
				'city'       => $_REQUEST['billing_city'],
				'state'      => $_REQUEST['billing_state'],
				'zip_code'   => $_REQUEST['billing_zip_code'],
				'country'    => 'US'
			);

			$billing_address['hash'] = md5(implode('', $billing_address));

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$billing_address['hash']}';")) {
				$billing_address_id = $this->db->getField("SELECT id FROM addresses WHERE hash = '{$billing_address['hash']}';");
			}
			else {
				$billing_address_id = $this->db->insert('addresses', $billing_address);
				$billing_address    = $this->db->getRow("SELECT * FROM addresses WHERE address_id = '{$billing_address_id}';");
			}
		}

		$customer = array(
			'email'               => $_REQUEST['shipping_email'],
			'password'            => md5('changeme'),
			'referred_by'         => $_REQUEST['referred_by'],
			'billing_address_id'  => $billing_address_id,
			'shipping_address_id' => $shipping_address_id,
			'created_at'          => datE('Y-m-d H:i:s')
		);

		// @todo Remove this when I figure out how I want to control certain code inside the common modules
		$this->error->resetErrors();

		$customer_id = $this->db->insert('customers', $customer);

		//if ($this->error->getErrors()) {
		if (false) {
			exit("There was an error - @todo make a more formal error for when the customer account cannot be created");
		}
		else {
			$gateway = new Gateway_AuthorizeNet_AIM($this->config, $this->error);

			$cart         = $_SESSION['cart'];
			$total_amount = $cart['subtotal'] + $cart['shipping'];

			if ($total_amount > 0) {

				// Payment information
				$gateway->total_amount     = $total_amount;
				//$gateway->card_type        = '',
				$gateway->card_number      = $_REQUEST['cc_number'];
				$gateway->expiration_month = $_REQUEST['cc_expiration']['month'];
				$gateway->expiration_year  = $_REQUEST['cc_expiration']['year'];

				if (isset($_REQUEST['ccv2'])) {
					$gateway->cvv2            = $_REQUEST['ccv2'];
				}

				// Billing information
				$gateway->billing_company    = $billing_address['company'];
				$gateway->billing_first_name = $billing_address['first_name'];
				$gateway->billing_last_name  = $billing_address['last_name'];
				$gateway->billing_address1   = $billing_address['address1'];
				$gateway->billing_address2   = $billing_address['address2'];
				$gateway->billing_city       = $billing_address['city'];
				$gateway->billing_state      = $billing_address['state'];
				$gateway->billing_zip_code   = $billing_address['zip_code'];
				$gateway->billing_country    = $billing_address['country'];
				$gateway->billing_email      = $billing_address['email'];
				$gateway->billing_phone      = $billing_address['phone'];
				$gateway->billing_fax        = $billing_address['fax'];
				
				$gateway->shipping_company    = $shipping_address['company'];
				$gateway->shipping_first_name = $shipping_address['first_name'];
				$gateway->shipping_last_name  = $shipping_address['last_name'];
				$gateway->shipping_address1   = $shipping_address['address1'];
				$gateway->shipping_address2   = $shipping_address['address2'];
				$gateway->shipping_city       = $shipping_address['city'];
				$gateway->shipping_state      = $shipping_address['state'];
				$gateway->shipping_zip_code   = $shipping_address['zip_code'];
				$gateway->shipping_country    = $shipping_address['country'];
				$gateway->shipping_email      = $shipping_address['email'];
				$gateway->shipping_phone      = $shipping_address['phone'];
				$gateway->shipping_fax        = $shipping_address['fax'];

				/*
				$gateway->tax            = '';
				$gateway->freight        = '';
				$gateway->order_number   = '';
				$gateway->session_number = '';
				*/

				$gateway->process();
			}
		}
	}
}

?>
