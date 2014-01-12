<?php

class BrowserTest extends  PHPUnit_Framework_TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf('Browser', Browser::getInstance());
	}

	public function testSetAndGet()
	{
		$this->assertTrue(Browser::set('foo', 'bar'));
		$this->assertEquals('bar', Browser::get('foo'));
	}

	public function testMissingVariable()
	{
		$this->assertFalse(Browser::get('missing'));
	}

	public function testGoHome()
	{
		Browser::goHome();
		$this->assertTrue(in_array('Location: http://testsite.com/', xdebug_get_headers()));
	}

	public function testIsMobile()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16';

		$this->assertTrue(Browser::isMobile());
	}

	public function testIsNotMobile()
	{
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.73.11 (KHTML, like Gecko) Version/7.0.1 Safari/537.73.11';

		$this->assertFalse(Browser::isMobile());
	}

	public function testRemoteIPNone()
	{
		$this->assertFalse(Browser::remoteIP());
	}

	public function testRemoteIPRemoteAddress()
	{
		$_SERVER['REMOTE_ADDR'] = '1.2.3.4';

		$this->assertEquals('1.2.3.4', Browser::remoteIP());
	}

	public function testRemoteIPHTTPXForwardedFor()
	{
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '2.3.4.5';

		$this->assertEquals('2.3.4.5', Browser::remoteIP());
	}

	public function testRemoteIPHTTPClientIP()
	{
		$_SERVER['HTTP_CLIENT_IP'] = '3.4.5.6';

		$this->assertEquals('3.4.5.6', Browser::remoteIP());
	}

	public function testStatus1xx()
	{
		Browser::status(100);
		$this->assertTrue(in_array('Status: 100 Continue', xdebug_get_headers()));
	}

	public function testStatus2xx()
	{
		Browser::status(200);
		$this->assertTrue(in_array('Status: 200 OK', xdebug_get_headers()));
	}

	public function testStatus3xx()
	{
		Browser::status(300);
		$this->assertTrue(in_array('Status: 300 Multiple Choices', xdebug_get_headers()));
	}

	public function testStatus4xx()
	{
		Browser::status(400);
		$this->assertTrue(in_array('Status: 400 Bad Request', xdebug_get_headers()));
	}

	public function testStatus5xx()
	{
		Browser::status(500);
		$this->assertTrue(in_array('Status: 500 Internal Server Error', xdebug_get_headers()));
	}
}

?>
