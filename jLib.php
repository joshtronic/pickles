<?php

function __autoload($class) {
	require_once "classes/{$class}.php";
}

Config::load($_SERVER['SERVER_NAME']);

?>
