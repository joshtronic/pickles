<?php

class API_Google_ProfanityTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider providerFormatPhoneNumber
	 */
	public function testCheck($a, $b)
	{
		$this->assertEquals($b, API_Google_Profanity::check($a));
	}

	public function providerFormatPhoneNumber()
	{
		return [
			['alpha',      false],
			['beta',       false],
			['joshtronic', false],
			['god',        false],
			['fck',        false],
			['fuck',       true],
			['shit',       true],
			['cocksucker', true],
			['cuntface',   false], // Unsure why not...
		];
	}
}

?>
