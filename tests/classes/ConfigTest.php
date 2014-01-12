<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
	public function testConfigProperty()
	{
		$config = new Config();

		$this->assertTrue(PHPUnit_Framework_Assert::readAttribute($config, 'config'));
	}
}

?>
