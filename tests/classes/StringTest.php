<?php

require_once 'classes/String.php';

class StringTest extends PHPUnit_Framework_TestCase
{
	public function testUpperWords()
	{
		$this->assertEquals(String::upperWords('foo@bar.com'), 'foo@bar.com');
		$this->assertEquals(String::upperWords('FOO@BAR.COM'), 'FOO@BAR.COM');
		$this->assertEquals(String::upperWords('foo bar'),     'Foo Bar');
		$this->assertEquals(String::upperWords('FOO BAR'),     'Foo Bar');
		$this->assertEquals(String::upperWords('fOO bAR'),     'Foo Bar');
	}
}

?>
