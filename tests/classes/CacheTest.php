<?php

class CacheTest extends PHPUnit_Framework_TestCase
{
	private $config;
	private $cache;

	public function setUp()
	{
		$this->config = Config::getInstance();
		$this->config->data['pickles']['cache'] = 'mc';
		$this->config->data['datasources']['mc'] = [
			'type'     => 'memcache',
			'hostname' => 'localhost',
			'port'     => 11211,
		];

		$this->cache = Cache::getInstance();
	}

	public function testGetInstance()
	{
		$this->assertInstanceOf('Cache', $this->cache);
	}

	public function testSetAndGet()
	{
		$key   = String::random();
		$value = String::random();

		$this->cache->set($key, $value);

		$this->assertEquals($value, $this->cache->get($key));
	}

	public function testSetAndGetMultiple()
	{
		$keys = $values = $expected = [];

		for ($i = 0; $i < 5; $i++)
		{
			$keys[]   = String::random();
			$values[] = String::random();
		}

		foreach ($keys as $key => $key_name)
		{
			$value = $values[$key];
			$expected[strtoupper($key_name)] = $value;
			$this->cache->set($key_name, $value);
		}

		$this->assertEquals($expected, $this->cache->get($keys));
	}

	public function testDelete()
	{
		$key   = String::random();
		$value = String::random();

		$this->cache->set($key, $value);
		$this->cache->delete($key);

		$this->assertFalse($this->cache->get($key));
	}

	public function testIncrement()
	{
		$key = String::random();

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

?>
