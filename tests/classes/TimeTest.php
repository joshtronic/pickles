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

    public function testAgoPastTimeMinute()
    {
        $this->assertEquals('a minute ago', Time::ago(strtotime('-1 minutes')));
    }

    public function testAgoPastTimeMinutes()
    {
        $this->assertEquals('5 minutes ago', Time::ago(strtotime('-5 minutes')));
    }

    public function testAgoPastTimeHour()
    {
        $this->assertEquals('an hour ago', Time::ago(strtotime('-1 hours')));
    }

    public function testAgoPastTimeHours()
    {
        $this->assertEquals('2 hours ago', Time::ago(strtotime('-2 hours')));
    }

    public function testAgoPastTimeDay()
    {
        $this->assertEquals('a day ago', Time::ago(strtotime('-1 days')));
    }

    public function testAgoPastTimeDays()
    {
        $this->assertEquals('2 days ago', Time::ago(strtotime('-2 days')));
    }

    public function testAgoPastTimeWeek()
    {
        $this->assertEquals('a week ago', Time::ago(strtotime('-1 weeks')));
    }

    public function testAgoPastTimeWeeks()
    {
        $this->assertEquals('2 weeks ago', Time::ago(strtotime('-2 weeks')));
    }

    public function testAgoPastTimeMonth()
    {
        $this->assertEquals('a month ago', Time::ago(strtotime('-1 months')));
    }

    public function testAgoPastTimeMonths()
    {
        $this->assertEquals('2 months ago', Time::ago(strtotime('-2 months')));
    }

    public function testAgoPastTimeYear()
    {
        $this->assertEquals('a year ago', Time::ago(strtotime('-1 years')));
    }

    public function testAgoPastTimeYears()
    {
        $this->assertEquals('2 years ago', Time::ago(strtotime('-2 years')));
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
        $this->assertEquals('an hour from now', Time::ago(strtotime('+1 hour')));
    }

    public function testAgoFutureTimeDays()
    {
        $this->assertEquals('a day from now', Time::ago(strtotime('+1 day')));
    }

    public function testAgoFutureTimeWeeks()
    {
        $this->assertEquals('a week from now', Time::ago(strtotime('+1 week')));
    }

    public function testAgoFutureTimeMonths()
    {
        $this->assertEquals('a month from now', Time::ago(strtotime('+1 month')));
    }

    public function testAgoFutureTimeYears()
    {
        $this->assertEquals('a year from now', Time::ago(strtotime('+1 year')));
    }

    public function testTimestamp()
    {
        $this->assertEquals(gmdate('Y-m-d H:i:s'), Time::timestamp());
    }

    public function testRoundUpHour()
    {
        $this->assertEquals('an hour ago', Time::ago(strtotime('-59 minutes -55 seconds')));
    }

    public function testRoundUpDay()
    {
        $this->assertEquals('a day ago', Time::ago(strtotime('-23 hours -55 minutes')));
    }

    public function testRoundUpWeek()
    {
        $this->assertEquals('a week ago', Time::ago(strtotime('-6 days -23 hours')));
    }

    public function testRoundUpMonth()
    {
        $this->assertEquals('a month ago', Time::ago(strtotime('-29 days')));
    }

    public function testRoundUpYear()
    {
        $this->assertEquals('a year ago', Time::ago(strtotime('-364 days')));
    }
}

