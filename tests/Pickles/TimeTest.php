<?php

class TimeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        date_default_timezone_set('GMT');
    }

    /**
     * @dataProvider providerAge
     */
    public function testAge($a, $b)
    {
        $this->assertEquals(Pickles\Time::age($a), $b);
    }

    public function providerAge()
    {
        $time = strtotime('-25 years');

        return [
            [date('Y-m-d', $time), '25'],
            [date('m/d/Y', $time), '25'],
            [date('r',     $time), '25'],
            ['today',              '0'],
            ['400 days ago',       '1'],
            [true,                 Pickles\Date::age('1969-12-31')],
        ];
    }

    public function testAgePastTime()
    {
        $this->assertEquals(18, Pickles\Time::age(date('Y-m-d', strtotime('-18 years'))));
    }

    public function testAgeFutureTime()
    {
        $this->assertEquals(-18, Pickles\Time::age(date('Y-m-d', strtotime('18 years'))));
    }

    public function testAgeWrongFormat()
    {
        $this->assertEquals(17, Pickles\Time::age(date('Ymd', strtotime('December 31st -18 years'))));
    }

    public function testAgoJustNow()
    {
        $this->assertEquals('just now', Pickles\Time::ago(Pickles\Time::timestamp()));
    }

    public function testAgoPastTimeSeconds()
    {
        $this->assertEquals('seconds ago', Pickles\Time::ago(strtotime('-30 seconds')));
    }

    public function testAgoPastTimeMinute()
    {
        $this->assertEquals('a minute ago', Pickles\Time::ago(strtotime('-1 minutes')));
    }

    public function testAgoPastTimeMinutes()
    {
        $this->assertEquals('5 minutes ago', Pickles\Time::ago(strtotime('-5 minutes')));
    }

    public function testAgoPastTimeHour()
    {
        $this->assertEquals('an hour ago', Pickles\Time::ago(strtotime('-1 hours')));
    }

    public function testAgoPastTimeHours()
    {
        $this->assertEquals('2 hours ago', Pickles\Time::ago(strtotime('-2 hours')));
    }

    public function testAgoPastTimeDay()
    {
        $this->assertEquals('a day ago', Pickles\Time::ago(strtotime('-1 days')));
    }

    public function testAgoPastTimeDays()
    {
        $this->assertEquals('2 days ago', Pickles\Time::ago(strtotime('-2 days')));
    }

    public function testAgoPastTimeWeek()
    {
        $this->assertEquals('a week ago', Pickles\Time::ago(strtotime('-1 weeks')));
    }

    public function testAgoPastTimeWeeks()
    {
        $this->assertEquals('2 weeks ago', Pickles\Time::ago(strtotime('-2 weeks')));
    }

    public function testAgoPastTimeMonth()
    {
        $this->assertEquals('a month ago', Pickles\Time::ago(strtotime('-1 months')));
    }

    public function testAgoPastTimeMonths()
    {
        $this->assertEquals('2 months ago', Pickles\Time::ago(strtotime('-2 months')));
    }

    public function testAgoPastTimeYear()
    {
        $this->assertEquals('a year ago', Pickles\Time::ago(strtotime('-1 years')));
    }

    public function testAgoPastTimeYears()
    {
        $this->assertEquals('2 years ago', Pickles\Time::ago(strtotime('-2 years')));
    }

    public function testAgoFutureTimeSeconds()
    {
        $this->assertEquals('seconds from now', Pickles\Time::ago(strtotime('+30 seconds')));
    }

    public function testAgoFutureTimeMinutes()
    {
        $this->assertEquals('5 minutes from now', Pickles\Time::ago(strtotime('+5 minutes')));
    }

    public function testAgoFutureTimeHours()
    {
        $this->assertEquals('an hour from now', Pickles\Time::ago(strtotime('+1 hour')));
    }

    public function testAgoFutureTimeDays()
    {
        $this->assertEquals('a day from now', Pickles\Time::ago(strtotime('+1 day')));
    }

    public function testAgoFutureTimeWeeks()
    {
        $this->assertEquals('a week from now', Pickles\Time::ago(strtotime('+1 week')));
    }

    public function testAgoFutureTimeMonths()
    {
        $this->assertEquals('a month from now', Pickles\Time::ago(strtotime('+1 month')));
    }

    public function testAgoFutureTimeYears()
    {
        $this->assertEquals('a year from now', Pickles\Time::ago(strtotime('+1 year')));
    }

    public function testTimestamp()
    {
        $this->assertEquals(gmdate('Y-m-d H:i:s'), Pickles\Time::timestamp());
    }

    public function testRoundUpHour()
    {
        $this->assertEquals('an hour ago', Pickles\Time::ago(strtotime('-59 minutes -55 seconds')));
    }

    public function testRoundUpDay()
    {
        $this->assertEquals('a day ago', Pickles\Time::ago(strtotime('-23 hours -55 minutes')));
    }

    public function testRoundUpWeek()
    {
        $this->assertEquals('a week ago', Pickles\Time::ago(strtotime('-6 days -23 hours')));
    }

    public function testRoundUpMonth()
    {
        $this->assertEquals('a month ago', Pickles\Time::ago(strtotime('-29 days')));
    }

    public function testRoundUpYear()
    {
        $this->assertEquals('a year ago', Pickles\Time::ago(strtotime('-364 days')));
    }
}

