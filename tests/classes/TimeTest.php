<?php

class TimeTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		date_default_timezone_set('GMT');
	}

	public function testAgePastTime()
	{
		$this->assertEquals(18, Time::age(date('Y-m-d', strtotime('-18 years'))));
	}

	public function testAgeFutureTime()
	{
		$this->assertEquals(-18, Time::age(date('Y-m-d', strtotime('18 years'))));
	}

	public function testAgeWrongFormat()
	{
		$this->assertEquals(17, Time::age(date('Ymd', strtotime('December 31st -18 years'))));
	}

	public function testAgoJustNow()
	{
		$this->assertEquals('just now', Time::ago(Time::timestamp()));
	}

	public function testAgoPastTimeSeconds()
	{
		$this->assertEquals('seconds ago', Time::ago(strtotime('-30 seconds')));
	}

	public function testAgoPastTimeMinutes()
	{
		$this->assertEquals('5 minutes ago', Time::ago(strtotime('-5 minutes')));
	}

	public function testAgoPastTimeHours()
	{
		$this->assertEquals('1 hour ago', Time::ago(strtotime('-1 hour')));
	}

	public function testAgoPastTimeDays()
	{
		$this->assertEquals('1 day ago', Time::ago(strtotime('-1 day')));
	}

	public function testAgoPastTimeWeeks()
	{
		$this->assertEquals('1 week ago', Time::ago(strtotime('-1 week')));
	}

	public function testAgoPastTimeMonths()
	{
		$this->assertEquals('1 month ago', Time::ago(strtotime('-1 month')));
	}

	public function testAgoPastTimeYears()
	{
		$this->assertEquals('1 year ago', Time::ago(strtotime('-1 year')));
	}

	public function testAgoFutureTimeSeconds()
	{
		$this->assertEquals('seconds from now', Time::ago(strtotime('+30 seconds')));
	}

	public function testAgoFutureTimeMinutes()
	{
		$this->assertEquals('5 minutes from now', Time::ago(strtotime('+5 minutes')));
	}

	public function testAgoFutureTimeHours()
	{
		$this->assertEquals('1 hour from now', Time::ago(strtotime('+1 hour')));
	}

	public function testAgoFutureTimeDays()
	{
		$this->assertEquals('1 day from now', Time::ago(strtotime('+1 day')));
	}

	public function testAgoFutureTimeWeeks()
	{
		$this->assertEquals('1 week from now', Time::ago(strtotime('+1 week')));
	}

	public function testAgoFutureTimeMonths()
	{
		$this->assertEquals('1 month from now', Time::ago(strtotime('+1 month')));
	}

	public function testAgoFutureTimeYears()
	{
		$this->assertEquals('1 year from now', Time::ago(strtotime('+1 year')));
	}

	public function testTimestamp()
	{
		$this->assertEquals(gmdate('Y-m-d H:i:s'), Time::timestamp());
	}
}

?>
