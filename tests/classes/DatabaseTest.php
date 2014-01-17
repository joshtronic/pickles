<?php

class DatabaseTest extends PHPUnit_Framework_TestCase
{
	public function testGetInstanceFalse()
	{
		$this->assertFalse(Database::getInstance());
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage The specified datasource is not defined in the config.
	 */
	public function testGetInstanceDatasourceNotDefined()
	{
		$config = Config::getInstance();
		$config->data['pickles']['datasource'] = 'mysql';
		Database::getInstance();
	}

//	/**
//	 * @expectedException        Exception
//	 * @expectedExceptionMessage The specified datasource lacks a driver.
//	 */
//	public function testGetInstanceDatasourceLacksDriver()
//	{
//		$config = Config::getInstance();
//		$config->data['datasources'] = [
//			'mysql' => [
//				'type' => 'mysql',
//			],
//		];
//		$this->assertInstanceOf('Database', Database::getInstance());
//	}
//
//	public function testGetInstanceDatasourcesArray()
//	{
//		$config = Config::getInstance();
//		$config->data['datasources'] = [
//			'mysql' => [
//				'type'   => 'mysql',
//				'driver' => 'pdo_mysql',
//			],
//		];
//		$this->assertInstanceOf('Database', Database::getInstance());
//	}

//	public function testGetInstanceFirstDatasource()
//	{
//		$config = Config::getInstance();
//		$config->data['pickles']['datasource'] = false;
//
//		//$this->assertInstanceOf('Database', Database::getInstance());
//	}
}

?>
