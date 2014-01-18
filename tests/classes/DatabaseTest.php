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
		$config->data['pickles']['datasource'] = 'bad';
		Database::getInstance();
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage The specified datasource lacks a driver.
	 */
	public function testGetInstanceDatasourceLacksDriver()
	{
		$config = Config::getInstance();
		$config->data['datasources'] = [
			'bad' => [
				'type' => 'mysql',
			],
		];
		$this->assertInstanceOf('Database', Database::getInstance());
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage There was an error loading the database configuration.
	 */
	public function testOpenConfigError()
	{
		$config = Config::getInstance();
		$config->data['datasources'] = [
			'bad' => [
				'type'     => 'mysql',
				'driver'   => 'pdo_mysql',
				'database' => 'test',
			],
		];
		$db = Database::getInstance();
		$db->open();
	}

	public function testGetInstanceDatasourcesArray()
	{
		$config = Config::getInstance();
		$config->data['datasources'] = [
			'mysql' => [
				'type'     => 'mysql',
				'driver'   => 'pdo_mysql',
				'hostname' => 'localhost',
				'username' => '',
				'password' => '',
				'database' => 'test',
			],
		];
		$this->assertInstanceOf('Database', Database::getInstance());
	}

	// Also tests the datasource being missing and selecting the first one
	public function testGetInstanceMySQL()
	{
		$config = Config::getInstance();
		unset($config->data['pickles']['datasource']);
		$this->assertInstanceOf('Database', Database::getInstance());
	}

	public function testOpenMySQL()
	{
		$config = Config::getInstance();
		$config->data['pickles']['datasource'] = 'mysql';
		$db = Database::getInstance();
		$db->open();
	}

	public function testExecute()
	{
		$db = Database::getInstance();
		$this->assertEquals('0', $db->execute('SHOW TABLES'));
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage No query to execute.
	 */
	public function testExecuteNoQuery()
	{
		$db = Database::getInstance();
		$db->execute(' ');
	}

	public function testFetch()
	{
		$config = Config::getInstance();
		$config->data['pickles']['logging'] = true;
		$config->data['pickles']['profiler'] = true;
		$db = Database::getInstance();
		$this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != ?', ['0']));
	}

	public function testExplainNoInput()
	{
		$config = Config::getInstance();
		$db = Database::getInstance();
		$this->assertEquals([], $db->fetch('SELECT * FROM pickles WHERE id != 0'));
	}

	public function testSlowQuery()
	{
		$db = Database::getInstance();
		$this->assertEquals('0', $db->execute('SHOW DATABASES', null, true));
	}

	public function testCloseMySQL()
	{
		$db = Database::getInstance();
		$db->open();

		$this->assertTrue($db->close());
	}

	public function testGetInstancePostgreSQL()
	{
		$config = Config::getInstance();
		$config->data['pickles']['datasource'] = 'pgsql';
		$config->data['datasources']['pgsql'] = [
			'type'     => 'pgsql',
			'driver'   => 'pdo_pgsql',
			'hostname' => 'localhost',
			'username' => '',
			'password' => '',
			'database' => 'test',
		];
		$this->assertInstanceOf('Database', Database::getInstance());
	}

	/**
	 * @expectedException        PDOException
	 * @expectedExceptionMessage SQLSTATE[08006] [7] could not connect to server
	 * @expectedExceptionCode    7
	 */
	public function testOpenPostgreSQL()
	{
		// Also throws an exception since I don't have PostgreSQL set up
		$config = Config::getInstance();
		$db = Database::getInstance();
		$db->open();
	}

	public function testGetInstanceSQLite()
	{
		$config = Config::getInstance();
		$config->data['pickles']['datasource'] = 'sqlite';
		$config->data['datasources']['sqlite'] = [
			'type'     => 'sqlite',
			'driver'   => 'pdo_sqlite',
			'hostname' => 'localhost',
			'username' => '',
			'password' => '',
			'database' => 'test',
		];
		$this->assertInstanceOf('Database', Database::getInstance());
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage Datasource driver "pdo_invalid" is invalid
	 */
	public function testGetInstanceInvalidDriver()
	{
		$config = Config::getInstance();
		$config->data['pickles']['datasource'] = 'invalid';
		$config->data['datasources']['invalid'] = [
			'type'     => 'invalid',
			'driver'   => 'pdo_invalid',
			'hostname' => 'localhost',
			'username' => '',
			'password' => '',
			'database' => 'test',
		];
		Database::getInstance();
	}
}

?>
