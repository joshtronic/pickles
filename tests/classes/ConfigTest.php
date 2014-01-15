<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
	private $config_file;
	private $config;

	public function setUp()
	{
		$this->config_file = SITE_PATH . 'config.php';
		$this->config      = Config::getInstance();
		$this->createConfigFile([]);

		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	private function createConfigFile($config)
	{
		$config = '<?php $config = ' . var_export($config, true) . '; ?>';

		file_put_contents($this->config_file, $config);
	}

	public function testConfigProperty()
	{
		$config = new Config();

		$this->assertTrue(PHPUnit_Framework_Assert::readAttribute($config, 'config'));
	}

	public function testInstanceOf()
	{
		$this->assertInstanceOf('Config', $this->config);
	}

	public function testUndefined()
	{
		$this->assertFalse($this->config->undefined);
	}

	public function testDefinedEnvironment()
	{
		$this->createConfigFile([
			'environment' => 'local',
		]);

		$config = new Config();

		$this->assertEquals('local', $config->environment);
	}

	public function testMultipleEnvironmentsByIP()
	{
		$this->createConfigFile([
			'environments' => [
				'local' => '127.0.0.1',
				'prod'  => '123.456.789.0',
			],
		]);

		$config = new Config();

		$this->assertEquals('local', $config->environment);
	}

	public function testMultipleEnvironmentsByRegex()
	{
		$this->createConfigFile([
			'environments' => [
				'local' => '/^local\.testsite\.com$/',
				'prod'  => '/^testsite\.com$/',
			],
		]);

		$config = new Config();

		$this->assertEquals('prod', $config->environment);
	}

	public function testCLIEnvironment()
	{
		unset($_SERVER['REQUEST_METHOD']);
		$_SERVER['argv'][1] = 'prod';

		$this->createConfigFile([
			'environments' => [
				'local' => '127.0.0.1',
				'prod'  => '123.456.789.0',
			],
		]);

		$config = new Config();

		$this->assertEquals('prod', $config->environment);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage You must pass an environment (e.g. php script.php <environment>)
	 */
	public function testCLIMissingEnvironment()
	{
		unset($_SERVER['REQUEST_METHOD']);
		$_SERVER['argc'] = 1;

		$this->createConfigFile(['environments' => []]);

		$config = new Config();
	}

	public function testProfiler()
	{
		$this->createConfigFile([
			'environment' => 'local',
			'pickles'     => ['profiler' => true],
		]);

		$config = new Config();

		$this->assertTrue($config->pickles['profiler']);
	}

	public function testProfilerArray()
	{
		$this->createConfigFile([
			'environment' => 'local',
			'pickles'     => ['profiler' => ['objects', 'timers']],
		]);

		$config = new Config();

		$this->assertEquals('objects,timers', $config->pickles['profiler']);
	}

	public function testProfilerForceTrue()
	{
		$this->createConfigFile([
			'environment' => 'local',
			'pickles'     => ['profiler' => ['unknown']],
		]);

		$config = new Config();

		$this->assertTrue($config->pickles['profiler']);
	}

	public function testSecurityConstant()
	{
		$this->createConfigFile([
			'environment' => 'local',
			'security'    => ['levels' => [10 => 'level']],
		]);

		$config = new Config();

		$this->assertEquals(10, SECURITY_LEVEL_USER);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage The constant SECURITY_LEVEL_LEVEL is already defined
	 */
	public function testSecurityConstantAlreadyDefined()
	{
		$this->createConfigFile([
			'environment' => 'local',
			'security'    => ['levels' => [10 => 'level']],
		]);

		$config = new Config();

		$this->assertEquals(10, SECURITY_LEVEL_USER);
	}

	// This test is just for coverage
	public function testConfigArrayMissing()
	{
		file_put_contents($this->config_file, '');
		new Config();
	}
}

?>
