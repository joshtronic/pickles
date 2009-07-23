<?php

/**
 * Mailer Class File for PICKLES
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
 * @copyright Copyright 2007, 2008, 2009 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Mailer Class
 *
 * Handles mailing messages from within PICKLES.  Modules interact with the
 * Mailer object directly (each module has an instance) to send mail.
 *
 * @todo Add a Pickles config to allow for overrides (i.e. email goes to a
 *       developer's email account instead of the actual account passed in)
 */
class Mailer extends Object
{
	/**
	 * Sends an email message
	 *
	 * @param  mixed $to String, object or array representing the recipient(s)
	 * @param  mixed $from String object or array representing the sender
	 * @param  string $subject Subject line for the email
	 * @param  string $message The body of the email
	 * @return array An associative array with a status type and message
	 */
	public function send($to, $from, $subject, $message, $html = false)
	{
		// Converts the recipients into a usable string format 
		if (is_object($to)) { $this->object2array($to); }
		if (is_array($to))  { $this->array2string($to); }
		
		// Converts the from variable into a usable string format 
		if (is_object($from)) { $this->object2array($from); }
		if (is_array($from))  { $this->array2string($from); }

		// Constructs the header
		$additional_headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\nFrom: {$from}\r\nX-Mailer: PICKLES (http://phpwithpickles.com)\r\n";

		// Sends the mail
		//if (mail($to, stripslashes(trim($subject)), stripslashes(trim($message)), $additional_headers))
		if (mail($to, stripslashes(trim($subject)), trim($message), $additional_headers))
		{
			$type    = 'success';
			$message = 'Message sent successfully';
		}
		else
		{
			$type    = 'error';
			$message = 'An unexpected error has occurred';
		}

		Logger::write('mailer', '[' . $type . ']');

		// Returns the status array
		return array(
			'type'    => $type,
			'message' => $message
		);
	}

	/**
	 * Converts an object to an array
	 *
	 * This function assumes that the object is formatted in a certain way,
	 * with a name and email member variable.
	 *
	 * @param  object $object Object to be converted
	 * @return array The resulting array
	 */
	private function object2array(&$object)
	{
		$array = array();

		foreach ($object as $key => $node)
		{
			if (isset($node->name, $node->email))
			{
				$array[trim((string)$node->name)] = trim((string)$node->email);
			}
			else if (isset($node->email))
			{
				$array[] = trim((string)$node->email);
			}
			else
			{
				$array[] = trim((string)$node);
			}
		}

		$object = $array;
	}


	/**
	 * Converts an array to a string
	 *
	 * This function assumes that the array is formatted in a certain way,
	 * with the name as the key and the email as the value.
	 *
	 * @param  array $array Array to be converted
	 * @return string The resulting string
	 */
	private function array2string(&$array)
	{
		$temp = array();

		foreach ($array as $name => $email)
		{
			if (is_string($name))
			{
				$temp[$name] = "{$name} <{$email}>";
			}
			else
			{
				$temp[] = $email;
			}
		}

		$array = implode(', ', $temp);
	}
}

?>
