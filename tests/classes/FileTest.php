<?php

class FileTest extends PHPUnit_Framework_TestCase
{
	public function testRemoveDirectory()
	{
		$directory = SITE_PATH . 'test/test/test/';

		mkdir($directory, 0777, true);
		touch(SITE_PATH . 'test/ing');
		touch(SITE_PATH . 'test/test/ing');
		touch(SITE_PATH . 'test/test/test/ing');

		File::removeDirectory($directory);

		$this->assertFalse(file_exists($directory));
	}

	public function testMissingTrailingSlash()
	{
		$directory = SITE_PATH . 'missing';

		mkdir($directory, 0777, true);
		touch(SITE_PATH . 'missing/slash');

		File::removeDirectory($directory);

		$this->assertFalse(file_exists($directory));
	}

	public function testRemoveFileNotDirectory()
	{
		$directory = SITE_PATH . 'dir';
		$file      = SITE_PATH . 'dir/file';

		mkdir($directory, 0777, true);
		touch($file);

		File::removeDirectory($file);

		$this->assertFalse(file_exists($file));

		File::removeDirectory($directory);
	}
}

?>
