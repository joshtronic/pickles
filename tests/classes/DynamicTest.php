<?php

class DynamicTest extends PHPUnit_Framework_TestCase
{
    private $dynamic;

    public static function setUpBeforeClass()
    {
        // Using actual filesystem because you can't chdir with vfs://
        $public_path = '/tmp/pickles-fs/public/';

        foreach (['css', 'images', 'js'] as $directory)
        {
            mkdir($public_path . $directory, 0777, true);
        }

        touch($public_path . 'images/image.png');
        touch($public_path . 'images/invalid');

        $css = <<<CSS
body
{
    color: #ffcc00;
    text-align: center;
}
CSS;

        foreach (['css', 'less', 'scss'] as $extension)
        {
            file_put_contents($public_path . 'css/stylesheet.' . $extension, $css);
        }

        file_put_contents($public_path . 'css/alternate.css', $css);

        $js = <<<JS
function foo()
{
    alert('bar');
}

console.log('stuff');
JS;

        file_put_contents($public_path . 'js/script.js', $js);

        chdir($public_path);
    }

    public function setUp()
    {
        $this->dynamic = new Dynamic();
    }

    public function tearDown()
    {
        $minified_file = '/tmp/pickles-fs/public/css/stylesheet.min.css';

        if (file_exists($minified_file))
        {
            unlink($minified_file);
        }
    }

    public static function tearDownAfterClass()
    {
        File::removeDirectory('/tmp/pickles-fs');
    }

    public function testReference()
    {
        $this->assertRegExp(
            '/^\/images\/image\.\d{10}\.png$/',
            $this->dynamic->reference('/images/image.png'
        ));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Supplied reference does not exist (/images/missing.png)
     */
    public function testReferenceMissingFileWithoutFailover()
    {
        $this->dynamic->reference('/images/missing.png');
    }

    public function testReferenceMissingFileWithFailover()
    {
        $this->assertEquals(
            '/images/failover.png',
            $this->dynamic->reference('/images/missing.png', '/images/failover.png')
        );
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Filename must have an extension (e.g. /path/to/file.png)
     */
    public function testReferenceInvalidFilename()
    {
        $this->dynamic->reference('/images/invalid');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Reference value must be absolute (e.g. /path/to/file.png)
     */
    public function testReferenceNotAbsolute()
    {
        $this->dynamic->reference('../images/relative.png');
    }

    public function testReferenceWithQueryString()
    {
        $this->assertRegExp(
            '/^\/images\/image\.\d{10}\.png\?foo=bar$/',
            $this->dynamic->reference('/images/image.png?foo=bar'
        ));
    }
}

