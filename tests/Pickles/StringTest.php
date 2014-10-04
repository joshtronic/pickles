<?php

class StringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerFormatPhoneNumber
     */
    public function testFormatPhoneNumber($a, $b)
    {
        $this->assertEquals(Pickles\String::formatPhoneNumber($a), $b);
    }

    public function providerFormatPhoneNumber()
    {
        return [
            ['1234567890',            '123-456-7890'],
            ['123 456 7890',          '123-456-7890'],
            ['123.456.7890',          '123-456-7890'],
            ['123_456_7890',          '123-456-7890'],
            ['1234567890',            '123-456-7890'],
            ['1234-56-7890',          '123-456-7890'],
            ['(123) 456-7890',        '123-456-7890'],
            ['1234567890 x1000',      '123-456-7890x1000'],
            ['(123) 456-7890_x10.00', '123-456-7890x1000'],
        ];
    }

    public function testIsEmpty()
    {
        $this->assertTrue(Pickles\String::isEmpty(''));
        $this->assertTrue(Pickles\String::isEmpty(' '));
        $this->assertTrue(Pickles\String::isEmpty(false));
        $this->assertTrue(Pickles\String::isEmpty(null));
        $this->assertTrue(Pickles\String::isEmpty(true, false));

        $this->assertFalse(Pickles\String::isEmpty(0));
        $this->assertFalse(Pickles\String::isEmpty('foo'));
        $this->assertFalse(Pickles\String::isEmpty(' bar '));
        $this->assertFalse(Pickles\String::isEmpty(true));
    }

    public function testRandom()
    {
        $this->assertEquals(strlen(Pickles\String::random()),   8);
        $this->assertEquals(strlen(Pickles\String::random(16)), 16);

        $this->assertEquals(preg_match('/[a-z0-9]/', Pickles\String::random(32, true,  true)),  1);
        $this->assertEquals(preg_match('/[a-z]/',    Pickles\String::random(32, true,  false)), 1);
        $this->assertEquals(preg_match('/[0-9]/',    Pickles\String::random(32, false, true)),  1);

        $this->assertEquals(preg_match('/[0-9]/',    Pickles\String::random(32, true,  false)), 0);
        $this->assertEquals(preg_match('/[a-z]/',    Pickles\String::random(32, false, true)),  0);
        $this->assertEquals(preg_match('/[a-z0-9]/', Pickles\String::random(32, false, false)), 0);
    }

    public function testRandomSimilarFalse()
    {
        $this->assertRegExp('/[a-hj-np-z2-9]{8}/', Pickles\String::random(8, true, true, false));
    }

    /**
     * @dataProvider providerTruncate
     */
    public function testTruncate($a, $b, $c, $d)
    {
        $this->assertEquals(Pickles\String::truncate($a, $b, $c), $d);
    }

    public function providerTruncate()
    {
        return [
            ['foo bar', 3, true,  '<span title="foo bar">foo&hellip;</span>'],
            ['foo bar', 3, false, 'foo&hellip;'],
            ['foo bar', 7, true,  'foo bar'],
            ['foo bar', 8, true,  'foo bar'],
        ];
    }

    /**
     * @dataProvider providerUpperWords
     */
    public function testUpperWords($a, $b)
    {
        $this->assertEquals(Pickles\String::upperWords($a), $b);
    }

    public function providerUpperWords()
    {
        return [
            ['foo bar',     'Foo Bar'],
            ['FOO BAR',     'Foo Bar'],
            ['fOO bAR',     'Foo Bar'],
            ['foo@bar.com', 'foo@bar.com'],
            ['FOO@BAR.COM', 'FOO@BAR.COM'],
        ];
    }

    /**
     * @dataProvider providerGenerateSlug
     */
    public function testGenerateSlug($a, $b)
    {
        $this->assertEquals($b, Pickles\String::generateSlug($a));
    }

    public function providerGenerateSlug()
    {
        return [
            ['TEST STRING',    'test-string'],
            ['Test String',    'test-string'],
            ['TEST  STRING',   'test-string'],
            ['#! Test String', 'test-string'],
            ['-test--string-', 'test-string'],
        ];
    }

    public function testPluralize()
    {
        $this->assertEquals('test',    Pickles\String::pluralize('test', 1, false));
        $this->assertEquals('1 test',  Pickles\String::pluralize('test', 1, true));
        $this->assertEquals('tests',   Pickles\String::pluralize('test', 2, false));
        $this->assertEquals('2 tests', Pickles\String::pluralize('test', 2, true));
    }
}

