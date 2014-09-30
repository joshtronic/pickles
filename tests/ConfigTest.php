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
     * @expectedException Exception
     * @expectedExceptionMessage Missing $config array.
     */
    public function testMissingConfig()
    {
        $config = new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Environments are misconfigured.
     */
    public function testMissingEnvironments()
    {
        file_put_contents('/tmp/pickles.php', '
            <?php
            $config = [];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must pass an environment (e.g. php script.php <environment>)
     */
    public function testMissingCLIEnvironment()
    {
        $_SERVER['argc'] = 1;

        file_put_contents('/tmp/pickles.php', '
            <?php
            $config = [
                "environments" => [
                    "local" => "127.0.0.1",
                    "production" => "123.456.798.0",
                ],
            ];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must pass an environment (e.g. php script.php <environment>)
     */
    public function testCLIEnvironmentMissingParameter()
    {
        $_SERVER['argc'] = 1;

        file_put_contents('/tmp/pickles.php', '
            <?php
            $config = [
                "environments" => [
                    "local" => "127.0.0.1",
                    "production" => "123.456.798.0",
                ],
            ];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');
    }

    public function testCLIEnvironment()
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'][1] = 'local';

        file_put_contents('/tmp/pickles.php', '
            <?php
            $config = [
                "environments" => [
                    "local" => "127.0.0.1",
                    "production" => "123.456.798.0",
                ],
            ];
        ');

        $config = new Pickles\Config('/tmp/pickles.php');

        $this->assertEquals('local', $config['environment']);
    }
}

