<?php

class MockUserModel extends Model
{
	protected $table = 'users';
}

class SecurityTest extends PHPUnit_Framework_TestCase
{
	public function testGenerateHashWithDefaultSalts()
	{
		$this->assertEquals(
			'4940e793006aa897db22751bba80dff4cb6a3e08',
			Security::generateHash('source')
		);
	}

	public function testGenerateHashWithCustomSalts()
	{
		$config = Config::getInstance();
		$config->data['security']['salt'] = 'salt';

		$this->assertEquals(
			'4eac88c934c33cfa9a80c0b2eb322f23ac3b13c5',
			Security::generateHash('source')
		);
	}

	public function testGenerateSHA256Hash()
	{
		$this->assertEquals(
			'3d04f805aff4838ecaf98c7260a813fffd2b7a8a7f957add8018908a1bbdad04',
			Security::generateSHA256Hash('source', 'salt')
		);
	}

	public function testLogin()
	{
		$this->assertTrue(Security::login(1, 10, 'USER'));
		$this->assertTrue(isset($_SESSION['__pickles']['security']));
	}

	public function testLoginNoSession()
	{
		session_destroy();
		$this->assertFalse(Security::login(1, 10, 'USER'));
	}

	public function testLogout()
	{
		session_start();
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::logout());
		$this->assertFalse(isset($_SESSION['__pickles']['security']));
	}

	public function testIsLevel()
	{
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::isLevel(SECURITY_LEVEL_USER));
	}

	public function testIsLevelArray()
	{
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::isLevel([SECURITY_LEVEL_USER, SECURITY_LEVEL_ADMIN]));
	}

	public function testHasLevel()
	{
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::hasLevel(SECURITY_LEVEL_USER));
	}

	public function testHasLevelArray()
	{
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::hasLevel([SECURITY_LEVEL_USER, SECURITY_LEVEL_ADMIN]));
	}

	public function testBetweenLevel()
	{
		Security::login(1, 10, 'USER');

		$this->assertTrue(Security::betweenLevel(SECURITY_LEVEL_USER, SECURITY_LEVEL_ADMIN));
	}

	public function testTokenMismatch()
	{
		Security::login(1, 10, 'USER');

		$_SESSION['__pickles']['security']['token'] = 'foo';
		$_COOKIE['pickles_security_token']          = 'bar';

		$this->assertFalse(Security::isLevel(SECURITY_LEVEL_USER));
	}

	public function testIsLevelDB()
	{
		$config = Config::getInstance();

		$config->data = [
			'pickles' => [
				'datasource' => 'mysql',
				'cache'      => 'memcache',
			],
			'datasources' => [
				'mysql' => [
					'type'     => 'mysql',
					'driver'   => 'pdo_mysql',
					'hostname' => 'localhost',
					'username' => '',
					'password' => '',
					'database' => 'test',
					'cache'    => true,
				],
				'memcache' => [
					'type'      => 'memcache',
					'hostname'  => 'localhost',
					'port'      => 11211,
					'namespace' => '',
				],
			],
			'security' => ['model' => 'MockUserModel'],
		];

		$model = new MockUserModel();
		$model->record['username'] = 'pickles';
		$model->commit();

		setUpConfig([

		]);

		new Config();

		Security::login(1, 10, 'USER');

		unset(
			$_SESSION['__pickles']['security']['token'],
			$_COOKIE['pickles_security_token'],
			$_SESSION['__pickles']['security']['level']
		);

		$this->assertFalse(Security::isLevel([SECURITY_LEVEL_USER, SECURITY_LEVEL_ADMIN]));
	}
}

?>
