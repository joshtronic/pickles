<?php

class API_PlaceholdIt_Test extends PHPUnit_Framework_TestCase
{
	private $placeholdit;

	public function setUp()
	{
		$this->placeholdit = new API_PlaceholdIt();
	}

	public function testInstantiateObject()
	{
		$this->assertInstanceOf('API_PlaceholdIt', $this->placeholdit);
	}

	public function testURL()
	{
		$expected = 'http://placehold.it/350x150.png/ffffff/000000&text=PICKLES+Rules%21';
		$url      = $this->placeholdit->url(350, 150, 'png', 'ffffff', '000000', 'PICKLES Rules!');
		$this->assertEquals($expected, $url);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Invalid format. Valid formats: gif, jpeg, jpg and png.
	 */
	public function testInvalidFormat()
	{
		$this->placeholdit->url(350, 150, 'invalid');
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage You must specify a background color if you wish to specify a foreground color.
	 */
	public function testForegroundNoBackground()
	{
		$this->placeholdit->url(350, 150, 'png', false, '000000');
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage The background color must be a 6 character hex code.
	 */
	public function testInvalidBackground()
	{
		$this->placeholdit->url(350, 150, 'png', 'fff');
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage The foreground color must be a 6 character hex code.
	 */
	public function testInvalidForeground()
	{
		$this->placeholdit->url(350, 150, 'png', 'ffffff', '000');
	}

	public function testIMG()
	{
		$expected = '<img src="http://placehold.it/350x150.png/ffffff/000000&text=PICKLES+Rules%21">';
		$url      = $this->placeholdit->img(350, 150, 'png', 'ffffff', '000000', 'PICKLES Rules!');
		$this->assertEquals($expected, $url);
	}
}

?>
