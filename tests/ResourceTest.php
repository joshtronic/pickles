<?php

$_POST['field2']    = 'short';
$_GET['field2']     = 'short';
$_REQUEST['field2'] = 'short';

class MockParentResource extends Pickles\Resource
{
    public $validate = [
        'field1',
        'field2' => [
            'length:<:10' => 'Too short',
            'length:>:50' => 'Too long',
        ],
    ];
}

class MockChildResource extends MockParentResource
{
    public $method = ['POST', 'GET'];
}

class ResourceTest extends PHPUnit_Framework_TestCase
{
    public function testAutoRun()
    {
        $this->assertInstanceOf('Pickles\\Resource', new Pickles\Resource(true));
    }

    public function testAutoRunParentError()
    {
        $this->expectOutputString('');
        $model = new MockChildResource(true);
    }

    public function testSetGetReturn()
    {
        $module = new Pickles\Resource();
        $module->foo = 'bar';
        $this->assertEquals('bar', $module->foo);
    }

    public function testGetMissing()
    {
        $module = new Pickles\Resource();
        $this->assertFalse($module->missing);
    }

    public function testValidateGet()
    {
        $module = new MockParentResource();
        $module->method = 'GET';
        $this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
    }

    public function testValidatePost()
    {
        $module = new MockParentResource();
        $this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
    }

    public function testValidateRequest()
    {
        $module = new MockParentResource();
        $module->method = null;
        $this->assertEquals(['The field1 field is required.', 'Too long'], $module->__validate());
    }
}

