<?php

/**
 * PennySMS (via Email) Web Service Class File for PICKLES
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
 * PennySMS (via Email) Web Service
 */
class WebService_PennySMS_Email extends WebService_PennySMS_Common
{
	public function process()
	{	
		// @todo check that API key is not null;
		// @todo check that the phone is there
		// @todo check that the message is <= 160 characters

		$to = 'api@pennysms.com';
		$subject = 'Text Message via PennySMS (via Email)';
		$message = 'key: ' . $this->variables['api_key'] . "\n"
		         . 'cell: ' . $this->variables['phone'] . "\n"
				 . "\n"
				 . substr($this->variables['message'], 0, 160);

		mail($to, $subject, $message);
	}
}

?>
