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

        Pickles\Sort::by('name', $shuffled);

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

        Pickles\Sort::by('name', $shuffled, Pickles\Sort::DESC);

        $this->assertEquals($sorted, $shuffled);
    }

    public function testMissingField()
    {
        $shuffled = [['foo' => 'bar', 'bar' => 'foo']];
        $sorted   = [['foo' => 'bar', 'bar' => 'foo']];

        Pickles\Sort::by('name', $shuffled);

        $this->assertEquals($sorted, $shuffled);
    }
}

