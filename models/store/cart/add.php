<?php

/**
 * Adds an item to the cart
 *
 * Adds the passed item to the cart with a quantity of 1.
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 */

class store_cart_add extends store {

	/**
	 * @todo Add handling for an invalid product
	 */
	public function __default() {
		if ($this->db->getField('SELECT COUNT(id) FROM products WHERE id = "' . $_REQUEST['id'] . '";') != 1) {
			
		}
		else {
			// References the product in the cart
			$product =& $_SESSION['cart']['products'][$_REQUEST['id']];

			// If the data is not set then grab it and set it
			if (!isset($product['name'], $product['sku'], $product['price'])) {
				$data = $this->db->getRow('SELECT name, sku, price FROM products WHERE id ="' . $_REQUEST['id'] . '";');

				$product['name']  = $data['name'];
				$product['sku']   = $data['sku'];
				$product['price'] = $data['price'];
			}

			// Increment the quantity and update the total
			$product['quantity']++;
			$product['total'] = number_format($product['price'] * $product['quantity'], 2);
			unset($product);

			// References the cart as a whole
			$cart     =& $_SESSION['cart'];
			$subtotal =  0;

			// Loops through the products and totals them up
			if (is_array($cart['products'])) {
				foreach ($cart['products'] as $product) {
					$subtotal += $product['total'];
				}
			}

			// Set the subtotal in the cart
			$cart['subtotal'] = $subtotal;
			unset($cart);

			// Redirect to the cart
			header('Location: /store/cart');
			exit();
		}
	}

}

?>
