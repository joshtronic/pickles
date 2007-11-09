<?php

function __autoload($class) {
	require_once "/var/www/josh/common/classes/{$class}.php";
}

// Obliterates any passed in PHPSESSID (thanks Google)
if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false) {
    list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: {$request_uri}");
    exit(); 
}

// XHTML compliancy stuff
ini_set('arg_separator.output', '&amp;');
ini_set('url_rewriter.tags', 'a=href,area=href,frame=src,input=src,fieldset=');

$_SERVER['SERVER_NAME'] = str_replace('.localhost', '.com', $_SERVER['SERVER_NAME']);
Config::load($_SERVER['SERVER_NAME']);

// Generic "site down" message
if (Config::getDisable()) {
	exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
}

if (Config::getSession()) {
	session_start();
}

// Smarty default stuff
if (Config::getSmarty()) {
	require_once 'smarty/Smarty.class.php';

	$smarty = new Smarty();

	define('TEMPLATES', "/var/www/josh/{$_SERVER['SERVER_NAME']}/templates/");
	$smarty->template_dir = TEMPLATES;

	$temp_path   = "/tmp/smarty/{$_SERVER['SERVER_NAME']}/";
	$cache_dir   = $temp_path . 'cache';
	$compile_dir = $temp_path . 'compile';

	if (!file_exists($cache_dir))   { mkdir($cache_dir,   0777, true); }
	if (!file_exists($compile_dir)) { mkdir($compile_dir, 0777, true); }

	$smarty->cache_dir   = $cache_dir ;
	$smarty->compile_dir = $compile_dir;

	$smarty->load_filter('output','trimwhitespace');
}

// Use the FCKeditor instead of textareas
if (Config::getFCKEditor()) {
	require_once '/var/www/josh/common/static/fckeditor/fckeditor.php';
}

//Request::load();

?>
