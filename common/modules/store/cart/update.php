<?php

/**
 * Updates the cart
 *
 * Updates the submitted data for the cart
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2008 Joshua Sherman
 */

class store_cart_update extends store {

	/**
	 * @todo Add handling for an invalid product
	 */
	public function __default() {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {	

			if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {

				// Updates the quantities
				foreach ($_POST['quantity'] as $id => $quantity) {
					// References the product in the cart
					$product = $_SESSION['cart']['products'][$id];

					if ($quantity <= 0) {
						unset($_SESSION['cart']['products'][$id]);
					}
					else {
						if ($product['limit_per_customer'] != 0 && $quantity > $product['limit_per_customer']) {
							$quantity = $product['limit_per_customer'];
						}

						// The ceil() is to force up any fractions
						$product['quantity'] = ceil($quantity);
						$product['total']    = round($product['price'] * $product['quantity'], 2);
						$_SESSION['cart']['products'][$id] = $product;
					}
				}

				if (count($_SESSION['cart']['products']) <= 0) {
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
				$cart['subtotal'] = round($subtotal, 2);
				$cart['shipping'] = round($shipping, 2);
				unset($cart);
			}
		}

		// Redirect to the cart
		header('Location: /store/cart');
		exit();
	}

}

?>
