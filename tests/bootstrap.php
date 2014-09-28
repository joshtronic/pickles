<?php

require_once 'vendor/autoload.php';

$root = org\bovigo\vfs\vfsStream::setup('site');

if (!defined('SITE_PATH'))
{
    define('SITE_PATH', org\bovigo\vfs\vfsStream::url('site/'));
}

require_once 'src/pickles.php';

/*
// @todo Update to resources path??
if (!file_exists(SITE_MODULE_PATH))
{
    mkdir(SITE_MODULE_PATH, 0644);
}
*/

$_SERVER['HTTP_HOST']   = 'testsite.com';
$_SERVER['SERVER_NAME'] = 'Test Server';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';

function setUpRequest($request, $method = 'GET')
{
    $_SERVER['REQUEST_URI']    = '/' . $request;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_REQUEST['request']       = $request;
}

function setUpConfig($config)
{
    file_put_contents(
        SITE_PATH . 'config.php',
        '<?php $config = ' . var_export($config, true) . '; ?>'
    );
}

`mysql -e 'TRUNCATE TABLE test.pickles;'`;
`mysql -e 'TRUNCATE TABLE test.mypickles;'`;
`mysql -e 'TRUNCATE TABLE test.users;'`;
`echo 'flush_all' | nc localhost 11211`;

