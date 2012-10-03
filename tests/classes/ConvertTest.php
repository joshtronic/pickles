<?php

require_once 'classes/Convert.php';
define('JSON_AVAILABLE', true);

class ConvertTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider providerToJSON
	 */
	public function testToJSON($a, $b)
	{
		$this->assertEquals(Convert::toJSON($a), $b);
	}

	public function providerToJSON()
	{
		$object      = (object)'object';
		$object->foo = 'foo';
		$object->bar = 'bar';

		return array(
			array('',                         '""'),
			array('foo',                      '"foo"'),
			array(array('bar'),               '["bar"]'),
			array(array('foo', 'bar'),        '["foo","bar"]'),
			array(19810223,                   '19810223'),
			array(array(1981, 02, 23),        '[1981,2,23]'),
			array(array('foo', 1981),         '["foo",1981]'),
			array(array('foo', array('bar')), '["foo",["bar"]]'),
			array($object,                    '{"scalar":"object","foo":"foo","bar":"bar"}'),
			array(true,                       'true'),
			array(false,                      'false'),
			array(null,                       'null'),
		);
	}

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
