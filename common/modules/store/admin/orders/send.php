<?php

class store_admin_orders_send extends store_admin {

	protected $display = DISPLAY_JSON;

	public function __default() {

		$this->send();
	}

	public function send($status_id = null, $status = null, $shipping_id = null, $shipping_method = null, $shipping_note = null) {

		// Unserializes the order so we can use it
		$order = unserialize(urldecode($_REQUEST['order']));

		if (!isset($status_id, $status, $shipping_id, $shipping_method)) {

			// Breaks apart the status into the ID and text
			list($status_id, $status) = split('\|', $order['status_id']);
			
			// Breaks apart the shipping method into the ID and text
			list($shipping_id, $shipping_method) = split('\|', $order['shipping_method']);

			$shipping_note = $order['shipping_note'];
		}

		// Grabs the date
		$date = date('m/d/Y');

		// Builds the message
		$message = "
{$this->config->store->title}
-------------------------------------------------------------------

Dear {$order['billing_first_name']} {$order['billing_last_name']},

This is an automatic email to let you know that your order was marked as {$status} on: {$date}

Order Number: {$_REQUEST['id']}";

		if ($status_id == 4) {
			$message .= "
Completion Date: {$date}
Ship Via: {$shipping_method}
Tracking #: {$_REQUEST['tracking_number']}

Shipped to:
---------------------------";

			if (trim($order['shipping_company']) != '') {
				$message .= "
{$order['shipping_company']}";
			}

			$message .= "
{$order['shipping_first_name']} {$order['shipping_last_name']}
{$order['shipping_address1']}";

			if (trim($order['shipping_address2']) != '') {
				$message .= "
{$order['shipping_address2']}";
			}

			$message .= "
{$order['shipping_city']}, {$order['shipping_state']} {$order['shipping_zip_code']}";
		}

		$message .= "

Order Summary
---------------------------
";

		$total_items = 0;

		// Loops through products
		foreach ($order['products'] as $product) {
			$message .= "
{$product['quantity']} - [{$product['sku']}] {$product['name']} {$product['description']} @ \${$product['price']} each";

			$total_items += $product['quantity'];
		}

		$message .= "

--
{$total_items}: Total Items";

		if ($status_id == 4) {
			$message .= "

According to our records, this order is now complete.";
		}

		if (trim($_REQUEST['shipping_note']) != '') {
			$message .= "

Additional Notes
---------------------------
{$shipping_note}";
		}

		$message .= "

------------------

Thank you for your interest in {$this->config->store->title}

{$this->config->store->title}
Phone: {$this->config->store->phone}
Fax:   {$this->config->store->fax}
URL:   {$this->config->store->url}
";

		mail($_REQUEST['email'], $this->config->store-title . ' - Order #' . $_REQUEST['id'] . ' - ' . $status, $affiliate_message, 'From: ' . $this->config->store->return_email);
		//mail('josh.sherman@gmail.com, dekin@ribbonnutrition.com', $this->config->store->title . ' - Order #' . $_REQUEST['id'] . ' - ' . $status, $message, 'From: ' . $this->config->store->return_email);

		$this->packing_slip = $message;

		$this->setPublic('status',  'Success');
		$this->setPublic('message', 'The latest update has been successfully resent to the customer.');
	}
}

?>
