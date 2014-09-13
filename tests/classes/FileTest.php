<?php

class FileTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        // Using actual filesystem because you can't chdir with vfs://
        $directory = '/tmp/pickles-fs/filetest/test/test';

        if (!file_exists($directory))
        {
            mkdir($directory, 0777, true);
        }
    }

    public static function tearDownAfterClass()
    {
        File::removeDirectory('/tmp/pickles-fs');
    }

    public function testRemoveDirectory()
    {
        $directory = '/tmp/pickles-fs/filetest/';

        touch($directory . 'ing');
        touch($directory . 'test/ing');
        touch($directory . 'test/test/ing');

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

