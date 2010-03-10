<?php

/**
 * Authorize.Net Customer Information Manager (CIM) Web Service Class File for PICKLES
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
 * Authorize.Net Customer Information Manager (CIM) Web Service
 */
class WebService_AuthorizeNet_AIM extends WebService_Common {

	private $loginname      = "YourApiLogin"; // Keep this secure.
	private $transactionkey = "YourTransactionKey"; // Keep this secure.
	private $url            = "apitest.authorize.net";
	private $path           = "/xml/v1/request.api";

	public function createCustomerProfile() { }
	public function createCustomerPaymentProfile() { }
	public function createCustomerShippingAddress() { }
	public function createCustomerProfileTransaction() { }
	public function deleteCustomerProfile() { }
	public function deleteCustomerPaymentProfile() { }
	public function deleteCustomerShippingAddress() { }
	public function getCustomerProfileIds() { }
	public function getCustomerProfile() { }
	public function getCustomerPaymentProfile() { }
	public function getCustomerShippingAddress() { }
	public function updateCustomerProfile() { }
	public function updateCustomerPaymentProfile() { }
	public function updateCustomerShippingAddress() { }
	public function validateCustomerPaymentProfile() { }

	public function process() {
		return $response;
	}
}

?>
