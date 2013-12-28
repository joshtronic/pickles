<?php

ob_start();

$vfs_files = [
	'/usr/local/Cellar/php55/5.5.7/lib/php/vfsStream/vfsStream.php',
	'./vendor/autoload.php',
];

echo getcwd();
print_r(scandir(getcwd()));

foreach ($vfs_files as $vfs_file)
{
	echo $vfs_file;
	if (file_exists($vfs_file))
	{
		echo 'loaded';
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
