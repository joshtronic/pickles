<?php

class API_Google_ProfanityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerFormatPhoneNumber
     */
    public function testCheck($a, $b)
    {
        $this->assertEquals($b, API_Google_Profanity::check($a));
    }

    public function providerFormatPhoneNumber()
    {
        return [
            ['alpha',      false],
            ['beta',       false],
            ['joshtronic', false],
            ['god',        false],
            ['fck',        false],
            ['fuck',       true],
            ['shit',       true],
            ['cocksucker', true],
            ['cuntface',   false], // Unsure why not...
        ];
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid response from API.
     */
    public function testInvalidResponse()
    {
        $file = SITE_PATH . 'null-';

        file_put_contents($file . 'test', null);

        API_Google_Profanity::check('test', $file);
    }
}

