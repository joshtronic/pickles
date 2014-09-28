<?php

class RouterTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = Pickles\Config::getInstance();
        $this->config->data['pickles']['disabled'] = false;
        $this->config->data['pickles']['profiler'] = false;

        setUpRequest('home');

        $module = '<?php class home extends Resource { }';

        file_put_contents(SITE_MODULE_PATH . 'home.php', $module);
    }

    public function testForceSecure()
    {
        setUpRequest('secure');

        $module = '<?php class secure extends Resource { public $secure = true; }';

        file_put_contents(SITE_MODULE_PATH . 'secure.php', $module);

        new Pickles\Router();

        $this->assertTrue(in_array('Location: https://testsite.com/secure', xdebug_get_headers()));
    }

    public function testForceInsecure()
    {
        setUpRequest('insecure');
        $_SERVER['HTTPS'] = 'on';

        $module = '<?php class insecure extends Resource { public $secure = false; }';

        file_put_contents(SITE_MODULE_PATH . 'insecure.php', $module);

        new Pickles\Router();

        $this->assertTrue(in_array('Location: http://testsite.com/insecure', xdebug_get_headers()));
    }

    public function testValidationErrors()
    {
        setUpRequest('validationerrors');

        $module = '<?php class validationerrors extends Resource { '
                . 'public $validate = ["test"];'
                . 'public function __default() { return ["foo" => "bar"]; }'
                . '}';

        file_put_contents(SITE_MODULE_PATH . 'validationerrors.php', $module);

        new Pickles\Router();

        $this->expectOutputString('{"status":"error","message":"The test field is required."}');
    }
}

