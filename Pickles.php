<?php

date_default_timezone_set('America/New_York');

define('PICKLES_PATH', getcwd() . '/../../pickles/');
//define('TEMP_PATH',    '/tmp/smarty/' . $_SERVER['SERVER_NAME'] . '/');
define('TEMP_PATH',    '/home/41938/data/tmp/smarty/' . $_SERVER['SERVER_NAME'] . '/');

function __autoload($class) {
	$file = PICKLES_PATH . 'classes/' . str_replace('_', '/', $class) . '.php';
	
	if (file_exists($file)) {
		require_once $file;
	}
	else {
		$file = PICKLES_PATH . 'models/' . str_replace('_', '/', $class) . '.php';

		if (file_exists($file)) {
			require_once $file;
		}
	}
}

?>
