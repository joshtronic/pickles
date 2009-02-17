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
		}

		// Adds the billing information into the database
		if ($_REQUEST['billing_same_as_shipping'] == 'on') {
			$billing_address_id = $shipping_address_id;
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
			// Once the user is customer and their addresses are added, perform the authenticate.net logic
			$debugging     = 1; // Display additional information to track down problems
			$testing	   = 1; // Set the testing flag so that transactions are not live
			$error_retries = 2; // Number of transactions to post if soft errors occur

			$auth_net_login_id = "CHANGE THIS";
			$auth_net_tran_key = "CHANGE THIS";
			$auth_net_url      = "https://test.authorize.net/gateway/transact.dll";
			// Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
			// $auth_net_url      = "https://secure.authorize.net/gateway/transact.dll";

			$authnet_values = array(
				'x_login'            => $auth_net_login_id,
				'x_version'          => '3.1',
				'x_delim_char'       => '|',
				'x_delim_data'       => 'TRUE',
				'x_type'             => 'AUTH_CAPTURE',
				'x_method'           => 'CC',
				'x_tran_key'         => $auth_net_tran_key,
				'x_relay_response'   => 'FALSE',
				'x_card_num'         => '4242424242424242',
				'x_exp_date'         => '1209',
				'x_description'      => 'Recycled Toner Cartridges',
				'x_amount'           => '12.23',
				'x_first_name'       => 'Charles D.',
				'x_last_name'        => 'Gaulle',
				'x_address'          => '342 N. Main Street #150',
				'x_city'             => 'Ft. Worth',
				'x_state'            => 'TX',
				'x_zip'              => '12345',
				'CustomerBirthMonth' => 'Customer Birth Month: 12',
				'CustomerBirthDay'   => 'Customer Birth Day: 1',
				'CustomerBirthYear'  => 'Customer Birth Year: 1959',
				'SpecialCode'        => 'Promotion: Spring Sale',
			);

			$fields = '';
			foreach ($authnet_values as $key => $value) {
				$fields .= "{$key}=" . urlencode($value) . '&';
			}

			// Post the transaction to Authorize.net
			$ch = curl_init("https://test.authorize.net/gateway/transact.dll"); 
			// Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
			// $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
			curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
			$resp = curl_exec($ch); //execute post and get results
			curl_close ($ch);

			echo $resp;
		}
	}
}

?>
