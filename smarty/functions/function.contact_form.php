<?php

function smarty_function_contact_form($params, &$smarty) {
	$form = '
		<form action="/contact/send" method="post" class="contact_form">
			Email:<br />
			<input name="email" title="required" class="contact_input" /><br /><br />
			Subject:<br />
			<input name="subject" title="required" class="contact_input" /><br /><br />
			Message:<br />
			<textarea name="message" title="required" class="contact_textarea"></textarea><br /><br />
			<div class="contact_button">
				<input type="button" value="Send Message" onclick="ajaxRequest(this.parentNode.parentNode); return false;" />
			</div>
		</form>
	';

	return $form;
}

?>
