<?php

set_exit_overload(function(){ return false; });

ob_start();
@session_start();

require_once 'vendors/composer/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');

if (!defined('SITE_PATH'))
{
	define('SECURITY_LEVEL_USER',  10);
	define('SECURITY_LEVEL_ADMIN', 20);
	define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));
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

if (!file_exists(SITE_TEMPLATE_PATH . '__shared/'))
{
	mkdir(SITE_TEMPLATE_PATH . '__shared/', 0644);
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
