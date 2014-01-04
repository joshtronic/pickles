<?php

class HTMLTest extends PHPUnit_Framework_TestCase
{
	private $html;

	public function setUp()
	{
		$this->html = HTML::getInstance();
	}

	public function testGetInstance()
	{
		$this->assertInstanceOf('HTML', $this->html);
	}

	public function testInput()
	{
		$this->assertEquals('<input type="text">', $this->html->input());
	}

	public function testInputDateTimeLocal()
	{
		$this->assertEquals('<input type="datetime-local">', $this->html->inputDateTimeLocal());
	}

	public function testInputEmail()
	{
		$this->assertEquals('<input type="email">', $this->html->inputEmail());
	}

	public function testInputWithAttributes()
	{
		$this->assertEquals(
			'<input id="id" class="class" value="value" type="text">',
			$this->html->input([
				'id'    => 'id',
				'class' => 'class',
				'value' => 'value',
			])
		);
	}

	public function testInputPasswordWithLabel()
	{
		$this->assertEquals(
			'<label for="password">Enter Password</label><input name="password" type="password">',
			$this->html->inputPassword([
				'name'  => 'password',
				'label' => 'Enter Password',
			])
		);

	}

	public function testNestedElements()
	{
		$this->assertEquals(
			'<div><p>Nested!</p></div>',
			$this->html->div(
				$this->html->p('Nested!')
			)
		);
	}

	public function testNestedElementsWithAttributes()
	{
		$this->assertEquals(
			'<div class="outer"><p class="inner">Nested!</p></div>',
			$this->html->div(
				['class' => 'outer'],
				$this->html->p(
					['class' => 'inner'],
					'Nested!'
				)
			)
		);
	}

	public function testClosingTag()
	{
		$this->assertEquals('<textarea></textarea>', $this->html->textarea());
	}

	public function testElement()
	{
		$this->assertEquals('<div></div>', $this->html->element('div'));
	}

	public function testReversedParameters()
	{
		$this->assertEquals(
			'<div class="fancy">string</div>',
			$this->html->div('string', ['class' => 'fancy'])
		);
	}
}

?>
