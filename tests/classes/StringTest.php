<?php

require_once 'classes/String.php';
require_once 'classes/Object.php';
require_once 'classes/API/Common.php';
require_once 'classes/API/Gravatar.php';

class StringTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider providerFormatPhoneNumber
	 */
	public function testFormatPhoneNumber($a, $b)
	{
		$this->assertEquals(String::formatPhoneNumber($a), $b);
	}

	public function providerFormatPhoneNumber()
	{
		return array(
			array('1234567890',            '123-456-7890'),
			array('123 456 7890',          '123-456-7890'),
			array('123.456.7890',          '123-456-7890'),
			array('123_456_7890',          '123-456-7890'),
			array('1234567890',            '123-456-7890'),
			array('1234-56-7890',          '123-456-7890'),
			array('(123) 456-7890',        '123-456-7890'),
			array('1234567890 x1000',      '123-456-7890x1000'),
			array('(123) 456-7890_x10.00', '123-456-7890x1000'),
		);
	}

	/**
	 * @dataProvider providerGenerateGravatarHash
	 */
	public function testGenerateGravatarHash($a, $b)
	{
		$this->assertEquals(String::generateGravatarHash($a), $b);
	}

	public function providerGenerateGravatarHash()
	{
		return array(
			array('foo@bar.com', 'f3ada405ce890b6f8204094deb12d8a8'),
			array('FOO@BAR.COM', 'f3ada405ce890b6f8204094deb12d8a8'),
		);
	}

	public function testIsEmpty()
	{
		$this->assertTrue(String::isEmpty(''));
		$this->assertTrue(String::isEmpty(' '));
		$this->assertTrue(String::isEmpty(false));
		$this->assertTrue(String::isEmpty(null));
		$this->assertTrue(String::isEmpty(true, false));

		$this->assertFalse(String::isEmpty(0));
		$this->assertFalse(String::isEmpty('foo'));
		$this->assertFalse(String::isEmpty(' bar '));
		$this->assertFalse(String::isEmpty(true));
	}

	public function testRandom()
	{
		$this->assertEquals(strlen(String::random()),   8);
		$this->assertEquals(strlen(String::random(16)), 16);

		$this->assertEquals(preg_match('/[a-z0-9]/', String::random(32, true,  true)),  1);
		$this->assertEquals(preg_match('/[a-z]/',    String::random(32, true,  false)), 1);
		$this->assertEquals(preg_match('/[0-9]/',    String::random(32, false, true)),  1);

		$this->assertEquals(preg_match('/[0-9]/',    String::random(32, true,  false)), 0);
		$this->assertEquals(preg_match('/[a-z]/',    String::random(32, false, true)),  0);
		$this->assertEquals(preg_match('/[a-z0-9]/', String::random(32, false, false)), 0);
	}

	/**
	 * @dataProvider providerTruncate
	 */
	public function testTruncate($a, $b, $c, $d)
	{
		$this->assertEquals(String::truncate($a, $b, $c), $d);
	}

	public function providerTruncate()
	{
		return array(
			array('foo bar', 3, true,  '<span title="foo bar">foo&hellip;</span>'),
			array('foo bar', 3, false, 'foo...'),
			array('foo bar', 7, true,  'foo bar'),
			array('foo bar', 8, true,  'foo bar'),
		);
	}

	/**
	 * @dataProvider providerUpperWords
	 */
	public function testUpperWords($a, $b)
	{
		$this->assertEquals(String::upperWords($a), $b);
	}

	public function providerUpperWords()
	{
		return array(
			array('foo bar',     'Foo Bar'),
			array('FOO BAR',     'Foo Bar'),
			array('fOO bAR',     'Foo Bar'),
			array('foo@bar.com', 'foo@bar.com'),
			array('FOO@BAR.COM', 'FOO@BAR.COM'),
		);
	}
}

?>
