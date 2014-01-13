<?php

class ModelTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage You must set the table variable
	 */
	public function testNoTable()
	{
		new Model();
	}
}

?>
