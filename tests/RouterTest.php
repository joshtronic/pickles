<?php

namespace Resources\v1
{
    class router extends \Pickles\Resource
    {

    }
}

namespace
{
    class RouterTest extends PHPUnit_Framework_TestCase
    {
        public function testServerError()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 500,
                    'message' => 'Undefined index: request',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'GET';

            new Pickles\Router();
        }

        public function testNotFound()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 404,
                    'message' => 'Not Found.',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/doesnotexist';

            new Pickles\Router();
        }

        // We're just testing that the class can be loaded, not that it will
        // work. That logic is off in ResourceTest
        public function testFoundWithUID()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 405,
                    'message' => 'Method not allowed.',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/router/1';

            new Pickles\Router();
        }
    }
}

