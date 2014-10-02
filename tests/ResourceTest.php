<?php

namespace Resources\v1
{
    class resource extends \Pickles\Resource
    {
        public $https = [
            'POST' => true,
        ];

        public $auth = [
            'DELETE' => true,
        ];

        public $filter = [
            'GET' => [
                'foo' => 'trim',
                'bar' => 'password_hash',
            ],
        ];

        public $validate = [
            'GET' => [
                'missing',
                'isBoolean'        => ['filter:boolean'  => 'Error'],
                'isNotBoolean'     => ['filter:boolean'  => 'Error'],
                'isEmail'          => ['filter:email'    => 'Error'],
                'isNotEmail'       => ['filter:email'    => 'Error'],
                'isFloat'          => ['filter:float'    => 'Error'],
                'isNotFloat'       => ['filter:float'    => 'Error'],
                'isInt'            => ['filter:int'      => 'Error'],
                'isNotInt'         => ['filter:int'      => 'Error'],
                'isIP'             => ['filter:ip'       => 'Error'],
                'isNotIP'          => ['filter:ip'       => 'Error'],
                'isURL'            => ['filter:url'      => 'Error'],
                'isNotURL'         => ['filter:url'      => 'Error'],
                'invalidRule'      => ['filter'          => 'Error'],
                'lessThan'         => ['length:<:10'     => 'Error'],
                'lessThanEqual'    => ['length:<=:10'    => 'Error'],
                'equal'            => ['length:==:10'    => 'Error'],
                'notEqual'         => ['length:!=:10'    => 'Error'],
                'greaterThan'      => ['length:>=:10'    => 'Error'],
                'greaterThanEqual' => ['length:>:10'     => 'Error'],
                'greaterLessThan'  => ['length:><:10'    => 'Error'],
                'regex'            => ['regex:/[a-z]+/'  => 'Error'],
            ],
        ];

        public function GET()
        {

        }

        public function PUT()
        {
            return ['foo' => 'bar'];
        }

        public function ERROR()
        {
            throw new \Exception('Error');
        }
    }
}

namespace
{
    class ResourceTest extends PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['SERVER_NAME']    = '127.0.0.1';

            file_put_contents('/tmp/pickles.php', '<?php
                $config = [
                    "environments" => [
                        "local"      => "127.0.0.1",
                        "production" => "123.456.789.0",
                    ],
                    "pickles" => [
                        "namespace"  => "",
                        "datasource" => "mysql",
                    ],
                    "datasources" => [
                        "mysql" => [
                            "driver" => "pdo_mysql",
                        ],
                    ],
                ];
            ');

            Pickles\Config::getInstance('/tmp/pickles.php');
        }

        public function testFilterAndValidate()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 400,
                    'message' => 'Missing or invalid parameters.',
                    'errors' => [
                        'missing'         => ['The missing parameter is required.'],
                        'isNotBoolean'    => ['Error'],
                        'isNotEmail'      => ['Error'],
                        'isNotFloat'      => ['Error'],
                        'isNotInt'        => ['Error'],
                        'isNotIP'         => ['Error'],
                        'isNotURL'        => ['Error'],
                        'invalidRule'     => ['Invalid filter, expecting boolean, email, float, int, ip or url.'],
                        'greaterLessThan' => ['Invalid operator, expecting <, <=, ==, !=, >= or >.'],
                        'regex'           => ['Error'],
                    ],
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/resource/1';
            $_GET = [
                'foo'              => '     bar     ',
                'bar'              => 'unencrypted',
                'isBoolean'        => true,
                'isNotBoolean'     => 'invalid',
                'isEmail'          => 'foo@bar.com',
                'isNotEmail'       => 'nope',
                'isFloat'          => 1.234567890,
                'isNotFloat'       => 'five',
                'isInt'            => 22381,
                'isNotInt'         => 'pretzel',
                'isIP'             => '127.0.0.1',
                'isNotIP'          => 'home',
                'isURL'            => 'http://joshtronic.com',
                'isNotURL'         => 'doubleUdoubleUdoubleUdot',
                'invalidRule'      => 'invalid',
                'lessThan'         => '...',
                'lessThanEqual'    => '.......',
                'equal'            => '..........',
                'notEqual'         => '.......',
                'greaterThan'      => '............',
                'greaterThanEqual' => '............',
                'greaterLessThan'  => '......',
                'regex'            => 'abc',
            ];

            new Pickles\Router();

            $this->assertEquals('bar', $_GET['foo']);
            $this->assertFalse('unencrypted' == $_GET['bar']);
        }

        public function testHTTPS()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 400,
                    'message' => 'HTTPS is required.',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }

        public function testPUT()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 200,
                    'message' => 'OK',
                ],
                'response' => [
                    'foo' => 'bar',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'PUT';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }

        public function testMisconfiguredAuth()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 401,
                    'message' => 'Authentication is not configured properly.',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'DELETE';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }

        public function testMethodNotAllowed()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 405,
                    'message' => 'Method not allowed.',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'NOPE';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }

        public function testLowErrorCode()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 500,
                    'message' => 'Error',
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'ERROR';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }

        public function testProfiler()
        {
            $this->expectOutputRegex('/"profiler":{/');

            file_put_contents('/tmp/pickles.php', '<?php
                $config = [
                    "environments" => [
                        "local"      => "127.0.0.1",
                        "production" => "123.456.789.0",
                    ],
                    "pickles" => [
                        "namespace"  => "",
                        "datasource" => "mysql",
                        "profiler"   => true,
                    ],
                    "datasources" => [
                        "mysql" => [
                            "driver" => "pdo_mysql",
                        ],
                    ],
                ];
            ');

            Pickles\Config::getInstance('/tmp/pickles.php');

            $_SERVER['REQUEST_METHOD'] = 'PUT';
            $_REQUEST['request'] = 'v1/resource/1';

            new Pickles\Router();
        }
    }
}

