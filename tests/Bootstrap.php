<?php

require_once 'vendor/autoload.php';

$_SERVER['HTTP_HOST']   = 'testsite.com';
$_SERVER['SERVER_NAME'] = 'Test Server';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

function setUpRequest($request, $method = 'GET')
{
    $_SERVER['REQUEST_URI']    = '/' . $request;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_REQUEST['request']       = $request;
}

`mysql -e 'TRUNCATE TABLE test.pickles;'`;
`mysql -e 'TRUNCATE TABLE test.mypickles;'`;
`mysql -e 'TRUNCATE TABLE test.users;'`;
`echo 'flush_all' | nc localhost 11211`;

