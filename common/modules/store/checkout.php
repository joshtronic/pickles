<?php

// @todo store the address_id's in the cart instance to allow for an even easier look up of the data, maybe
// @todo Add more error checking, basically against all queries

class store_checkout extends store {

	protected $display = DISPLAY_JSON;

	public function __default() {

		// Required fields
		$required = array(
			'shipping_first_name',
			'shipping_last_name',
			'shipping_email',
			'shipping_phone',
			'shipping_address1',
			'shipping_city',
			'shipping_state',
			'shipping_zip_code',
			'referred_by',
			'other_source',
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
			'billing_address1',
			'billing_city',
			'billing_state',
			'billing_zip_code',
			'cc_type',
			'cc_number',
			'cc_expiration'
		);

		// Double safety in case the Javascript fails
		if (isset($_REQUEST) && is_array($_REQUEST)) {
			foreach ($_REQUEST as $key => $value) {
				if (in_array($key, $required)) {
					$values = is_array($value) ? $value : array($value);

					foreach ($values as $value) {
						if (trim($value) == '') {
							$this->message = 'Error: The ' . strtr($key, '_', ' ') . ' field is required.';
							return false;
						}
					}
				}
			}
		}

		if (isset($_REQUEST['password']) && trim($_REQUEST['password']) != '') {
			if ($_REQUEST['password'] != $_REQUEST['confirm_password']) {
				$this->message = 'Error: The password and confirm password fields must match.';
				return false;
			}
		}

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

		if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$shipping_address['hash']}';") == 0) {
			$shipping_address_id = $this->db->insert('addresses', $shipping_address);
		}
		else {
			$shipping_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$shipping_address['hash']}';");
			$shipping_address_id = $shipping_address['id'];
		}

		// Adds the billing information into the database
		$billing_address_id = null;

		if (isset($_REQUEST['billing_same_as_shipping']) && $_REQUEST['billing_same_as_shipping'] == 'on') {
			$billing_address_id = $shipping_address_id;
			$billing_address    = $shipping_address;
		}
		else if (isset($_REQUEST['billing_address'])) {
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

			if ($this->db->getField("SELECT COUNT(*) FROM addresses WHERE hash = '{$billing_address['hash']}';") == 0) {
				$billing_address_id = $this->db->insert('addresses', $billing_address);
			}
			else {
				$billing_address    = $this->db->getRow("SELECT * FROM addresses WHERE hash = '{$billing_address['hash']}';");
				$billing_address_id = $billing_address['id'];
			}
		}

		// @todo Remove this when I figure out how I want to control certain code inside the common modules
		$this->error->resetErrors();

		$cart =& $_SESSION['cart'];
			
		// Adds the customer's email into the email database
		$email = $_REQUEST['shipping_email'];

		if ($this->db->getField("SELECT COUNT(*) FROM emails WHERE email = '{$email}';") == 0) {
			$email_id = $this->db->insert('emails', array('email' => $email));
		}
		else {
			$email_id = $this->db->getField("SELECT id FROM emails WHERE email = '{$email}';");
		}

		// Adds the customer's reference into the database
		$referrer = strtolower($_REQUEST['referred_by']) == 'other' ? $_REQUEST['other_source'] : $_REQUEST['referred_by'];

		if ($this->db->getField("SELECT COUNT(*) FROM referrers WHERE referrer = '{$referrer}';") == 0) {
			$referrer_id = $this->db->insert('referrers', array('referrer' => $referrer));
		}
		else {
			$referrer_id = $this->db->getField("SELECT id FROM referrers WHERE referrer = '{$referrer}';");
		}

		// If a password exists, try to create a customer account
		if (isset($_REQUEST['password']) && trim($_REQUEST['password']) != '') {
			$customer = array(
				'email_id'            => $email_id,
				'password'            => md5($_REQUEST['password']),
				'billing_address_id'  => $billing_address_id,
				'shipping_address_id' => $shipping_address_id,
				'created_at'          => datE('Y-m-d H:i:s')
			);
		
			if (!isset($cart['customer_id']) || $cart['customer_id'] == 0) {
				// Adds the customer account
				if ($this->db->getField("SELECT COUNT(*) FROM customers WHERE email_id = '{$email_id}';") == 0) {
					$cart['customer_id'] = $this->db->insert('customers', $customer);

					// Contacts the user to advise them of their sign up
					mail($email, 'Welcome to Menopause Solutions', '<< @todo >>');
				}
				else {
					// @todo Change this out for a confirmation box and re-submit
					// $this->status  = 'ExistingCustomer';
					$this->message = 'Error: The email address you supplied is already in use.  There is an existing customer login form on the right-hand side of the page.  If you wish to continue without logging in, please provide a different email address or delete the contents of the password box (this will skip the process of creating a new account).';
					return false;
				}
			}
			// Updates the existing customer account
			else {
				$this->db->execute('customers', $customer, array('id' => $cart['customer_id']));
			}
		}

		if ($this->error->getErrors()) {
			$this->status  = 'Error';
			$this->message = 'There was an error adding the customer account (' . implode('. ', $this->error->getErrors()) . '.)';
			return false;
		}
		else {
			$total_amount =  $cart['subtotal'] + $cart['shipping'];

			if (isset($cart['customer_id'])) {
				$xref_id   = $cart['customer_id'];
				$xref_type = 'CUSTOMER';
			}
			else if (isset($email_id)) {
				$xref_id   = $email_id;
				$xref_type = 'EMAIL';
			}
			else {
				$this->status  = 'Error';
				$this->message = 'There was an internal error.';
				return false;
			}

			// Assembles the order array
			$order = array(
				'xref_id'             => $xref_id,
				'xref_type'           => $xref_type,
				'shipping_address_id' => $shipping_address_id,
				'billing_address_id'  => $billing_address_id,
				'referrer_id'         => $referrer_id,
				'affiliate_id'        => isset($cart['affiliate']) ? $cart['affiliate'] : null,
				'cc_type'             => isset($_REQUEST['cc_type']) ? $_REQUEST['cc_type'] : null,
				'cc_last4'            => isset($_REQUEST['cc_number']) ? substr($_REQUEST['cc_number'], -4) : null,
				'cc_expiration'       => isset($_REQUEST['cc_expiration']) ? '20' . $_REQUEST['cc_expiration']['year'] . '-' . $_REQUEST['cc_expiration']['month'] . '-01' : null,
				'total_amount'        => "{$total_amount}",
				'shipping_amount'     => "{$cart['shipping']}"
			);

			// Inserts the order into the database
			if (!isset($cart['order_id']) || $cart['order_id'] == 0) {
				$cart['order_id'] = $this->db->insert('orders', $order);
			}
			// Updates an existing order
			else {
				$this->db->update('orders', $order, array('id' => $cart['order_id']));

				// Cleans out the order_* tables
				$this->db->execute("DELETE FROM order_products WHERE order_id = '{$cart['order_id']}';");
			}

			// Populates the order_* tables
			foreach ($cart['products'] as $product_id => $product) {
				$order_product = array(
					'order_id'   => $cart['order_id'],
					'product_id' => $product_id,
					'sequence'   => '0',
					'quantity'   => $product['quantity']
				);
				$this->db->insert('order_products', $order_product);
			}

			// Checks if the transaction ID exists for the order, if not, process the order
			if ($this->db->getField("SELECT transaction_id FROM orders WHERE id = '{$cart['order_id']}';") == NULL) {
				if ($total_amount > 0) {
					$gateway = new WebService_AuthorizeNet_AIM($this->config, $this->error);

					// Customer and order information
					$gateway->order_id         = $cart['order_id'];
					$gateway->customer_id      = isset($cart['customer_id']) ? $cart['customer_id'] : 'N/A';
					$gateway->customer_ip      = $_SERVER['REMOTE_ADDR'];

					// Payment information
					$gateway->total_amount     = $total_amount;
					$gateway->shipping         = $cart['shipping'];
					$gateway->card_number      = $_REQUEST['cc_number'];
					$gateway->expiration_month = $_REQUEST['cc_expiration']['month'];
					$gateway->expiration_year  = $_REQUEST['cc_expiration']['year'];

					if (isset($_REQUEST['ccv2'])) {
						$gateway->cvv2 = $_REQUEST['ccv2'];
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
				
					// Shipping information
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

					// Order information
					$gateway->products = $cart['products'];

					/*
					$gateway->tax            = '';
					$gateway->order_number   = '';
					$gateway->session_number = '';
					*/

					$response = $gateway->process();

					// If the transaction was approved, update the order
					if ($response['response_code'] == 'Approved') {
						$this->db->execute("
							UPDATE orders
							SET transaction_id = '{$response['transaction_id']}', time_placed = NOW()
							WHERE id = '{$response['invoice_number']}';
						");

						// Does some clean up to avoid duplicate transactions
						unset($_SESSION['cart']);
					}

					$this->status  = $response['response_code'];
					$this->message = $response['response_reason_text'];
				}
				// Free order (no payment processing necessary)
				else {
					// Email the user a receipt
					// Email the shipping department the request

					$this->status = 'Approved';
						
					$this->db->execute("
						UPDATE orders
						SET	transaction_id = '', time_placed = NOW()
						WHERE id = '{$cart['order_id']}';
					");

					// Does some clean up to avoid duplicate transactions
					unset($_SESSION['cart']);
				}
			}
			else {
				$this->status  = 'Error';
				$this->message = 'A duplicate transaction has been submitted.';
			}

			// Unsets the cart variable
			unset($cart);
		}
	}
}

?>
