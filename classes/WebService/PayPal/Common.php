<?php

/**
 * Common PayPal Web Service Class File for PICKLES
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
 * Common PayPal Web Service Class
 *
 * This is the class that each PayPal gateway class should be extending from.
 */
abstract class WebService_PayPal_Common extends WebService_Common {

	private $test_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	private $prod_url = 'https://www.paypal.com/cgi-bin/webscr';

	protected $url = null;
	
	/**
	 * Constructor
	 *
	 * Runs the parent's constructor and adds the module to the object.
	 */
	public function __construct(Config $config, Error $error) {
		parent::__construct($config, $error);

		$this->config = $config;
		$this->error  = $error;
		
		$this->url = $this->test_url;

		// @todo there is a test flag for paypal "test_ipn = 1"

		// Loads the API keys based on what URL is being loaded
		// if (preg_match("/{$this->config->webservices->authorizenet_aim->domain}/", $_SERVER['HTTP_HOST'])) {
		// 	$url             = $this->prod_url;
		// 	$login           = $this->config->webservices->authorizenet_aim->login;
		// 	$transaction_key = $this->config->webservices->authorizenet_aim->transaction_key;
		// 	$test_request    = 'FALSE';
		// }
		// else {
		// 	$url             = $this->test_url;
		// 	$login           = $this->test_login;
		// 	$transaction_key = $this->test_transaction_key;
		// 	$test_request    = 'TRUE';
		// }
	}

	/**
	 * Abstract processing function that is overloaded within the loaded gateway
	 */
	//public abstract function process();
}

?>
