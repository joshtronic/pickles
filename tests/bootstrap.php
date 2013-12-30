<?php

ob_start();

require_once '.composer/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');

if (!defined('SITE_PATH'))
{
	define('SECURITY_LEVEL_USER', 10);
	define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));
	// This isn't ideal but it helps a ton when testing the Browser class.
	define('UNIT_TESTING', true);
}

require_once 'pickles.php';

if (!file_exists(SITE_MODULE_PATH))
{
	mkdir(SITE_MODULE_PATH, 0644);
}

if (!file_exists(SITE_TEMPLATE_PATH))
{
	mkdir(SITE_TEMPLATE_PATH, 0644);
}

$_SERVER['HTTP_HOST']   = 'testsite.com';
$_SERVER['SERVER_NAME'] = 'Test Server';

function setUpRequest($request, $method = 'GET')
{
	$_SERVER['REQUEST_URI']    = '/' . $request;
	$_SERVER['REQUEST_METHOD'] = $method;
	$_REQUEST['request']       = $request;
}

?>
