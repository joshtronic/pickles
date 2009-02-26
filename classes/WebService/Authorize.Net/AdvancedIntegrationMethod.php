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
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Authorize.Net Advanced Integrated Method (AIM) Web Service
 */
class WebService_AuthorizeNet_AdvancedIntegratedMethod extends WebService_Common {
	
	public function process() {

		// Once the user is customer and their addresses are added, perform the authenticate.net logic
		$debugging     = 1; // Display additional information to track down problems
		$testing	   = 1; // Set the testing flag so that transactions are not live
		$error_retries = 2; // Number of transactions to post if soft errors occur

		// @todo move to object variables
		$auth_net_url = "https://test.authorize.net/gateway/transact.dll";
		// Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
		// $auth_net_url      = "https://secure.authorize.net/gateway/transact.dll";

		$authnet_values = array(
			'x_invoice_num'    => $this->order_id,
			'x_login'          => $this->config->gateway->authorizenet_aim->test->login,
			'x_version'        => '3.1',
			'x_delim_char'     => '|',
			'x_delim_data'     => 'TRUE',
			'x_type'           => 'AUTH_CAPTURE',
			'x_method'         => 'CC',
			'x_tran_key'       => $this->config->gateway->authorizenet_aim->test->transaction_key,
			'x_relay_response' => 'FALSE',

			'x_card_num'    => $this->cc_number,
			'x_exp_date'    => $this->expiration_month . $this->expiration_year,
			'x_description' => 'Menopause Solutions',
			'x_amount'      => $this->total_amount,
			
			'x_company'    => $this->billing_company,
			'x_first_name' => $this->billing_first_name,
			'x_last_name'  => $this->billing_last_name,
			'x_address'    => $this->billing_address1,
			'x_city'       => $this->billing_city,
			'x_state'      => $this->billing_state,
			'x_zip'        => $this->billing_zip_code,
			'x_country'    => $this->billing_country,
			'x_email'      => $this->billing_email,
			'x_phone'      => $this->billing_phone,
			'x_fax'        => $this->billing_fax,
			
			'x_ship_to_company'    => $this->shipping_company,
			'x_ship_to_first_name' => $this->shipping_first_name,
			'x_ship_to_last_name'  => $this->shipping_last_name,
			'x_ship_to_address'    => $this->shipping_address1,
			'x_ship_to_city'       => $this->shipping_city,
			'x_ship_to_state'      => $this->shipping_state,
			'x_ship_to_zip'        => $this->shipping_zip_code,
			'x_ship_to_country'    => $this->shipping_country,

			// @todo
			'x_email' => 'joshsherman@gmail.com',

			'x_email_customer'       => true,
			'x_header_email_receipt' => 'header text',
			'x_footer_email_receipt' => 'footer text',

			// 'x_tax'                => '',
			// 'x_freight'            => '',

			// 'x_invoice_num'        => '',
			// 'se_session_token'     => '',
		);

		// Assembles the POSTed fields
		$fields = '';
		foreach ($authnet_values as $key => $value) {
			$fields .= "{$key}=" . urlencode($value) . '&';
		}

		// Post the transaction to Authorize.net
		$ch = curl_init("https://test.authorize.net/gateway/transact.dll"); 
		// Uncomment the line ABOVE for test accounts or BELOW for live merchant accounts
		// $ch = curl_init("https://secure.authorize.net/gateway/transact.dll"); 
		curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $fields, "& " )); // use HTTP POST to send form data
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response. ###
		$response = curl_exec($ch); //execute post and get results
		curl_close ($ch);

		$h = substr_count($response, '|');
		$h++;

		for($j = 1; $j <= $h; $j++) {
			$p = strpos($response, '|');

			if ($p === false) {

			echo "<tr>";
			echo "<td class=\"e\">";

			//  x_delim_char is obviously not found in the last go-around

			if($j>=69){

				echo "Merchant-defined (".$j."): ";
				echo ": ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $response;
				echo "<br>";

			} else {

				echo $j;
				echo ": ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $response;
				echo "<br>";

			}


		echo "</td>";
		echo "</tr>";

	}else{

		$p++;

		//  We found the x_delim_char and accounted for it . . . now do something with it

		//  get one portion of the response at a time
		$pstr = substr($response, 0, $p);

		//  this prepares the text and returns one value of the submitted
		//  and processed name/value pairs at a time
		//  for AIM-specific interpretations of the responses
		//  please consult the AIM Guide and look up
		//  the section called Gateway Response API
		$pstr_trimmed = substr($pstr, 0, -1); // removes "|" at the end

		if($pstr_trimmed==""){
			$pstr_trimmed="NO VALUE RETURNED";
		}


		echo "<tr>";
		echo "<td class=\"e\">";

		switch($j){

			case 1:
				echo "Response Code: ";

				echo "</td>";
				echo "<td class=\"v\">";

				$fval="";
				if($pstr_trimmed=="1"){
					$fval="Approved";
				}elseif($pstr_trimmed=="2"){
					$fval="Declined";
				}elseif($pstr_trimmed=="3"){
					$fval="Error";
				}

				echo $fval;
				echo "<br>";
				break;

			case 2:
				echo "Response Subcode: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 3:
				echo "Response Reason Code: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 4:
				echo "Response Reason Text: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 5:
				echo "Approval Code: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 6:
				echo "AVS Result Code: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 7:
				echo "Transaction ID: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 8:
				echo "Invoice Number (x_invoice_num): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 9:
				echo "Description (x_description): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 10:
				echo "Amount (x_amount): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 11:
				echo "Method (x_method): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 12:
				echo "Transaction Type (x_type): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 13:
				echo "Customer ID (x_cust_id): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 14:
				echo "Cardholder First Name (x_first_name): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 15:
				echo "Cardholder Last Name (x_last_name): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 16:
				echo "Company (x_company): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 17:
				echo "Billing Address (x_address): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 18:
				echo "City (x_city): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 19:
				echo "State (x_state): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 20:
				echo "ZIP (x_zip): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 21:
				echo "Country (x_country): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 22:
				echo "Phone (x_phone): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 23:
				echo "Fax (x_fax): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 24:
				echo "E-Mail Address (x_email): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 25:
				echo "Ship to First Name (x_ship_to_first_name): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 26:
				echo "Ship to Last Name (x_ship_to_last_name): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 27:
				echo "Ship to Company (x_ship_to_company): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 28:
				echo "Ship to Address (x_ship_to_address): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 29:
				echo "Ship to City (x_ship_to_city): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 30:
				echo "Ship to State (x_ship_to_state): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 31:
				echo "Ship to ZIP (x_ship_to_zip): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 32:
				echo "Ship to Country (x_ship_to_country): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 33:
				echo "Tax Amount (x_tax): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 34:
				echo "Duty Amount (x_duty): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 35:
				echo "Freight Amount (x_freight): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 36:
				echo "Tax Exempt Flag (x_tax_exempt): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 37:
				echo "PO Number (x_po_num): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 38:
				echo "MD5 Hash: ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			case 39:
				echo "Card Code Response: ";

				echo "</td>";
				echo "<td class=\"v\">";

				$fval="";
				if($pstr_trimmed=="M"){
					$fval="M = Match";
				}elseif($pstr_trimmed=="N"){
					$fval="N = No Match";
				}elseif($pstr_trimmed=="P"){
					$fval="P = Not Processed";
				}elseif($pstr_trimmed=="S"){
					$fval="S = Should have been present";
				}elseif($pstr_trimmed=="U"){
					$fval="U = Issuer unable to process request";
				}else{
					$fval="NO VALUE RETURNED";
				}

				echo $fval;
				echo "<br>";
				break;

			case 40:
			case 41:
			case 42:
			case 43:
			case 44:
			case 45:
			case 46:
			case 47:
			case 48:
			case 49:
			case 50:
			case 51:
			case 52:
			case 53:
			case 54:
			case 55:
			case 55:
			case 56:
			case 57:
			case 58:
			case 59:
			case 60:
			case 61:
			case 62:
			case 63:
			case 64:
			case 65:
			case 66:
			case 67:
			case 68:
				echo "Reserved (".$j."): ";

				echo "</td>";
				echo "<td class=\"v\">";

				echo $pstr_trimmed;
				echo "<br>";
				break;

			default:

				if($j>=69){

					echo "Merchant-defined (".$j."): ";
					echo ": ";

					echo "</td>";
					echo "<td class=\"v\">";

					echo $pstr_trimmed;
					echo "<br>";

				} else {

					echo $j;
					echo ": ";

					echo "</td>";
					echo "<td class=\"v\">";

					echo $pstr_trimmed;
					echo "<br>";

				}

				break;

		}

		echo "</td>";
		echo "</tr>";

		// remove the part that we identified and work with the rest of the string
		$response = substr($response, $p);

	}

}

echo "</table>";
		
		exit();
	}
}

?>
