<?php

class API_AYAH extends API_Common
{
	public static function getHTML()
	{
		$config = Config::getInstance();

		if (!$config->api['ayah'])
		{
			throw new Exception('Missing API configuration.');
		}

		$ayah = new AYAH($config->api['ayah']);

		return $ayah->getPublisherHTML();
	}

	public static function isHuman()
	{
		$config = Config::getInstance();

		if (!$config->api['ayah'])
		{
			throw new Exception('Missing API configuration.');
		}

		$ayah = new AYAH($config->api['ayah']);

		return $ayah->scoreResult();
	}
}

?>
