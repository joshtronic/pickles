<?php

class Buffer {
	public static function get() {
    	$buffer = str_replace(
	        array('    ', "\r\n", "\n", "\t"),
    	    null,
    	    ob_get_contents()
	    );
	    ob_end_clean();
	    
		return $buffer;
	}
}

?>
