<?php

/**
 * Mail Class File for PICKLES
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
 * Small collection of mail utilities
 *
 * A bit too small actually, as there is still only one function in here.  Since
 * I have built PICKLES based around my own needs, this has been the only 
 * function I have felt the need to add.
 *
 * @todo Just so it doesn't seem so bare, I need to come up and implement a
 *       few more mail functions.
 * @todo Still thinking about making this an instantiated object instead of
 *       a static class.  Perhaps I should add a mail (rename it to mailer
 *       maybe) object to each model so it's ready to be loaded, and if it
 *       is loaded, then go ahead and automatically send it?!? Loves it.
 */
class Mail {

	/**
	 * Sends an email message
	 *
	 * Creates and sends an email message.  Relies heavily on a certain set of
	 * circumstances specifically, the sender information, subject and message
	 * are all assumed to be in the $_REQUEST variable and named a certain way.
	 * This isn't that bad assuming you're using the PICKLES canned contact form
	 * or adhere to the naming conventions.  Recipient, subject line and subject
	 * line prefix can all be loaded in from the configuration file.
	 *
	 * @param  array $recipients An array of recipients (optional)
	 * @param  string $prefix Prefix to use on the subject line (optional)
	 * @return array An associative array with a status type and message
	 */
	static function send($recipients = null, $prefix = null) {
		$config   = Config::getInstance();
		$defaults = $config->contact;

		if (!isset($recipients)) {
			$recipients = $defaults->recipients->recipient;
		}

		if (is_object($recipients)) {
			$to = null;
			foreach ($recipients as $recipient) {
				$to .= (isset($to) ? ',' : '') . (string)$recipient;
			}
		}
		else {
			$to = $recipients;
		}

		if (!isset($prefix)) {
			$prefix = isset($defaults->prefix) && $defaults->prefix != '' ? $defaults->prefix : null;
		}

		$subject = str_replace("\n", '', (isset($prefix) ? "[{$prefix}] " : ''));

		if (isset($defaults->subject)) {
			$subject .= $defaults->subject;
		}
		else {
			$subject .= $_REQUEST['subject'];
		}

		if (isset($_REQUEST['name'])) {
			$from = "{$_REQUEST['name']} <{$_REQUEST['email']}>";
		}
		else {
			$from = $_REQUEST['email'];
		}

		if (mail($to, $subject, stripslashes($_REQUEST['message']), "From: {$from}\r\n")) {
			$type    = 'success';
			$message = isset($defaults['response']) ? $defaults['response'] : 'Message sent successfully';
		}
		else {
			$type    = 'error';
			$message = 'An unexpected error has occurred';
		}

		$return = array(
			'type'    => $type,
			'message' => $message
		);

		return $return;
	}
}

?>
