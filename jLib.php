<?php

date_default_timezone_set('America/New_York');
define('JLIB_PATH', '/var/www/josh/common/');

function __autoload($class) {
	require_once JLIB_PATH . 'classes/' . $class . '.php';
}

// Obliterates any passed in PHPSESSID (thanks Google)
if (stripos($_SERVER['REQUEST_URI'], '?PHPSESSID=') !== false) {
	list($request_uri, $phpsessid) = split('\?PHPSESSID=', $_SERVER['REQUEST_URI'], 2);
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . $request_uri);
	exit();
}

// XHTML compliancy stuff
ini_set('arg_separator.output', '&amp;');
ini_set('url_rewriter.tags',    'a=href,area=href,frame=src,input=src,fieldset=');

// Strips the subdomain before loading the configuration file
$config_array   = split('\.', $_SERVER['SERVER_NAME']);
$subless_server = null;
if (count($config_array) == 3) {
	$subless_server = $config_array[1] . '.' . $config_array[2];
}

// Do some prep work if we're working locally
if (strpos($_SERVER['SERVER_NAME'], '.localhost')) {
	foreach (array('com', 'net', 'org') as $tld) {
		$config = str_replace('.localhost', '.' . $tld, $_SERVER['SERVER_NAME']);

		if (Config::check($config)) {
			$_SERVER['SERVER_NAME'] = $config;
			break;
		}
	}
}

Config::load($subless_server ? $subless_server : $_SERVER['SERVER_NAME']);

// Generic "site down" message
if (Config::getDisable()) {
	exit("<h2><em>{$_SERVER['SERVER_NAME']} is currently down for maintenance</em></h2>");
}

if (Config::getSession() && !isset($_SESSION)) {
	session_start();
}

// Smarty default stuff
if (Config::getSmarty()) {
	require_once 'contrib/smarty/Smarty.class.php';

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

	// Include custom Smarty functions
	$directory = JLIB_PATH . 'smarty/';
	if (is_dir($directory)) {
	    if ($handle = opendir($directory)) {
			while (($file = readdir($handle)) !== false) {
				if (!preg_match('/^\./', $file)) {
					list($type, $name, $ext) = split('\.', $file);
					require_once $directory . $file;
					$smarty->register_function($name, "smarty_{$type}_{$name}");
				}
			}
			closedir($handle);
		}
	}
}

// Use the FCKeditor instead of textareas
if (Config::getFCKEditor()) {
	require_once '/var/www/josh/common/static/fckeditor/fckeditor.php';
}

// Use the FCKeditor instead of textareas
if (Config::getMagpieRSS()) {
	require_once '/var/www/josh/common/contrib/magpierss/rss_fetch.inc';
}

//Request::load();

?>
