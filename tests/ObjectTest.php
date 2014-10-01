<?php

class ObjectTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME']    = '127.0.0.1';

        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "datasource" => "mysql",
                ],
                "datasources" => [
                    "mysql" => [
                        "driver" => "pdo_mysql",
                    ],
                ],
            ];
        ');

        $config = Pickles\Config::getInstance('/tmp/pickles.php');
    }

    public static function tearDownAfterClass()
    {
        unlink('/tmp/pickles.php');
    }

    public function testConstructorWithoutObjects()
    {
        $object = new Pickles\Object();

        $this->assertInstanceOf('Pickles\\Config', PHPUnit_Framework_Assert::readAttribute($object, 'config'));
    }

    public function testConstructorWithObjects()
    {
        $object = new Pickles\Object('cache');
        $this->assertInstanceOf('Pickles\\Cache', $object->cache);

        $object = new Pickles\Object(['cache', 'db']);
        $this->assertInstanceOf('Pickles\\Cache',    $object->cache);
        $this->assertInstanceOf('Pickles\\Database', $object->db);
    }

    public function testGetInstanceWithoutClass()
    {
        $this->assertFalse(Pickles\Object::getInstance());
    }

    public function testProfiler()
    {
        file_put_contents('/tmp/pickles.php', '<?php
            $config = [
                "environments" => [
                    "local"      => "127.0.0.1",
                    "production" => "123.456.789.0",
                ],
                "pickles" => [
                    "datasource" => "mysql",
                    "profiler"   => true,
                    "foo" => "bar",
                ],
                "datasources" => [
                    "mysql" => [
                        "driver" => "pdo_mysql",
                    ],
                ],
            ];
        ');

        $config = Pickles\Config::getInstance('/tmp/pickles.php');
        $object = new Pickles\Object();
    }
}

