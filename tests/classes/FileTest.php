<?php

class FileTest extends PHPUnit_Framework_TestCase
{
	function testRemoveDirectory()
	{
		$directory = SITE_PATH . 'test/test/test/';

		mkdir($directory, 0777, true);
		touch(SITE_PATH . 'test/ing');
		touch(SITE_PATH . 'test/test/ing');
		touch(SITE_PATH . 'test/test/test/ing');

		File::removeDirectory($directory);

		$this->assertFalse(file_exists($directory));
	}
}

?>
