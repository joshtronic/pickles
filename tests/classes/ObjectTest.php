<?php

class ObjectTest extends PHPUnit_Framework_TestCase
{
	public function testConstructorWithoutObjects()
	{
		$object = new Object();

		$this->assertInstanceOf('Config', PHPUnit_Framework_Assert::readAttribute($object, 'config'));
	}

	public function testConstructorWithObjects()
	{
		$object = new Object('cache');

		$this->assertInstanceOf('Cache', PHPUnit_Framework_Assert::readAttribute($object, 'cache'));
	}

	public function testGetInstanceWithoutClass()
	{
		$this->assertFalse(Object::getInstance());
	}
}

?>
