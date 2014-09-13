<?php

class ConvertTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerArrayToXML
     */
    public function testArrayToXML($a, $b, $c)
    {
        $this->assertEquals(Convert::arrayToXML($a, $b), $c);
    }

    public function providerArrayToXML()
    {
        return [
            ['foo',                                       false, ''],
            [['foo', 'bar'],                              false, '<0>foo</0><1>bar</1>'],
            [['foo', 'bar'],                              true,  "<0>foo</0>\n<1>bar</1>\n"],
            [['foo' => 'bar'],                            false, '<foo>bar</foo>'],
            [['foo' => 'b & r'],                          false, '<foo><![CDATA[b & r]]></foo>'],
            [['children' => ['child' => ['foo', 'bar']]], false, '<children><child>foo</child><child>bar</child></children>'],
            [['children' => ['child' => ['foo & bar']]],  false, '<children><child><![CDATA[foo & bar]]></child></children>'],
            [['children' => ['child' => ['foo', 'bar']]], true,  "<children>\n\t<child>foo</child>\n\t<child>bar</child>\n</children>\n"],
        ];
    }
}

