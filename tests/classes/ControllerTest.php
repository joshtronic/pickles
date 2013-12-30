<?php

class ControllerTest extends PHPUnit_Framework_TestCase
{
	private $config;

	public function setUp()
	{
		$this->config = Config::getInstance();
		$this->config->data['pickles']['disabled']    = false;
		$this->config->data['pickles']['profiler']    = false;
		$this->config->data['security']['levels'][10] = 'USER';

		setUpRequest('home');

		$module = '<?php class home extends Module { } ?>';

		file_put_contents(SITE_MODULE_PATH . 'home.php', $module);
	}

	public function testSiteDown()
	{
		$this->config->data['pickles']['disabled'] = true;

		$this->expectOutputRegex('/Test Server is currently down for maintenance/');

		new Controller();
	}

	/*
	public function testCustomSiteDown()
	{
		$this->fail();
	}

	public function testAttributesInURI()
	{
		/testing/id:123/foo:bar
		$this->fail();
	}
	*/

	public function testUpperCaseURI()
	{
		setUpRequest('TESTING');

		new Controller();

		$this->assertTrue(in_array('Location: /testing', xdebug_get_headers()));
	}

	public function testForceSecure()
	{
		setUpRequest('secure');

		$module = '<?php class secure extends Module { public $secure = true; } ?>';

		file_put_contents(SITE_MODULE_PATH . 'secure.php', $module);

		new Controller();

		$this->assertTrue(in_array('Location: https://testsite.com/secure', xdebug_get_headers()));
	}

	public function testForceInsecure()
	{
		setUpRequest('insecure');
		$_SERVER['HTTPS'] = 'on';

		$module = '<?php class insecure extends Module { public $secure = false; } ?>';

		file_put_contents(SITE_MODULE_PATH . 'insecure.php', $module);

		new Controller();

		$this->assertTrue(in_array('Location: http://testsite.com/insecure', xdebug_get_headers()));
	}

	public function testNotAuthenticated()
	{
		setUpRequest('notauth');

		$module = '<?php class notauth extends Module { public $security = SECURITY_LEVEL_USER; } ?>';

		file_put_contents(SITE_MODULE_PATH . 'notauth.php', $module);

		new Controller();

		$this->assertTrue(in_array('Location: http://testsite.com/login', xdebug_get_headers()));
	}

	public function testNotAuthenticatedPOST()
	{
		setUpRequest('notauthpost', 'POST');

		$module = '<?php class notauthpost extends Module { public $security = SECURITY_LEVEL_USER; } ?>';

		file_put_contents(SITE_MODULE_PATH . 'notauthpost.php', $module);

		new Controller();

		$this->expectOutputRegex('/You are not properly authenticated/');
	}

	public function testAuthenticated()
	{
		setUpRequest('auth');

		$module = '<?php class auth extends Module { '
				. 'public $security = SECURITY_LEVEL_USER;'
				. 'public function __default() { return ["foo" => "bar"]; }'
				. '} ?>';

		file_put_contents(SITE_MODULE_PATH . 'auth.php', $module);

		session_start();
		Security::login(1, 10, 'USER');
		new Controller();

		$this->expectOutputString('{"foo":"bar"}');
	}

	/*
	public function testHasLevelAccess()
	{
		$this->fail();
	}

	public function testIsLevelAccess()
	{
		$this->fail();
	}

	public function testRoleDefaultMethod()
	{
		$this->fail();
	}

	public function testBadRequestMethod()
	{
		$this->fail();
	}

	// @todo Reuse one of the Module tests?
	public function testValidationErrors()
	{
		$this->fail();
	}

	public function testError404()
	{
		$this->fail();
	}

	public function testCustomError404()
	{
		$this->fail();
	}

	// @todo Reuse one of the Display tests?
	public function testOutput()
	{
		$this->fail();
	}
	*/

	public function testProfilerOutput()
	{
		$this->config->data['pickles']['profiler'] = true;

		$this->expectOutputRegex('/id="pickles-profiler"/');

		new Controller();
	}
}

?>
