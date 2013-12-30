<?php

ob_start();

require_once '.composer/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');

if (!defined('SITE_PATH'))
{
	define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));
}

require_once 'pickles.php';

?>
