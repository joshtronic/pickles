<?php

class RouterTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = Config::getInstance();
        $this->config->data['pickles']['disabled']    = false;
        $this->config->data['pickles']['profiler']    = false;
        $this->config->data['security']['levels'][10] = 'USER';
        $this->config->data['security']['levels'][20] = 'ADMIN';

        setUpRequest('home');

        $module = '<?php class home extends Resource { }';

        file_put_contents(SITE_MODULE_PATH . 'home.php', $module);
    }

    public function testSiteDown()
    {
        $this->config->data['pickles']['disabled'] = true;

        $this->expectOutputRegex('/Test Server is currently down for maintenance/');

        new Router();
    }

    public function testCustomSiteDown()
    {
        $this->config->data['pickles']['disabled'] = true;

        file_put_contents(SITE_TEMPLATE_PATH . '__shared/maintenance.phtml', '<h1>Custom Down for Maintenance</h1>');

        new Router();

        $this->expectOutputRegex('/<h1>Custom Down for Maintenance<\/h1>/');
    }

    public function testAttributesInURI()
    {
        setUpRequest('home/id:123');

        new Router();

        $this->assertEquals(123, Browser::get('id'));

        setUpRequest('home/id:456/foo:bar');

        new Router();

        // Compensates for 2 empty template executions of the Router
        $this->expectOutputString('[][]');
        $this->assertEquals(456,   Browser::get('id'));
        $this->assertEquals('bar', Browser::get('foo'));
    }

    public function testUpperCaseURI()
    {
        setUpRequest('TESTING');

        new Router();

        $this->assertTrue(in_array('Location: /testing', xdebug_get_headers()));
    }

    public function testForceSecure()
    {
        setUpRequest('secure');

        $module = '<?php class secure extends Resource { public $secure = true; }';

        file_put_contents(SITE_MODULE_PATH . 'secure.php', $module);

        new Router();

        $this->assertTrue(in_array('Location: https://testsite.com/secure', xdebug_get_headers()));
    }

    public function testForceInsecure()
    {
        setUpRequest('insecure');
        $_SERVER['HTTPS'] = 'on';

        $module = '<?php class insecure extends Resource { public $secure = false; }';

        file_put_contents(SITE_MODULE_PATH . 'insecure.php', $module);

        new Router();

        $this->assertTrue(in_array('Location: http://testsite.com/insecure', xdebug_get_headers()));
    }

    public function testNotAuthenticated()
    {
        setUpRequest('notauth');

        $module = '<?php class notauth extends Resource { public $security = SECURITY_LEVEL_USER; }';

        file_put_contents(SITE_MODULE_PATH . 'notauth.php', $module);

        new Router();

        // Compensates for an empty template due to exit() being skipped
        $this->expectOutputString('[]');

        $this->assertTrue(in_array('Location: http://testsite.com/login', xdebug_get_headers()));
    }

    public function testValidRequestMethod()
    {
        setUpRequest('validrequestmethod');

        $module = '<?php class validrequestmethod extends Resource { '
                . 'public $method = "GET";'
                . 'public function __default() { return ["foo" => "bar"]; }'
                . '}';

        file_put_contents(SITE_MODULE_PATH . 'validrequestmethod.php', $module);

        new Router();

        $this->expectOutputString('{"foo":"bar"}');
    }

    public function testInvalidRequestMethod()
    {
        setUpRequest('invalidrequestmethod');

        $module = '<?php class invalidrequestmethod extends Resource { '
                . 'public $method = "POST";'
                . 'public function __default() { return ["foo" => "bar"]; }'
                . '}';

        file_put_contents(SITE_MODULE_PATH . 'invalidrequestmethod.php', $module);

        new Router();

        $this->expectOutputString('{"status":"error","message":"There was a problem with your request method."}');
    }

    public function testValidationErrors()
    {
        setUpRequest('validationerrors');

        $module = '<?php class validationerrors extends Resource { '
                . 'public $validate = ["test"];'
                . 'public function __default() { return ["foo" => "bar"]; }'
                . '}';

        file_put_contents(SITE_MODULE_PATH . 'validationerrors.php', $module);

        new Router();

        $this->expectOutputString('{"status":"error","message":"The test field is required."}');
    }

    public function testError404()
    {
        setUpRequest('fourohfour');

        new Router();

        $this->assertTrue(in_array('Status: 404 Not Found', xdebug_get_headers()));
        $this->expectOutputRegex('/<h1>Not Found<\/h1>/');
    }

    public function testCustomError404()
    {
        setUpRequest('customfourohfour');

        file_put_contents(SITE_TEMPLATE_PATH . '__shared/404.phtml', '<h1>Custom Not Found</h1>');

        new Router();

        $this->assertTrue(in_array('Status: 404 Not Found', xdebug_get_headers()));
        $this->expectOutputRegex('/<h1>Custom Not Found<\/h1>/');
    }

    public function testProfilerOutput()
    {
        $this->config->data['pickles']['profiler'] = true;

        $this->expectOutputRegex('/id="pickles-profiler"/');

        new Router();
    }

    public function testTwoValidTemplates()
    {
        $this->config->data['pickles']['profiler'] = true;

        setUpRequest('validtemplates');

        $module = '<?php class validtemplates extends Resource { }';

        file_put_contents(SITE_MODULE_PATH . 'validtemplates.php', $module);

        $child_template = SITE_TEMPLATE_PATH . 'validtemplates.phtml';
        file_put_contents($child_template, '<div>child template</div>');

        // Vim syntax highlighting borks unless ----v
        $child = '<?php require $this->template; ?' . '>' . "\n";

        $html = <<<HTML
<!doctype html>
<html>
    <body>
        <h1>parent template</h1>
        {$child}
    </body>
</html>
HTML;

        file_put_contents(SITE_TEMPLATE_PATH . '__shared/index.phtml', $html);

        new Router();

        $this->expectOutputRegex('/^<!doctype html>
<html>
<body>
<h1>parent template<\/h1>
<div>child template<\/div>
<\/body>
<\/html>.+<style>/');
    }
}

