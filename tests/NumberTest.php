<?php

class NumberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerOrginalIndicatorNoSuper
     */
    public function testOrdinalIndicatorNoSuper($a, $b)
    {
        $this->assertEquals($b, Pickles\Number::ordinalIndicator($a));
    }

    public function providerOrginalIndicatorNoSuper()
    {
        return [
            [1,  '1st'],
            [2,  '2nd'],
            [3,  '3rd'],
            [4,  '4th'],
            [51, '51st'],
            [52, '52nd'],
            [53, '53rd'],
            [54, '54th'],
        ];
    }

    /**
     * @dataProvider providerOrginalIndicatorSuper
     */
    public function testOrdinalIndicatorSuper($a, $b)
    {
        $this->assertEquals($b, Pickles\Number::ordinalIndicator($a, true));
    }

    public function providerOrginalIndicatorSuper()
    {
        return [
            [1,  '1<sup>st</sup>'],
            [2,  '2<sup>nd</sup>'],
            [3,  '3<sup>rd</sup>'],
            [4,  '4<sup>th</sup>'],
            [51, '51<sup>st</sup>'],
            [52, '52<sup>nd</sup>'],
            [53, '53<sup>rd</sup>'],
            [54, '54<sup>th</sup>'],
        ];
    }
}

