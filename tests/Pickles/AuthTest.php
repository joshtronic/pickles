<?php

class AuthTest extends PHPUnit_Framework_TestCase
{
    private $auth;

    public function setUp()
    {
        Pickles\Object::$instances = [];

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = '127.0.0.1';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->auth = new Pickles\Auth();
    }

    public function testBasic()
    {
        $this->assertFalse($this->auth->basic());
    }

    public function testOAuth2()
    {
        $this->assertFalse($this->auth->oauth2());
    }
}

