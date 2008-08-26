<?php

date_default_timezone_set('America/New_York');
define('PATH', getcwd() . '/');

function __autoload($class) {
	$file = PATH . '../../pickles/classes/' . str_replace('_', '/', $class) . '.php';
	
	if (file_exists($file)) {
		require_once $file;
	}
	else {
		$file = PATH . '../../pickles/models/' . str_replace('_', '/', $class) . '.php';

		if (file_exists($file)) {
			require_once $file;
		}
	}
}

?>
