<?php

/**
 * Small collection of mail utilities
 *
 * A bit too small actually, as there is still only one function in here.  Since
 * I have built PICKLES based around my own needs, this has been the only 
 * function I have felt the need to add.
 *
 * @package   PICKLES
 * @author    Joshua Sherman <josh@phpwithpickles.org>
 * @copyright 2007-2008 Joshua Sherman
 * @todo      Just so it doesn't seem so bare, I need to come up and implement a
 *            few more mail functions.
 * @todo      Still thinking about making this an instantiated object instead of
 *            a static class.  Perhaps I should add a mail (rename it to mailer
 *            maybe) object to each model so it's ready to be loaded, and if it
 *            is loaded, then go ahead and automatically send it?!? Loves it.
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
		$defaults = $config->get('contact');

		if (!isset($recipients)) {
			$recipients = $defaults['recipients']['recipient'];
		}

		if (is_array($recipients)) {
			$to = null;
			foreach ($recipients as $recipient) {
				$to .= (isset($to) ? ',' : '') . $recipient;
			}
		}
		else {
			$to = $recipients;
		}

		if (!isset($prefix)) {
			$prefix = isset($defaults['prefix']) ? $defaults['prefix'] : null;
		}

		if (isset($defaults['subject'])) {
			$subject = $defaults['subject'];
		}
		else {
			$subject = str_replace("\n", '', (isset($prefix) ? "[{$prefix}] " : '') . $_REQUEST['subject']);
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
