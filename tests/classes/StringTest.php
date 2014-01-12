<?php

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
		return [
			['1234567890',            '123-456-7890'],
			['123 456 7890',          '123-456-7890'],
			['123.456.7890',          '123-456-7890'],
			['123_456_7890',          '123-456-7890'],
			['1234567890',            '123-456-7890'],
			['1234-56-7890',          '123-456-7890'],
			['(123) 456-7890',        '123-456-7890'],
			['1234567890 x1000',      '123-456-7890x1000'],
			['(123) 456-7890_x10.00', '123-456-7890x1000'],
		];
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
		return [
			['foo@bar.com', 'f3ada405ce890b6f8204094deb12d8a8'],
			['FOO@BAR.COM', 'f3ada405ce890b6f8204094deb12d8a8'],
		];
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

	public function testRandomSimilarFalse()
	{
		$this->assertRegExp('/[a-hj-np-z2-9]{8}/', String::random(8, true, true, false));
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
		return [
			['foo bar', 3, true,  '<span title="foo bar">foo&hellip;</span>'],
			['foo bar', 3, false, 'foo...'],
			['foo bar', 7, true,  'foo bar'],
			['foo bar', 8, true,  'foo bar'],
		];
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
		return [
			['foo bar',     'Foo Bar'],
			['FOO BAR',     'Foo Bar'],
			['fOO bAR',     'Foo Bar'],
			['foo@bar.com', 'foo@bar.com'],
			['FOO@BAR.COM', 'FOO@BAR.COM'],
		];
	}

	/**
	 * @dataProvider providerGenerateSlug
	 */
	public function testGenerateSlug($a, $b)
	{
		$this->assertEquals($b, String::generateSlug($a));
	}

	public function providerGenerateSlug()
	{
		return [
			['TEST STRING',    'test-string'],
			['Test String',    'test-string'],
			['TEST  STRING',   'test-string'],
			['#! Test String', 'test-string'],
			['-test--string-', 'test-string'],
		];
	}

	public function testPluralize()
	{
		$this->assertEquals('test',    String::pluralize('test', 1, false));
		$this->assertEquals('1 test',  String::pluralize('test', 1, true));
		$this->assertEquals('tests',   String::pluralize('test', 2, false));
		$this->assertEquals('2 tests', String::pluralize('test', 2, true));
	}
}

?>
