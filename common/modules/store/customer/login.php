<?php

/**
 * Customer Login
 *
 * Logs an existing customer into the store and loads their previous
 * shopping cart (if they had one).
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2009 Joshua Sherman
 *
 * @todo I'm assuming that the index is working correctly and that only 1 or 0 rows will be returned, I should be better checking this.
 */

class store_customer_login extends store {
	
	protected $display = DISPLAY_JSON;

	public function __default() {
		
		// Checks that the email address is valid
		$sql = "FROM emails WHERE email = '{$_REQUEST['email']}';";
		if ($this->db->getField("SELECT COUNT(id) {$sql}") != 0) {
			// Pulls the email ID
			$email_id = $this->db->getField("SELECT id {$sql}");

			// Checks that the password is valid
			$password = md5($_REQUEST['password']);
			$sql = "FROM customers WHERE email_id = '{$email_id}' AND password = '{$password}';";
			if ($this->db->getField("SELECT COUNT(id) {$sql}") == 0) {
				$this->status  = 'error';
				$this->message = 'Invalid logon credentials, please try again.';
			}
			else {
				// Pulls the customer and address IDs
				$customer = $this->db->getRow("SELECT id, shipping_address_id, billing_address_id {$sql}");

				// Pulls the email
				$email = $this->db->getField("SELECT email FROM emails WHERE id = '{$email_id}';");

				// Pulls the shipping address
				$shipping_address = $this->db->getRow("SELECT * FROM addresses WHERE id = '{$customer['shipping_address_id']}';"); 

				// Pulls or syncs the billing address
				if ($customer['shipping_address_id'] == $customer['billing_address_id']) {
					$billing_address = $shipping_address;
				}
				else {
					$billing_address = $this->db->getRow("SELECT * FROM addresses WHERE id = '{$customer['billing_address_id']}';"); 
				}

				// Adds the customer ID to the session
				$_SESSION['cart']['customer_id']      = $customer['id'];
				$_SESSION['cart']['email']            = $email;
				$_SESSION['cart']['shipping_address'] = $shipping_address;
				$_SESSION['cart']['billing_address']  = $billing_address;

				// Sets up our variables to be returned in the JSON object
				$this->status           = 'success';
				$this->customer_id      = $customer['id'];
				$this->shipping_address = $shipping_address;
				$this->billing_address  = $billing_address;

				$this->billing_same_as_shipping = $shipping_address == $billing_address;
			}
		}
		else {
			$this->status  = 'error';
			$this->message = 'There is no customer account associated with that email address.';
		}
	}
}

?>
