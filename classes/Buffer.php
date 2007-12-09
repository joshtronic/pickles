<?php

class Buffer {

	public static function get() {
		$buffer = ob_get_contents();
		ob_end_clean();

		$buffer = str_replace(
			array('    ', "\r\n", "\n", "\t"),
			null,
			$buffer
		);

		return $buffer;
	}

}

?>
