<?php

class SessionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (session_id())
        {
            session_destroy();
        }

        $_SERVER['HTTP_USER_AGENT'] = 'yes';
        $_SERVER['REQUEST_METHOD']  = 'GET';
    }

    public function testFiles()
    {
        $config = Config::getInstance();
        $config->data['pickles']['sessions'] = 'files';

        new Session();

        $_SESSION['test'] = 'files';
        $this->assertEquals('files', $_SESSION['test']);
    }

    public function testMemcache()
    {
        $config = Config::getInstance();
        $config->data['pickles']['sessions'] = 'memcache';
        $config->data['datasources']['memcache'] = [
            'type'     => 'memcache',
            'hostname' => 'localhost',
            'port'     => '11211',
        ];

        new Session();

        $_SESSION['test'] = 'memcache';
        $this->assertEquals('memcache', $_SESSION['test']);
    }

    public function testMemcached()
    {
        $config = Config::getInstance();
        $config->data['pickles']['sessions'] = 'memcached';
        $config->data['datasources']['memcached'] = [
            'type'     => 'memcached',
            'hostname' => 'localhost',
            'port'     => '11211',
        ];

        new Session();

        $_SESSION['test'] = 'memcached';
        $this->assertEquals('memcached', $_SESSION['test']);
    }

    public function testRedis()
    {
        $config = Config::getInstance();
        $config->data['pickles']['sessions'] = 'redis';
        $config->data['datasources']['redis'] = [
            'type'     => 'redis',
            'hostname' => 'localhost',
            'port'     => '6379',
            'database' => '1',
            'prefix'   => 'p:',
        ];

        new Session();

        $_SESSION['test'] = 'redis';
        $this->assertEquals('redis', $_SESSION['test']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage You must provide both the hostname and port for the datasource.
     */
    public function testMissingHostname()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $config = Config::getInstance();
        $config->data['pickles']['sessions'] = 'redis';
        $config->data['datasources']['redis'] = [
            'type'     => 'redis',
            'port'     => '6379',
        ];

        new Session();

        $_SESSION['test'] = 'redis';
        $this->assertEquals('redis', $_SESSION['test']);
    }
}

