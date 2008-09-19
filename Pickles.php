<?php

date_default_timezone_set('America/New_York');

define('PICKLES_PATH', (phpversion() < 5.3 ? dirname(__FILE__) : __DIR__) . '/');
define('TEMP_PATH',    sys_get_temp_dir() . '/pickles/smarty/' . $_SERVER['SERVER_NAME'] . '/');

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
