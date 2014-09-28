<?php

namespace Resources\v1
{
    class resource extends \Pickles\Resource
    {

    }
}

namespace
{
    class RouterTest extends PHPUnit_Framework_TestCase
    {
        public function testServerError()
        {
            $this->expectOutputRegex('/{"status":500,"message":"Undefined index: request"}/');

            $_SERVER['REQUEST_METHOD'] = 'GET';

            new Pickles\Router();
        }

        public function testNotFound()
        {
            $this->expectOutputRegex('/{"status":404,"message":"Not Found."}/');

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/test';

            new Pickles\Router();
        }

        // We're just testing that the class can be loaded, not that it will
        // work. That logic is off in ResourceTest
        public function testFound()
        {
            $this->expectOutputRegex('/{"status":405,"message":"Method not allowed."}/');

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }
    }
}

