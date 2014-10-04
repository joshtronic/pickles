<?php

class CacheTest extends PHPUnit_Framework_TestCase
{
    private $cache;

    public function setUp()
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
                    "cache" => "mc",
                ],
                "datasources" => [
                    "mc" => [
                        "type"      => "memcache",
                        "hostname"  => "localhost",
                        "port"      => 11211,
                        "namespace" => "ns",
                    ],
                ],
            ];
        ');

        Pickles\Config::getInstance('/tmp/pickles.php');

        $this->cache = Pickles\Cache::getInstance();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('Pickles\\Cache', $this->cache);
    }

    public function testSetAndGet()
    {
        $key   = Pickles\String::random();
        $value = Pickles\String::random();

        $this->cache->set($key, $value);

        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testSetAndGetMultiple()
    {
        $keys = $values = $expected = [];

        for ($i = 0; $i < 5; $i++)
        {
            $keys[]   = Pickles\String::random();
            $values[] = Pickles\String::random();
        }

        foreach ($keys as $key => $key_name)
        {
            $value = $values[$key];
            $expected['NS-' . strtoupper($key_name)] = $value;
            $this->cache->set($key_name, $value);
        }

        $this->assertEquals($expected, $this->cache->get($keys));
    }

    public function testDelete()
    {
        $key   = Pickles\String::random();
        $value = Pickles\String::random();

        $this->cache->set($key, $value);
        $this->cache->delete($key);

        $this->assertFalse($this->cache->get($key));
    }

    public function testIncrement()
    {
        $key = Pickles\String::random();

        $this->assertFalse($this->cache->increment($key));

        $this->cache->set($key, 1);

        $this->assertEquals(2, $this->cache->increment($key));
        $this->assertEquals(3, $this->cache->increment($key));
        $this->assertEquals(4, $this->cache->increment($key));
    }

    // Doesn't do much but test that the destructor doesn't explode
    public function testDestructor()
    {
        $this->cache->__destruct();
    }
}

