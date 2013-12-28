<?php

ob_start();

$vfs_files = [
	'/usr/local/Cellar/php55/5.5.7/lib/php/vfsStream/vfsStream.php',
	'./vendor/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php',
];

foreach ($vfs_files as $vfs_file)
{
	if (file_exists($vfs_file))
	{
		require_once $vfs_file;
	}
}

$root = vfsStream::setup('site');
define('SITE_PATH', vfsStream::url('site/'));

require_once 'classes/Object.php';
require_once 'classes/Config.php';
require_once 'classes/Display.php';
require_once 'classes/File.php';
require_once 'pickles.php';

?>
