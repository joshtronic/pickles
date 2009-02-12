<?php

/**
 * Removes an item from the cart
 *
 * Removes the passed item from the cart completely
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2008 Joshua Sherman
 */

class store_cart_remove extends store {

	/**
	 * @todo Add handling for an invalid product
	 */
	public function __default() {

		if ($this->db->getField('SELECT COUNT(id) FROM products WHERE id = "' . $_REQUEST['id'] . '";') != 1) {
			
		}
		else {
			// Unsets the product from the cart
			unset($_SESSION['cart']['products'][$_REQUEST['id']]);

			if (count($_SESSION['cart']['products']) == 0) {
				unset($_SESSION['cart']['products']);
			}

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
			$cart['subtotal'] = $subtotal;
			$cart['shipping'] = $shipping;
			unset($cart);

			// Redirect to the cart
			header('Location: /store/cart');
			exit();
		}
	}

}

?>
