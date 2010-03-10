<?php

/**
 * PayPal Web Payments Standard (WPS) Web Service Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * PayPal Web Payments Standard (WPS) Web Service
 */
class WebService_PayPal_WPS extends WebService_PayPal_Common {

	private $variables = array(
		'rm'  => 2,         // 2 == POST
		'cmd' => '_xclick', // _xclick-subscriptions
	);

	public function set($variable, $value) {
		$this->variables[$variable] = $value;
	}

	// @todo ENCRYPT FORM VIA PAYPAL ENCRYPTED WEBSITE PAYMENTS
	public function process() {
		
		$form = '
			<form method="post" id="paypalRedirectForm" action="' . $this->url .'">
				<h2>Please wait while you are redirected to PayPal.</h2>
				If you are not redirected to PayPal within 5 seconds...
		';

		// Adds all the variables to the form
		foreach ($this->variables as $variable => $value) {
			$form .= '<input type="hidden" name="' . $variable . '" value="' . $value . '" />' . "\n";
		}

		$form .= '
				<input type="submit" value="Click Here">
			</form>
		';

		return $form;
	}
}

?>
