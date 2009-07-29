<?php

/**
 * Authorize.Net Advanced Integrated Method (AIM) Web Service Class File for PICKLES
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
 * Authorize.Net Advanced Integrated Method (AIM) Web Service
 */
class WebService_AuthorizeNet_AIM extends WebService_Common {

	private $test_url = 'https://test.authorize.net/gateway/transact.dll';
	private $prod_url = 'https://secure.authorize.net/gateway/transact.dll';

	private $test_login           = '7wYB5c6R';
	private $test_transaction_key = '4px54kx6ZZ7489Gq';

	private $response_variables = array(
		'response_code',
		'response_subcode',
		'response_reason_code',
		'response_reason_text',
		'authorization_code',
		'avs_response',
		'transaction_id',
		'invoice_number',
		'description',
		'amount',
		'method',
		'transaction_type',
		'customer_id',
		'first_name',
		'last_name',
		'company',
		'address',
		'city',
		'state',
		'zip_code',
		'country',
		'phone',
		'fax',
		'email_address',
		'ship_to_first_name',
		'ship_to_last_name',
		'ship_to_company',
		'ship_to_address',
		'ship_to_city',
		'ship_to_state',
		'ship_to_zip_code',
		'ship_to_country',
		'tax',
		'duty',
		'feight',
		'tax_exempt',
		'purchase_order_number',
		'md5_hash',
		'card_code_response',
		'cardholder_authentication_verification_response',
		'reserved'
	);

	public function process() {

		// Loads the API keys based on what URL is being loaded
		if (preg_match("/{$this->config->webservices->authorizenet_aim->domain}/", $_SERVER['HTTP_HOST'])) {
			$url             = $this->prod_url;
			$login           = $this->config->webservices->authorizenet_aim->login;
			$transaction_key = $this->config->webservices->authorizenet_aim->transaction_key;
			$test_request    = 'FALSE';
		}
		else {
			$url             = $this->test_url;
			$login           = $this->test_login;
			$transaction_key = $this->test_transaction_key;
			$test_request    = 'TRUE';
		}

		// Assembles an array of all our transaction variables and values
		$post_variables = array(
			'x_test_request'         => $test_request,
			'x_invoice_num'          => $this->order_id,
			'x_cust_id'              => trim($this->customer_id) != '' ? $this->customer_id : 'N/A',
			'x_cust_up'              => $this->customer_ip,
			'x_description'          => 'Menopause Solutions',
			'x_login'                => $login,
			'x_version'              => '3.1',
			'x_delim_char'           => '|',
			'x_delim_data'           => 'TRUE',
			'x_type'                 => 'AUTH_CAPTURE', // @todo let the user pass this in for more functionality
			'x_method'               => 'CC',
			'x_tran_key'             => $transaction_key,
			'x_relay_response'       => 'FALSE',

			// Payment information
			'x_card_num'             => $this->card_number,
			'x_exp_date'             => $this->expiration_month . $this->expiration_year,
			'x_amount'               => $this->total_amount,
			'x_freight'              => 'Shipping<|>Standard<|>' . $this->shipping,
			
			// Billing address information
			'x_company'              => $this->billing_company,
			'x_first_name'           => $this->billing_first_name,
			'x_last_name'            => $this->billing_last_name,
			'x_address'              => $this->billing_address1,
			'x_city'                 => $this->billing_city,
			'x_state'                => $this->billing_state,
			'x_zip'                  => $this->billing_zip_code,
			'x_country'              => $this->billing_country,
			'x_email'                => $this->billing_email,
			'x_phone'                => $this->billing_phone,
			'x_fax'                  => $this->billing_fax,
			
			// Shipping address information
			'x_ship_to_company'      => $this->shipping_company,
			'x_ship_to_first_name'   => $this->shipping_first_name,
			'x_ship_to_last_name'    => $this->shipping_last_name,
			'x_ship_to_address'      => $this->shipping_address1,
			'x_ship_to_city'         => $this->shipping_city,
			'x_ship_to_state'        => $this->shipping_state,
			'x_ship_to_zip'          => $this->shipping_zip_code,
			'x_ship_to_country'      => $this->shipping_country,

			// Order information
			// @todo I'd like to change the line item stuff to be part of the array and
			//       then looped through pragmatically, opposed to tacking it all to the end
			//       of the transaction (see below)
			//'x_line_item'            => '',

			// Email receipt information 
			'x_email_customer'       => true,

			// @todo These currently aren't in use
			// 'x_tax'                  => '',
			// 'se_session_token'       => '',
			// 'x_header_email_receipt' => '',
			// 'x_footer_email_receipt' => '',

			// @todo Debugging / testing information
			//'x_email'                => 'joshsherman@gmail.com',
			//'x_card_num'             => '4242424242424242',
		);

		// Assembles the POSTed fields
		$fields = '';
		foreach ($post_variables as $variable => $value) {
			$fields .= $variable . '=' . urlencode($value) . '&';
		}

		// Tacks the line items to the end of the assemble POST fields
		if (is_array($this->products)) {
			foreach ($this->products as $product_id => $product) {
				//$fields .= 'x_line_item=' . $product['sku'] . '<|>' . substr($product['name'], 0, 31) . '<|>' . substr($product['name'], 0, 255) . '<|>' . $product['quantity'] . '<|>' . $product['price'] . '<|>N&';
				$fields .= 'x_line_item=' . $product['sku'] . '<|>><|>' . substr($product['name'], 0, 255) . '<|>' . $product['quantity'] . '<|>' . $product['price'] . '<|>N&';
			}
		}

		// POSTs the transaction to Authorize.Net
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER,         0);                    // set to 0 to eliminate header info from response
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                    // Returns response data instead of TRUE(1)
		curl_setopt($curl, CURLOPT_POSTFIELDS,     rtrim($fields, '& ')); // use HTTP POST to send form data

		// @todo uncomment this line if you get no gateway response, or whatever they way
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 

		$response = curl_exec($curl);
		curl_close($curl);

		// Breaks apart the response and assigns it to an associative array
		$response_values = explode('|', $response, 41);
		$response = array_combine($this->response_variables, $response_values);

		file_put_contents('/tmp/authnet.log', print_r($response, true), FILE_APPEND);

		// Trims all of the variables up
		// @todo Replace this with a user defined trim() and use array_walk()
		foreach ($response as $key => $value) {
			$response[$key] = trim($value);
		}

		// Deciphers the cryptic internal response code
		// @todo case 4 is not represented
		switch ($response['response_code']) {
			case 1:  $value = 'Approved'; break;
			case 2:	 $value = 'Declined'; break;
			default: $value = 'Error';    break;
		}

		$response['response_code'] = $value;
	
		// Deciphers the cryptic internal card code response
		switch ($response['card_code_response']) {
			case 'M': $value .= ' = Match';                            break;
			case 'N': $value .= ' = No Match';                         break;
			case 'P': $value .= ' = Not Processed';                    break;
			case 'S': $value .= ' = Should have been present';         break;
			case 'U': $value .= ' = Issuer unable to process request'; break;
			case '':  $value = 'No value returned';                    break;
			default:  $value .= ' = Unknown value';                    break;
		}

		$response['card_code_response'] = $value;

		return $response;
	}
}

?>
