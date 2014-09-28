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
                'isBoolean'    => ['filter:boolean' => 'Error'],
                'isNotBoolean' => ['filter:boolean' => 'Error'],
                'isEmail'      => ['filter:email'   => 'Error'],
                'isNotEmail'   => ['filter:email'   => 'Error'],
                'isFloat'      => ['filter:float'   => 'Error'],
                'isNotFloat'   => ['filter:float'   => 'Error'],
                'isInt'        => ['filter:int'     => 'Error'],
                'isNotInt'     => ['filter:int'     => 'Error'],
                'isIP'         => ['filter:ip'      => 'Error'],
                'isNotIP'      => ['filter:ip'      => 'Error'],
                'isURL'        => ['filter:url'     => 'Error'],
                'isNotURL'     => ['filter:url'     => 'Error'],
                'invalidRule'  => ['filter' => 'Error'],
            ],
        ];

        public function GET()
        {
            return ['foo' => 'bar'];
        }
    }
}

namespace
{
    class ResourceTest extends PHPUnit_Framework_TestCase
    {
        public function testFilterAndValidate()
        {
            $response = json_encode([
                'meta' => [
                    'status' => 500,
                    'message' => 'Invalid filter, expecting boolean, email, float, int, ip or url.',
                    'errors' => [
                        'missing'      => ['The missing parameter is required.'],
                        'isNotBoolean' => ['Error'],
                        'isNotEmail'   => ['Error'],
                        'isNotFloat'   => ['Error'],
                        'isNotInt'     => ['Error'],
                        'isNotIP'      => ['Error'],
                        'isNotURL'     => ['Error'],
                    ],
                ],
            ]);

            $this->expectOutputString($response);

            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_REQUEST['request'] = 'v1/resource/1';
            $_GET = [
                'foo'          => '     bar     ',
                'bar'          => 'unencrypted',
                'isBoolean'    => true,
                'isNotBoolean' => 'invalid',
                'isEmail'      => 'foo@bar.com',
                'isNotEmail'   => 'nope',
                'isFloat'      => 1.234567890,
                'isNotFloat'   => 'five',
                'isInt'        => 22381,
                'isNotInt'     => 'pretzel',
                'isIP'         => '127.0.0.1',
                'isNotIP'      => 'home',
                'isURL'        => 'http://joshtronic.com',
                'isNotURL'     => 'doubleUdoubleUdoubleUdot',
                'invalidRule'  => 'invalid',
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

        public function testAuthMisconfigured()
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

        public function testValidation()
        {

        }
    }
}

