<?php

function __autoload($class) {
	require_once "classes/{$class}.php";
}

$server = explode('.', $_SERVER["SERVER_NAME"]);
$site   = $server[count($server) - 2];

// @debug
$site = 'meatronic';

Config::load($site);

?>
