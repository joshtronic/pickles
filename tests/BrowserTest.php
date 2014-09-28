<?php

class BrowserTest extends PHPUnit_Framework_TestCase
{
    public function testRemoteIPNone()
    {
        $this->assertFalse(Pickles\Browser::remoteIP());
    }

    public function testRemoteIPRemoteAddress()
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        $this->assertEquals('1.2.3.4', Pickles\Browser::remoteIP());
    }

    public function testRemoteIPHTTPXForwardedFor()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.3.4.5';

        $this->assertEquals('2.3.4.5', Pickles\Browser::remoteIP());
    }

    public function testRemoteIPHTTPClientIP()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '3.4.5.6';

        $this->assertEquals('3.4.5.6', Pickles\Browser::remoteIP());
    }

    public function testRemoteIPWithComma()
    {

    }

    public function testStatus1xx()
    {
        Pickles\Browser::status(100);
        $this->assertTrue(in_array('Status: 100 Continue', xdebug_get_headers()));
    }

    public function testStatus2xx()
    {
        Pickles\Browser::status(200);
        $this->assertTrue(in_array('Status: 200 OK', xdebug_get_headers()));
    }

    public function testStatus3xx()
    {
        Pickles\Browser::status(300);
        $this->assertTrue(in_array('Status: 300 Multiple Choices', xdebug_get_headers()));
    }

    public function testStatus4xx()
    {
        Pickles\Browser::status(400);
        $this->assertTrue(in_array('Status: 400 Bad Request', xdebug_get_headers()));
    }

    public function testStatus5xx()
    {
        Pickles\Browser::status(500);
        $this->assertTrue(in_array('Status: 500 Internal Server Error', xdebug_get_headers()));
    }
}

