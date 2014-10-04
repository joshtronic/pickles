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
            $_SERVER['SERVER_NAME']    = '127.0.0.1';

            file_put_contents('/tmp/pickles.php', '<?php
                $config = [
                    "environments" => [
                        "local"      => "127.0.0.1",
                        "production" => "123.456.789.0",
                    ],
                    "pickles" => [
                        "namespace" => "",
                    ],
                    "datasources" => [],
                ];
            ');

            Pickles\Config::getInstance('/tmp/pickles.php');

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
            Pickles\Object::$instances = [];

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['SERVER_NAME']    = '127.0.0.1';

            file_put_contents('/tmp/pickles.php', '<?php
                $config = [
                    "environments" => [
                        "local"      => "127.0.0.1",
                        "production" => "123.456.789.0",
                    ],
                    "pickles" => [
                        "namespace" => "",
                    ],
                    "datasources" => [],
                ];
            ');

            Pickles\Config::getInstance('/tmp/pickles.php');

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

