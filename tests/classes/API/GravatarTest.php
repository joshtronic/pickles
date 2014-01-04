<?php

class API_Gravatar_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider providerHash
	 */
	public function testHash($a, $b)
	{
		$this->assertEquals($b, API_Gravatar::hash($a));
	}

	public function providerHash()
	{
		return [
			['foo@bar.com', 'f3ada405ce890b6f8204094deb12d8a8'],
			['FOO@BAR.COM', 'f3ada405ce890b6f8204094deb12d8a8'],
		];
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid email address.
	 */
	public function testImgInvalidEmail()
	{
		API_Gravatar::img('invalidemail');
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid size parameter, expecting an integer between 1 and 2048.
	 */
	public function testImgInvalidSize()
	{
		API_Gravatar::img('foo@bar.com', 2050);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid default parameter, expecting gravatar, 404, mm, identicon, monsterid, wavatar, retro, blank or a valid URL.
	 */
	public function testImgInvalidDefault()
	{
		API_Gravatar::img('foo@bar.com', 80, 'invalid');
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid rating perameter, expecting g, pg, r or x.
	 */
	public function testImgInvalidRating()
	{
		API_Gravatar::img('foo@bar.com', 80, 'gravatar', 'sexytime');
	}

	public function testImg()
	{
		$this->assertEquals(
			'<img src="http://www.gravatar.com/avatar/f3ada405ce890b6f8204094deb12d8a8?s=80&d=&r=g">',
			API_Gravatar::img('foo@bar.com')
		);
	}
}

?>
