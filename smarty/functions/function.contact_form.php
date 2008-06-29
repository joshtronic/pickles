<?php

function smarty_function_contact_form($params, &$smarty) {
	$form = '
		<form action="/contact/send" method="post">
			Email:<br />
			<input name="email" title="required" class="contact_input" /><br /><br />
			Subject:<br />
			<input name="subject" title="required" class="contact_input" /><br /><br />
			Message:<br />
			<textarea name="message" title="required" class="contact_textarea"></textarea><br /><br />
			<input type="button" value="Send" onclick="ajaxSubmit(this.parentNode); return false;" class="contact_button" />
		</form>
	';

	return $form;
}

?>
