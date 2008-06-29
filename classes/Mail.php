<?php

class Mail {

	static function send($recipients = null, $prefix = null) {

		global $smarty;

		$defaults = Config::get('contact');

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

		if (mail($to, str_replace("\n", '', (isset($prefix) ? "[{$prefix}] " : '') . $_REQUEST['subject']), stripslashes($_REQUEST['message']), "From: {$_REQUEST['email']}\r\n")) {
			$type    = 'success';
			$message = 'Message sent successfully';
		}
		else {
			$type    = 'error';
			$message = 'An unexpected error has occurred';
		}

		$smarty->assign('type',    $type);
		$smarty->assign('message', $message);
	}
}

?>
