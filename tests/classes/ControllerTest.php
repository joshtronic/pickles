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
	*/

	public function testAttributesInURI()
	{
		setUpRequest('home/id:123');

		new Controller();

		$this->assertEquals(123, Browser::get('id'));

		setUpRequest('home/id:456/foo:bar');

		new Controller();

		// Compensates for 2 empty template executions of the Controller
		$this->expectOutputString('[][]');
		$this->assertEquals(456,   Browser::get('id'));
		$this->assertEquals('bar', Browser::get('foo'));
	}

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

		Security::login(1, 10, 'USER');
		new Controller();

		$this->expectOutputString('{"foo":"bar"}');
	}

	public function testRoleDefaultMethod()
	{
		setUpRequest('rolemethod');

		$module = '<?php class rolemethod extends Module { '
				. 'public $security = SECURITY_LEVEL_USER;'
				. 'public function __default() { return ["foo" => "bar"]; }'
				. 'public function __default_USER() { return ["user" => "me"]; }'
				. '} ?>';

		file_put_contents(SITE_MODULE_PATH . 'rolemethod.php', $module);

		Security::login(1, 10, 'USER');
		new Controller();

		$this->expectOutputString('{"user":"me"}');
	}

	public function testBadRequestMethod()
	{
		setUpRequest('requestmethod');

		$module = '<?php class requestmethod extends Module { '
				. 'public $method = "POST";'
				. 'public function __default() { return ["foo" => "bar"]; }'
				. '} ?>';

		file_put_contents(SITE_MODULE_PATH . 'requestmethod.php', $module);

		new Controller();

		$this->expectOutputString('{"status":"error","message":"There was a problem with your request method."}');
	}

	public function testValidationErrors()
	{
		setUpRequest('validationerrors');

		$module = '<?php class validationerrors extends Module { '
				. 'protected $validate = ["test"];'
				. 'public function __default() { return ["foo" => "bar"]; }'
				. '} ?>';

		file_put_contents(SITE_MODULE_PATH . 'validationerrors.php', $module);

		new Controller();

		$this->expectOutputString('{"status":"error","message":"The test field is required."}');
	}

	public function testError404()
	{
		setUpRequest('fourohfour');

		new Controller();

		$this->assertTrue(in_array('Status: 404 Not Found', xdebug_get_headers()));
		$this->expectOutputRegex('/<h1>Not Found<\/h1>/');
	}

	public function testCustomError404()
	{
		setUpRequest('customfourohfour');

		file_put_contents(SITE_TEMPLATE_PATH . '__shared/404.phtml', '<h1>Custom Not Found</h1>');

		new Controller();

		$this->assertTrue(in_array('Status: 404 Not Found', xdebug_get_headers()));
		$this->expectOutputRegex('/<h1>Custom Not Found<\/h1>/');
	}

	public function testProfilerOutput()
	{
		$this->config->data['pickles']['profiler'] = true;

		$this->expectOutputRegex('/id="pickles-profiler"/');

		new Controller();
	}
}

?>
