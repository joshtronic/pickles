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
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Mailer Class
 *
 * Handles mailing messages from within PICKLES.  Mailer data is
 * loaded into the object (each model has one) and after everything
 * is done loading, it will automatically send out the email.
 *
 * @todo Logic needs to be cleaned up a bit (it's just sloppy since
 *       the conversion from Mail();
 */
class Mailer extends Object {

	public function __construct(Config $config, Error $error) {
		parent::__construct();
		$this->config = $config;
		$this->error  = $error;
	}

	/**
	 * Sends an email message
	 *
	 * @param  array $recipients An array of recipients (optional)
	 * @param  string $prefix Prefix to use on the subject line (optional)
	 * @return array An associative array with a status type and message
	 */
	public function send() {

		// Gets the values (is any) set in the config
		$defaults = $this->config->contact;

		// Pulls the recipients from the config
		if (!isset($this->recipients) && isset($defaules->recipients->recipient)) {
			$this->recipients = $defaults->recipients->recipient;
		}

		// Loads up the "to" value
		if (is_object($this->recipients)) {
			$to = null;
			foreach ($this->recipients as $recipient) {
				$to .= (isset($to) ? ',' : '') . (string)$recipient;
			}
		}
		else {
			$to = $this->recipients;
		}

		// Loads the subject line prefix
		$prefix = isset($this->prefix) ? $this->prefix : (isset($defaults->prefix) && $defaults->prefix != '' ? $defaults->prefix : null);

		// Assembles the subject line with prefix
		$subject = strtr((isset($prefix) ? '[' . $prefix . '] ' : ''), "\n", '');

		// Tacks on the subject
		if (isset($this->subject)) {
			$subject .= $this->subject;
		}
		else if (isset($defaults->subjec)) {
			$subject .= $defaults->subject;
		}

		// Puts together the sender's contact info in name <email> format
		if (isset($this->name)) {
			$from = $this->name . ' <' . $this->email . '>';
		}
		else {
			$from = $this->email;
		}

		// Sends the mail
		if (mail($to, $subject, stripslashes($this->message), "From: {$from}\r\nX-Mailer: PHP with PICKLES\r\n")) {
			$type    = 'success';
			$message = isset($defaults['response']) ? $defaults['response'] : 'Message sent successfully';
		}
		else {
			$type    = 'error';
			$message = 'An unexpected error has occurred';
		}

		Logger::write('mailer', '[' . $type . ']');

		// Builds the status array to be returned
		$return = array(
			'type'    => $type,
			'message' => $message
		);

		return $return;
	}
}

?>
