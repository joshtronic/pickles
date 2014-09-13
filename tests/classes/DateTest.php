<?php

class DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerAge
     */
    public function testAge($a, $b)
    {
        $this->assertEquals(Date::age($a), $b);
    }

    public function providerAge()
    {
        ini_set('date.timezone', 'America/New_York');

        $time = strtotime('-25 years');

        return [
            [date('Y-m-d', $time), '25'],
            [date('m/d/Y', $time), '25'],
            [date('r',     $time), '25'],
            ['today',              '0'],
            ['400 days ago',       '1'],
            [true,                 Date::age('1969-12-31')],
        ];
    }
}

