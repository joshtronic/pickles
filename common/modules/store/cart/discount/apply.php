<?php

/**
 * Applies a discount to the cart
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 */

class store_cart_discount_apply extends store {

	public function __default() {

		$error  = false;
		$coupon = $_POST['coupon'];

		try {
			if (trim($coupon) == '') {
				throw new Exception('is blank');
			}

			// Check if the discount is already in the cart
			if (in_array($coupon, $_SESSION['cart']['discounts'])) {
				throw new Exception('has already been applied');
			}

			// Pulls the discount from the database (null if it's not there)
			$discount = $this->db->getRow("
				SELECT
					id,
					name,
					all_products,
					all_categories,
					all_customers,
					combinable,
					valid_from,
					valid_through,
					max_customer_usage,
					max_order_usage,
					usage_count,
					remaining_usages,
					disabled
				FROM
					discounts
				WHERE
					coupon = '{$coupon}';
			");
			
			if ($discount == null) {
				throw new Exception('was not found.');
			}

			// Check combinability against if another discount is in the session

			// Checks if the discount is being used during the right time
			if (isset($valid_from) || isset($valid_through)) {
				$today = date('Y-m-d');

				if ($today < $valid_from) {
					throw new Exception('is associated with a promotion that has not yet started.');
				}
				else if ($today > $valid_through) {
					throw new Exception('has expired.');
				}
			}

			// @todo
			// check if the customer already used the coupon on a previous order
			// check if the customer already used the coupon this order

			// Checks if the discount still has remaining usages
			if ($discount['remaining_usages'] <= 0) {
				throw new Exception('has no more remaining usages');
			}

			// Checks if the discount is disabled
			if ($discount['disabled'] == 'Y') {
				throw new Exception('is currently disabled');
			}

			// Pulls any associated discount rules
			$discount['rules'] = $this->db->getArray("
				SELECT applied_to, amount, amount_type, min_subtotal, min_items, max_discount
				FROM discount_rules
				WHERE discount_id = '{$discount['id']}';
			");

			// Pulls any associated discount cross-references
			$discount['xref'] = $this->db->getArray("
				SELECT type, xref_id, eligible, exclusion
				FROM discount_xref
				WHERE discount_id = '{$discount['id']}';
			");

			// Adds the discount to the session
			// Calculations aren't done here, they are done on the cart view module
			$_SESSION['cart']['discounts'][$coupon] = $discount;

		}
		catch (Exception $e) {
			// @todo Get the error message to the user.
			var_dump('The specified discount code ' . $e->getMessage());
		}
		
		// Redirect to the cart
		header('Location: /store/cart');
		exit();
	}
}

?>
