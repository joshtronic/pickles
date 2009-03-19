<?php

/**
 * Store cart view
 *
 * Displays the contents of the shopping cart and gives the user the
 * ability to update quantities, remove items, apply discount codes and
 * proceed to the checkout.
 *
 * @package    PICKLES
 * @subpackage store
 * @author     Joshua Sherman <josh@phpwithpickles.org>
 * @copyright  2007-2008 Joshua Sherman
 */

class store_cart extends store {

	protected $display = DISPLAY_SMARTY;

	public function __default() {

		if (isset($_SESSION['cart'])) {
			$this->setPublic('cart', $_SESSION['cart']);
		}

		$discounts = null;
		
		if (isset($_SESSION['cart']['discounts']) && is_array($_SESSION['cart']['discounts'])) {

			foreach ($_SESSION['cart']['discounts'] as $code => $discount) {

				if (isset($discount['rules']) && is_array($discount['rules'])) {

					foreach ($discount['rules'] as $rule) {

						switch ($rule['applied_to']) {
							case 'ORDER':
								// Takes the discount from the subtotal
								break;

							case 'PRODUCT':
								// Takes the discount from the product
								if (isset($discount['xref']) && is_array($discount['xref'])) {
									foreach ($discount['xref'] as $xref) {
										switch ($xref['type']) {
											case 'CATEGORY':
												break;
												
											case 'CUSTOMER':
												break;
												
											case 'PRODUCT':
												// Checks if the product referenced is in the cart
												if (array_key_exists($xref['xref_id'], $_SESSION['cart']['products'])) {
													$quantity = $_SESSION['cart']['products'][$xref['xref_id']]['quantity'];

													$total = $_SESSION['cart']['products'][$xref['xref_id']]['total'];
													$price = $_SESSION['cart']['products'][$xref['xref_id']]['price'];

													switch ($rule['amount_type']) {
														case 'FLAT':
															break;

														case 'PERCENT':
															$discounts[$xref['xref_id']]['price'] = round($price * ($rule['amount'] * 0.01), 2);
															$discounts[$xref['xref_id']]['total'] = $discounts[$xref['xref_id']]['price'] * $quantity;
															break;
													}
													var_dump($discounts);
												}
												break;
										}
									}
								}
								break;

							case 'SHIPPING':
								// Takes the discount from the shipping
								break;
						}
					}
				}
			}
		}

		$this->setPublic('discounts', $discounts);

		//var_dump($_SESSION['cart']);
		//var_dump($_SESSION['cart']['discounts']['inPink']);
	}
}

?>
