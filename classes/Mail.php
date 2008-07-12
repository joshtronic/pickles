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

		$subject = str_replace("\n", '', (isset($prefix) ? "[{$prefix}] " : '') . $_REQUEST['subject']);

		if (mail($to, $subject, stripslashes($_REQUEST['message']), "From: {$_REQUEST['email']}\r\n")) {
			$type    = 'success';
			$message = 'Message sent successfully';
		}
		else {
			$type    = 'error';
			$message = 'An unexpected error has occurred';
		}

		$return = array(
			'type' => $type,
			'message' => $message
		);

		return $return;
	}
}

?>
