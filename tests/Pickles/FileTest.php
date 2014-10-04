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
        Pickles\File::removeDirectory('/tmp/pickles-fs');
    }

    public function testRemoveDirectory()
    {
        $directory = '/tmp/pickles-fs/filetest/';

        touch($directory . 'ing');
        touch($directory . 'test/ing');
        touch($directory . 'test/test/ing');

        Pickles\File::removeDirectory($directory);

        $this->assertFalse(file_exists($directory));
    }

    public function testMissingTrailingSlash()
    {
        $directory = '/tmp/pickles-fs/missing';

        mkdir($directory, 0777, true);
        touch('/tmp/pickles-fs/missing/slash');

        Pickles\File::removeDirectory($directory);

        $this->assertFalse(file_exists($directory));
    }

    public function testRemoveFileNotDirectory()
    {
        $directory = '/tmp/pickles-fs/dir';
        $file      = '/tmp/pickles-fs/dir/file';

        mkdir($directory, 0777, true);
        touch($file);

        Pickles\File::removeDirectory($file);

        $this->assertFalse(file_exists($file));

        Pickles\File::removeDirectory($directory);

        $this->assertFalse(file_exists($directory));
    }
}

