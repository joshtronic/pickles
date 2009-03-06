<?php

/**
 * Adds an item to the cart
 *
 * Adds the passed item to the cart with a quantity of 1.
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007, 2008, 2009 Joshua Sherman
 */

class store_cart_add extends store {

	public function __default() {

		if ($this->db->getField('SELECT COUNT(id) FROM products WHERE id = "' . $_REQUEST['id'] . '";') != 1) {
			/**
			 * @todo Add handling for an invalid product
			 */	
		}
		else {
			// References the product in the cart
			$product =& $_SESSION['cart']['products'][$_REQUEST['id']];

			// If the data is not set then grab it and set it
			if (!isset($product['name'], $product['sku'], $product['price'])) {
				$data = $this->db->getRow('
					SELECT sku, name, description, price, limit_per_customer
					FROM products
					WHERE id ="' . $_REQUEST['id'] . '";
				');

				$product['sku']                = $data['sku'];
				$product['name']               = $data['name'] . ' ' . $data['description'];
				$product['description']        = $data['description'];
				$product['price']              = $data['price'];
				$product['limit_per_customer'] = $data['limit_per_customer'];

				$product['discounts'] = $this->db->getArray('
					SELECT discounts.* , discount_rules.*
					FROM discounts
					INNER JOIN discount_xref  ON discounts.id = discount_xref.discount_id
					INNER JOIN discount_rules ON discounts.id = discount_rules.discount_id
					WHERE discount_xref.xref_id = "' . $_REQUEST['id'] . '"
					  AND discounts.disabled = "N";
				');

				// @todo Should do a look up on the shipping table
				// @todo Not sure how we want to handle flat rate shipping
				$product['shipping'] = 4.99;

				if (is_array($product['discounts'])) {
					foreach ($product['discounts'] as $discount) {

						switch ($discount['applied_to']) {

							case 'SHIPPING':
								switch ($discount['amount_type']) {
									case 'FLAT':
										if ($product['shipping'] < $discount['amount']) {
											$discount['amount'] = $product['shipping'];
										}

										break;

									case 'PERCENT':
										if ($discount['amount'] > 100) {
											$discount['amount'] = 100;
										}

										$discount['amount'] = $product['shipping'] * ($discount['amount'] / 100);

										break;
								}

								$product['shipping'] -= $discount['amount'];

								break;
						}
					}
				}
			}

			// Increment the quantity and update the total
			// @todo Add per customer limits across all orders.
			if (empty($product['quantity']) || $product['limit_per_customer'] == 0 || $product['quantity'] < $product['limit_per_customer']) {
				if (empty($product['quantity'])) {
					$product['quantity'] = 0;
				}

				$increment            = isset($_REQUEST['quantity']) && preg_match('/^[0-9]+$/', $_REQUEST['quantity']) && trim($_REQUEST['quantity']) != '' ? $_REQUEST['quantity'] : 1;
				$product['quantity'] += $increment;
				$product['total']     = round($product['price'] * $product['quantity'], 2);
			}

			unset($product);

			// References the cart as a whole
			$cart     =& $_SESSION['cart'];
			$subtotal =  0;
			$shipping =  0;

			// Loops through the products and totals them up
			if (is_array($cart['products'])) {
				foreach ($cart['products'] as $product) {
					$subtotal += $product['total'];
					$shipping += $product['shipping'];
				}
			}

			// Set the subtotal in the cart
			$cart['subtotal'] = round($subtotal, 2);
			$cart['shipping'] = round($shipping, 2);
			unset($cart);

			// Redirect to the cart
			header('Location: /store/cart');
			exit();
		}
	}
}

?>
