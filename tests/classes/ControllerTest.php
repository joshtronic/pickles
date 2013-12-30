<?php

class ControllerTest extends PHPUnit_Framework_TestCase
{
	private $config;

	public function setUp()
	{
		$this->config = Config::getInstance();
		$this->config->data['pickles']['disabled'] = false;
		$this->config->data['pickles']['profiler'] = false;
		$_SERVER['REQUEST_URI'] = '';

		if (!file_exists(SITE_MODULE_PATH))
		{
			mkdir(SITE_MODULE_PATH, 0644);
		}

		unlink(SITE_MODULE_PATH . 'testing.php');

		$_SERVER['HTTP_HOST']   = 'testsite.com';
		$_SERVER['REQUEST_URI'] = '/home';
		$_REQUEST['request']    = 'home';

		$module = '<?php class home extends Module { } ?>';

		file_put_contents(SITE_MODULE_PATH . 'home.php', $module);
	}

	public function testSiteDown()
	{
		$_SERVER['SERVER_NAME'] = 'Test Server';

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
		$_SERVER['REQUEST_URI'] = '/TESTING';
		$_REQUEST['request']    = 'TESTING';

		new Controller();

		$this->assertTrue(in_array('Location: /testing', xdebug_get_headers()));
	}

	/*
	public function testForceSecure()
	{
		$_SERVER['REQUEST_URI'] = '/secure';
		$_REQUEST['request']    = 'secure';

		$module = '
			<?php
			class secure extends Module
			{
				public $secure = true;
			}
			?>
		';

		file_put_contents(SITE_MODULE_PATH . 'secure.php', $module);

		new Controller();

		$this->assertTrue(in_array('Location: https://testsite.com/secure', xdebug_get_headers()));
	}

	public function testForceInsecure()
	{
		$_SERVER['HTTPS']       = 'on';
		$_SERVER['REQUEST_URI'] = '/insecure';
		$_REQUEST['request']    = 'insecure';

		$module = '
			<?php
			class insecure extends Module
			{
				public $secure = false;
			}
			?>
		';

		file_put_contents(SITE_MODULE_PATH . 'insecure.php', $module);

		new Controller();

		$this->assertTrue(in_array('Location: http://testsite.com/insecure', xdebug_get_headers()));
	}

	public function testNotAuthenticated()
	{
		$this->fail();
	}

	public function testNotAuthenticatedPOST()
	{
		$this->fail();
	}

	public function testAuthenticated()
	{
		$this->fail();
	}

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
