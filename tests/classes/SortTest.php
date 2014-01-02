<?php

class SortTest extends PHPUnit_Framework_TestCase
{
	public function testByNameASC()
	{
		$shuffled = [
			['name' => 'epsilon'],
			['name' => 'gamma'],
			['name' => 'alpha'],
			['name' => 'delta'],
			['name' => 'beta'],
		];

		$sorted = [
			['name' => 'alpha'],
			['name' => 'beta'],
			['name' => 'delta'],
			['name' => 'epsilon'],
			['name' => 'gamma'],
		];

		Sort::by('name', $shuffled);

		$this->assertEquals($sorted, $shuffled);
	}

	public function testByNameDESC()
	{
		$shuffled = [
			['name' => 'epsilon'],
			['name' => 'gamma'],
			['name' => 'alpha'],
			['name' => 'delta'],
			['name' => 'beta'],
		];

		$sorted = [
			['name' => 'gamma'],
			['name' => 'epsilon'],
			['name' => 'delta'],
			['name' => 'beta'],
			['name' => 'alpha'],
		];

		Sort::by('name', $shuffled, Sort::DESC);

		$this->assertEquals($sorted, $shuffled);
	}

	public function testMissingField()
	{
		$shuffled = [['foo' => 'bar', 'bar' => 'foo']];
		$sorted   = [['foo' => 'bar', 'bar' => 'foo']];

		Sort::by('name', $shuffled);

		$this->assertEquals($sorted, $shuffled);
	}
}

?>
