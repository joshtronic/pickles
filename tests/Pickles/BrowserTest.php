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
}

