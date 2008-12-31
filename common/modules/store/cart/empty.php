<?php

/**
 * Empties the cart
 *
 * Removes all the items from the cart completely
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2008 Joshua Sherman
 */

class store_cart_empty extends store {

	public function __default() {

		// Unsets the products array if products are there.
		if (isset($_SESSION['cart'])) {
			unset($_SESSION['cart']);
		}

		// Redirect to the cart
		header('Location: /store/cart');
		exit();
	}

}

?>
