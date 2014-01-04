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
}

?>
