<?php

class Form {

	private function __construct() { }

	public static function displayContact() {
		?>
		<form action="">
			Name:<br />
			Email:<br />
			Subject:<br />
			Message:<br />

		</form>
		<?php
		return true;
	}

	public static function processContact() {

	}

}

?>


