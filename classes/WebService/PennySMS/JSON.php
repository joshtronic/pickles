<?php

/**
 * PennySMS (via JSON-RPC) Web Service Class File for PICKLES
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
 * PennySMS (via JSON-RPC) Web Service
 */
class WebService_PennySMS_JSON extends WebService_PennySMS_Common
{
	public function process()
	{	
		// @todo check that API key is not null;
		// @todo check that the phone is there
		// @todo check that the message is <= 160 characters

		$array = array(
			'method' => 'send',
			'params' => array(
				(string)$this->variables['api_key'],
				$this->variables['from'],
				$this->variables['phone'],
				addslashes(substr($this->variables['message'], 0, 160))
			)
		);

		$json = json_encode($array);
		var_dump($json);


		$params = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-Type: text/json' . "\r\n",
				'content' => $json
			)
		);
	
		$context  = stream_context_create($params);
		$response = file_get_contents('http://api.pennysms.com/jsonrpc', false, $context);

		// @todo error trapping / re-runs
	}
}

?>
