<?php

class Viewer_Debug extends Viewer_Common {

	public function display() {
		echo '<h1>Debug</h1>' . "\n";
		echo '<h2>$_REQUEST</h2>' . "\n";
		echo '<pre>';
		var_dump($_REQUEST);
		echo '</pre>';
	}

}

?>
