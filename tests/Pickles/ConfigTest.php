<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        touch('/tmp/pickles.php');
    }

    public static function tearDownAfterClass()
    {
        unlink('/tmp/pickles.php');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Missing $config array.
     */
    public function testMissingConfig()
    {
        file_put_contents('/tmp/pickles.php', '');

        new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Environments are misconfigured.
     */
    public function testMissingEnvironments()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [];
        ');

        new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must pass an environment (e.g. php script.php <environment>)
     */
    public function testMissingCLIEnvironment()
    {
        $_SERVER['argc'] = 1;

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
            ];
        ');

        new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must pass an environment (e.g. php script.php <environment>)
     */
    public function testCLIEnvironmentMissingParameter()
    {
        $_SERVER['argc'] = 1;

        new Pickles\Config('/tmp/pickles.php');
    }

    public function testEnvironmentMatchCLI()
    {
        $_SERVER['argc']    = 2;
        $_SERVER['argv'][1] = 'local';

        $config = new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('local', $config['environment']);
    }

    public function testEnvironmentMatchExact()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('local', $config['environment']);
    }

    public function testEnvironmentMatchFuzzy()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = '127.0.0.1';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "/127\.0\.0\.[0-9]+/",
                    "production" => "123.456.789.0",
                ],
            ];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('local', $config['environment']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Unable to determine the environment.
     */
    public function testEnvironmentNoMatch()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = 'lolnope';

        new Pickles\Config('/tmp/pickles.php');
    }

    public function testProductionDisplayErrors()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST']      = '123.456.789.0';

        ini_set('display_errors', true);

        $this->assertEquals('1', ini_get('display_errors'));

        new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('', ini_get('display_errors'));
    }

    public function testFlatten()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST']      = '123.456.789.0';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "/127\.0\.0\.[0-9]+/",
                    "production" => "123.456.789.0",
                ],
                "foo" => [
                    "local"      => "barLocal",
                    "production" => "barProduction",
                ],
                "nestedOne" => [
                    "nestedTwo" => [
                        "local"      => "nestedLocal",
                        "production" => "nestedProduction",
                    ],
                ],
            ];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('barProduction', $config['foo']);
        $this->assertEquals('nestedProduction', $config['nestedOne']['nestedTwo']);
    }

    public function testGetInstance()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST']      = '123.456.789.0';

        $config = Pickles\Config::getInstance('/tmp/pickles.php');

        $this->assertInstanceOf('Pickles\\Config', $config);
    }
}

