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
		return array(
			array('foo',                                                      false, ''),
			array(array('foo', 'bar'),                                        false, '<0>foo</0><1>bar</1>'),
			array(array('foo', 'bar'),                                        true,  "<0>foo</0>\n<1>bar</1>\n"),
			array(array('foo' => 'bar'),                                      false, '<foo>bar</foo>'),
			array(array('children' => array('child' => array('foo', 'bar'))), false, '<children><child>foo</child><child>bar</child></children>'),
			array(array('children' => array('child' => array('foo', 'bar'))), true,  "<children>\n\t<child>foo</child>\n\t<child>bar</child>\n</children>\n"),
		);
	}
}

?>
