<?php

class store_admin_orders_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {
		// Update orders.shipping_id, orders.shipping_note, orders.tracking_number
		$this->db->execute('
			UPDATE orders
			SET
				shipping_id     = "' . $_REQUEST['shipping_method'] . '",
				shipping_note   = "' . $_REQUEST['shipping_note'] . '",
				tracking_number = "' . $_REQUEST['tracking_number'] . '"
			WHERE id = "' . $_REQUEST['id'] . '";
		');

		// Insert a record into the order status updates table
		$this->db->execute('
			INSERT INTO order_status_updates (
				order_id, user_id, status_id, update_time
			) VALUES (
				"' . $_REQUEST['id'] . '",
				"' . $_SESSION['user_id'] . '",
				"' . $_REQUEST['status'] . '",
				NOW()
			);
		');


				/*
				$affiliate_message = "
{$this->config->store->title} Affiliate Program
-------------------------------------------------------------------

Dear {$contact_address['first_name']} {$contact_address['last_name']},

You have been registered

Your custom URL:
---------------------
{$this->config->store->url}/referral/" . md5($affiliate_id) . "

Your commission rate:
---------------------
{$_REQUEST['commission_rate']}%

------------------

Thank you for your interest in the {$this->config->store->title} Affiliate Program.

{$this->config->store->title}
Phone: {$this->config->store->phone}
Fax:   {$this->config->store->fax}
URL:   {$this->config->store->url}
";

				mail($_REQUEST['email'], 'Welcome to the ' . $this->config->store->title . ' Affiliate Program', $affiliate_message, 'From: ' . $this->config->store->return_email);
				*/

		$this->setPublic('status',  'Success');
		$this->setPublic('message', 'The order has been updated successfully.');
	}
}

?>
