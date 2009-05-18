<?php

class store_admin_orders_save extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {

		// Breaks apart the status into the ID and text
		list($status_id, $status) = split('\|', $_REQUEST['status']);
		
		// Breaks apart the shipping method into the ID and text
		list($shipping_id, $shipping_method) = split('\|', $_REQUEST['shipping_method']);

		// Update orders.shipping_id, orders.shipping_note, orders.tracking_number
		$this->db->execute('
			UPDATE orders
			SET
				shipping_id     = "' . $shipping_id . '",
				tracking_number = "' . $_REQUEST['tracking_number'] . '"
			WHERE id = "' . $_REQUEST['id'] . '";
		');

		// Insert a record into the order status updates table
		$this->db->execute('
			INSERT INTO order_status_updates (
				order_id, user_id, status_id, note, update_time
			) VALUES (
				"' . $_REQUEST['id'] . '",
				"' . $_SESSION['user_id'] . '",
				"' . $status_id . '",
				"' . $_REQUEST['shipping_note'] . '",
				NOW()
			);
		');

		// Sends the message to the customer
		if ($_REQUEST['email_customer'] == 'on') {
			$sender = new store_admin_orders_send($this->config, $this->db, $this->mailer, $this->error);
			$sender->send($status_id, $status, $shipping_id, $shipping_method, $_REQUEST['shipping_note']);

			$this->packing_slip = $sender->packing_slip;
		}

		$this->setPublic('status',  'Success');
		$this->setPublic('message', 'The order has been updated successfully.');
	}
}

?>
