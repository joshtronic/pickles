<?php

class API_AYAHTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Missing API configuration
	 */
	public function testGetHTMLMissingConfig()
	{
		API_AYAH::getHTML();
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Missing API configuration
	 */
	public function testIsHumanMissingConfig()
	{
		API_AYAH::isHuman();
	}

	public function testGetHTML()
	{
		$config = Config::getInstance();
		$config->data['api']['ayah'] = [
			'publisher_key' => '01f70454bada303692be5f36a8fd104eba8b00dd',
			'scoring_key'   => '80cc3f9c6e1da29369c238d55bd8528a968473ad',
		];

		$this->assertRegExp('/<div id=\'AYAH\'><\/div><script src=\'https:\/\/ws.areyouahuman.com\/ws\/script\/[a-z0-9]{40}\/[a-zA-Z0-9]{45}\' type=\'text\/javascript\' language=\'JavaScript\'><\/script>/', API_AYAH::getHTML());
	}

	public function testIsNotHuman()
	{
		// Unfortunately there's no way to test a true response (mock maybe?)
		$this->assertFalse(API_AYAH::isHuman());
	}
}

?>
