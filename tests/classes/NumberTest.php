<?php

class NumberTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider providerOrginalIndicatorNoSuper
	 */
	public function testOrdinalIndicatorNoSuper($a, $b)
	{
		$this->assertEquals($b, Number::ordinalIndicator($a));
	}

	public function providerOrginalIndicatorNoSuper()
	{
		return array(
			array(1,  '1st'),
			array(2,  '2nd'),
			array(3,  '3rd'),
			array(4,  '4th'),
			array(51, '51st'),
			array(52, '52nd'),
			array(53, '53rd'),
			array(54, '54th'),
		);
	}

	/**
	 * @dataProvider providerOrginalIndicatorSuper
	 */
	public function testOrdinalIndicatorSuper($a, $b)
	{
		$this->assertEquals($b, Number::ordinalIndicator($a, true));
	}

	public function providerOrginalIndicatorSuper()
	{
		return array(
			array(1,  '1<sup>st</sup>'),
			array(2,  '2<sup>nd</sup>'),
			array(3,  '3<sup>rd</sup>'),
			array(4,  '4<sup>th</sup>'),
			array(51, '51<sup>st</sup>'),
			array(52, '52<sup>nd</sup>'),
			array(53, '53<sup>rd</sup>'),
			array(54, '54<sup>th</sup>'),
		);
	}
}

?>
