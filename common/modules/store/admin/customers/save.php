<?php

class store_admin_customers_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {

		$update_password = false;

		if (isset($_REQUEST['password']) && trim($_REQUEST['password']) != '') {
			if ($_REQUEST['password'] != $_REQUEST['password_verify']) {
				$this->setPublic('status',  'Error');
				$this->setPublic('message', 'The password and verification do not match.');
				return false;
			}
			else {
				$update_password = true;
			}
		}

		// Adds the billing information into the database
		if (isset($_REQUEST['billing_address1']) && trim($_REQUEST['billing_address1']) != '') {
			$billing_address = array(
				'company'    => $_REQUEST['billing_company'],
				'first_name' => $_REQUEST['billing_first_name'],
				'last_name'  => $_REQUEST['billing_last_name'],
				'address1'   => $_REQUEST['billing_address1'],
				'address2'   => $_REQUEST['billing_address2'],
				'city'       => $_REQUEST['billing_city'],
				'state'      => $_REQUEST['billing_state'],
				'zip_code'   => $_REQUEST['billing_zip_code'],
				'country'    => 'US',
				'phone'      => $_REQUEST['billing_phone'],
				'fax'        => $_REQUEST['billing_fax']
			);

			$billing_address['hash'] = md5(implode('', $billing_address));

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$billing_address['hash']}';") == 0) {
				$billing_address_id = $this->db->insert('addresses', $billing_address);
			}
			else {
				$billing_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$billing_address['hash']}';");
				$billing_address_id = $billing_address['id'];
			}
		}

		// Adds the shipping information into the database
		$shipping_address_id = null;

		if (isset($_REQUEST['shipping_same_as_billing']) && $_REQUEST['shipping_same_as_billing'] == 'on') {
			$shipping_address_id = $billing_address_id;
			$shipping_address    = $billing_address;
		}
		else if (isset($_REQUEST['shipping_address1']) && trim($_REQUEST['shipping_address1']) != '') {
			$shipping_address = array(
				'company'    => $_REQUEST['shipping_company'],
				'first_name' => $_REQUEST['shipping_first_name'],
				'last_name'  => $_REQUEST['shipping_last_name'],
				'address1'   => $_REQUEST['shipping_address1'],
				'address2'   => $_REQUEST['shipping_address2'],
				'city'       => $_REQUEST['shipping_city'],
				'state'      => $_REQUEST['shipping_state'],
				'zip_code'   => $_REQUEST['shipping_zip_code'],
				'country'    => 'US',
				'phone'      => $_REQUEST['shipping_phone'],
				'fax'        => $_REQUEST['shipping_fax']
			);

			$shipping_address['hash'] = md5(implode('', $shipping_address));

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$shipping_address['hash']}';") == 0) {
				$shipping_address_id = $this->db->insert('addresses', $shipping_address);
			}
			else {
				$shipping_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$shipping_address['hash']}';");
				$shipping_address_id = $shipping_address['id'];
			}
		}

		// Adds the customer's email into the email database
		if (isset($_REQUEST['email']) && trim($_REQUEST['email']) != '') {
			$email = $_REQUEST['email'];

			if ($this->db->getField("SELECT COUNT(*) FROM emails WHERE email = '{$email}';") == 0) {
				$email_id = $this->db->insert('emails', array('email' => $email));
			}
			else {
				$email_id = $this->db->getField("SELECT id FROM emails WHERE email = '{$email}';");
			}
		}

		// Updates the existing customer
		if (isset($_REQUEST['id'])) {

			$customer = array(
				'email_id'            => $email_id,
				'billing_address_id'  => $billing_address_id,
				'shipping_address_id' => $shipping_address_id
			);
			
			if ($update_password == true) {
				$customer['password'] = md5($_REQUEST['password']);
			}

			$this->db->update('customers', $customer, array('id' => $_REQUEST['id']));
		
			if ($this->error->getErrors()) {
				$this->setPublic('status',  'Error');
				$this->setPublic('message', 'There was an error updating the customer account (' . implode('. ', $this->error->getErrors()) . '.)');
				return false;
			}
			else {
				$this->setPublic('status',  'Success');
				$this->setPublic('message', 'The customer information has been updated successfully.');
			}
		}
		// Adds a brand new affiliate
		else {
			$customer = array(
				'email_id'            => $email_id,
				'password'            => md5($_REQUEST['password']),
				'billing_address_id'  => $billing_address_id,
				'shipping_address_id' => $shipping_address_id,
				'created_at'          => time()
			);

			$customer_id = $this->db->insert('customers', $customer);
		
			if ($this->error->getErrors()) {
				$this->setPublic('status',  'Error');
				$this->setPublic('message', 'There was an error adding the customer account (' . implode('. ', $this->error->getErrors()) . '.)');
				return false;
			}
			else {
				// @todo Leverage sign up code and reuse here
				//mail($_REQUEST['email'], 'Welcome to the ' . $this->config->store->title . ' Affiliate Program', $customer_message, 'From: ' . $this->config->store->return_email);

				$this->setPublic('status',  'Success');
				$this->setPublic('message', 'The new customer has been added successfully.');
			}
		}
	}
}

?>
