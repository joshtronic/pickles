<?php

ob_start();

require_once '.composer/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');
define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));

require_once 'classes/Object.php';
require_once 'classes/Config.php';
require_once 'classes/Display.php';
require_once 'classes/File.php';
require_once 'pickles.php';

?>
