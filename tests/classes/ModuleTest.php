<?php

$_POST['field2']    = 'short';
$_GET['field2']     = 'short';
$_REQUEST['field2'] = 'short';

class MockParentModule extends Module
{
	public $validate = [
		'field1',
		'field2' => [
			'length:<:10' => 'Too short',
			'length:>:50' => 'Too long',
		],
	];
}

class MockChildModule extends MockParentModule
{
	public $method = ['POST', 'GET'];
}

class ModuleTest extends PHPUnit_Framework_TestCase
{
	public function testAutoRun()
	{
		$this->assertInstanceOf('Module', new Module(true));
	}

	public function testAutoRunParentError()
	{
		$this->expectOutputString('');
		$model = new MockChildModule(true);
	}

	public function testSetGetReturn()
	{
		$module = new Module();
		$module->foo = 'bar';
		$this->assertEquals('bar', $module->foo);
	}

	public function testGetMissing()
	{
		$module = new Module();
		$this->assertFalse($module->missing);
	}

	public function testValidateGet()
	{
		$module = new MockParentModule();
		$module->method = 'GET';
		$this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
	}

	public function testValidatePost()
	{
		$module = new MockParentModule();
		$this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
	}

	public function testValidateRequest()
	{
		$module = new MockParentModule();
		$module->method = null;
		$this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
	}
}

?>
