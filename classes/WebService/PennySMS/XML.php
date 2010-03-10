<?php

/**
 * PennySMS (via XML-RPC) Web Service Class File for PICKLES
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
 * PennySMS (via XML-RPC) Web Service
 */
class WebService_PennySMS_XML extends WebService_PennySMS_Common
{
	public function process()
	{
		$success = false;

		if ($this->checkVariables() === true)
		{
			$xml = '
				<?xml version="1.0"?>
				<methodCall>
					<methodName>send</methodName>
					<params>
						<param>
							<value><string>' . $this->variables['api_key'] . '</string></value>
						</param>
						<param>
							<value><string>' . $this->variables['from'] . '</string></value>
						</param>
						<param>
							<value><string>' . $this->variables['phone'] . '</string></value>
						</param>
						<param>
							<value><string>' . substr($this->variables['message'], 0, 160) . '</string></value>
						</param>
					</params>
				</methodCall>
			';
			
			// Cleans up the XML before sending it
			$xml = str_replace(array("\t", "\r", "\n"), '', $xml);

			$params = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => 'Content-Type: text/xml' . "\r\n",
					'content' => $xml
				)
			);
		
			$context  = stream_context_create($params);
			$response = file_get_contents('http://api.pennysms.com/xmlrpc', false, $context);

			Logger::write('pennysms', 'SENT: ' . $xml . ' RCVD: ' . trim($response));

			if ($response == '<?xml version="1.0" ?><methodResponse><params><param><value><string>OK</string></value></param></params></methodResponse>' . "\n")
			{
				$success = true;
			}
		}

		return $success;
	}
}

?>
