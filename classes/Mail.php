<?php

class Mail {

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
