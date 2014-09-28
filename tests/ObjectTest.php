<?php

class ObjectTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWithoutObjects()
    {
        $object = new Pickles\Object();

        $this->assertInstanceOf('Pickles\Config', PHPUnit_Framework_Assert::readAttribute($object, 'config'));
    }

    public function testConstructorWithObjects()
    {
        $object = new Pickles\Object('cache');

        $this->assertInstanceOf('Pickles\Cache', PHPUnit_Framework_Assert::readAttribute($object, 'cache'));
    }

    public function testGetInstanceWithoutClass()
    {
        $this->assertFalse(Pickles\Object::getInstance());
    }
}

