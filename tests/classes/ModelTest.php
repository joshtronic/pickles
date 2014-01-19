<?php

class MockModelWithoutColumns extends Model
{
	public $table   = 'pickles';
	public $columns = false;
}

class MockModel extends Model
{
	public $table   = 'pickles';
	public $columns = ['created_at' => 'created_at'];
}

class ModelTest extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		$config = Config::getInstance();

		$config->data = [
			'pickles' => [
				'datasource' => 'mysql',
				'cache'      => 'memcache',
			],
			'datasources' => [
				'mysql' => [
					'type'     => 'mysql',
					'driver'   => 'pdo_mysql',
					'hostname' => 'localhost',
					'username' => '',
					'password' => '',
					'database' => 'test',
					'cache'    => true,
				],
				'memcache' => [
					'type'      => 'memcache',
					'hostname'  => 'localhost',
					'port'      => 11211,
					'namespace' => '',
				],
			],
		];

		for ($i = 0; $i < 5; $i++)
		{
			$model = new MockModel();
			$model->record['field1'] = 'one';
			$model->record['field2'] = 'two';
			$model->record['field3'] = 'three';
			$model->record['field4'] = 'four';
			$model->record['field5'] = 'five';
			$model->commit();
		}
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage You must set the table variable
	 */
	public function testNoTable()
	{
		new Model();
	}

	public function testWithoutColumns()
	{
		$model   = new MockModelWithoutColumns();
		$columns = PHPUnit_Framework_Assert::readAttribute($model, 'columns');

		$this->assertFalse($columns['is_deleted']);
	}

	public function testWithColumns()
	{
		$model   = new MockModel();
		$columns = PHPUnit_Framework_Assert::readAttribute($model, 'columns');

		$this->assertEquals('is_deleted', $columns['is_deleted']);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage You cannot pass in 2 query parameter arrays
	 */
	public function testDoubleArray()
	{
		$model = new MockModel(['foo' => 'bar'], ['test' => 'ing']);
	}

	public function testFetchInt()
	{
		$model = new MockModel(1);
		$this->assertEquals(1, $model->count());
		$this->assertEquals(1, $model->record['id']);
	}

	public function testFetchIntArray()
	{
		$model = new MockModel([1, 2, 3]);
		$this->assertEquals(3, $model->count());
	}

	/*
	@todo Acting wonky, passes tests on just this class, fails on all
	public function testFetchConditionsID()
	{
		$model = new MockModel(['conditions' => ['id' => 1]]);
		var_dump($model->record);
		$this->assertEquals(1, $model->count());
		$this->assertEquals(1, $model->record['id']);
	}
	*/

	public function testFetchCount()
	{
		$model = new MockModel('count');
		$this->assertEquals(5, $model->record['count']);
	}

	public function testFetchCountConditions()
	{
		$model = new MockModel('count', ['conditions' => ['id' => [1, 3, 5]]]);
		$this->assertEquals(3, $model->record['count']);
	}

	public function testFetchIndexed()
	{
		$model = new MockModel('indexed', ['conditions' => ['id' => [2, 4]]]);
		$this->assertEquals(2, $model->count());
		$this->assertEquals([2, 4], array_keys($model->records));
	}

	// Also tests against a full cache
	public function testFetchList()
	{
		$model = new MockModel('list', ['conditions' => ['id' => [2, 4]]]);
		$this->assertEquals(2, $model->count());
		$this->assertEquals([2, 4], array_keys($model->records));
	}

	public function testFetchCountWithID()
	{
		$model = new MockModel('count', 3);
		$this->assertEquals(1, $model->record['count']);
	}

	public function testFetchListWithID()
	{
		$model = new MockModel('list', 2);
		$this->assertEquals(1, $model->count());
		$this->assertEquals([2 => 'one'], $model->records);
	}

	public function testFieldValues()
	{
		$model = new MockModel('all');

		$fields = $model->fieldValues('id');

		$this->assertEquals('5', count($fields));

		foreach ($fields as $value)
		{
			$this->assertTrue(ctype_digit($value));
		}
	}

	public function testSort()
	{
		$model = new MockModel();
		$this->assertTrue($model->sort('id'));
	}

	public function testShuffle()
	{
		$model = new MockModel();
		$this->assertTrue($model->shuffle());
	}

	public function testNextPrev()
	{
		$model = new MockModel('all');
		$model->next();
		$this->assertEquals(2, $model->record['id']);
		$model->prev();
		$this->assertEquals(1, $model->record['id']);
	}

	public function testLastFirst()
	{
		$model = new MockModel('all');
		$model->last();
		$this->assertEquals(5, $model->record['id']);
		$model->first();
		$this->assertEquals(1, $model->record['id']);
	}

	public function testEndReset()
	{
		$model = new MockModel('all');
		$model->end();
		$this->assertEquals(5, $model->record['id']);
		$model->reset();
		$this->assertEquals(1, $model->record['id']);
	}

	public function testWalk()
	{
		$model    = new MockModel('all');
		$expected = 0;

		while ($model->walk())
		{
			$expected++;
			$this->assertEquals($expected, $model->record['id']);
		}
	}

	public function testInsert()
	{
		$model = new MockModel();

		for ($i = 1; $i <= 5; $i++)
		{
			$model->record['field' . $i] = String::random();
		}

		$model->commit();
	}

	public function testInsertMultiple()
	{
		$model = new MockModel();

		for ($i = 1; $i <= 5; $i++)
		{
			for ($j = 1; $j <= 5; $j++)
			{
				$model->record['field' . $j] = String::random();
			}

			$model->queue();
		}

		$model->commit();
	}

	public function testGetFromCache()
	{
		$model = new MockModel(1);
		$this->assertEquals('1', $model->record['id']);
	}

	public function testGetFromCacheConditionals()
	{
		$model = new MockModel(['conditions' => ['id' => 1]]);
		$this->assertEquals('1', $model->record['id']);
	}

	public function testCacheKey()
	{
		$model = new MockModel('indexed', 1, 'cache-key');
		$this->assertEquals([1], array_keys($model->records));
	}

	public function testGenerateQuery()
	{
		$model = new MockModelWithoutColumns([
			'conditions' => [1, 2, 3],
			'group'      => 'id',
			'having'     => '1 = 1',
			'order'      => 'id DESC',
			'limit'      => 5,
			'offset'     => 1,
		]);
		$this->assertEquals('2', $model->record['id']);
	}

	public function testGenerateConditions()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions([
			'id' => [1, 2, 3],
			'NOT' => 5,
			'OR id !=' => 10,
			'OR NOT' => [15, 20, 25],
			'id != 30',
			'id IS NOT' => null,
			'id !=' => false,
			'id <' => true,
			'id >' => null,
			'id BETWEEN' => [35, 40],
		]);
		$this->assertEquals('id in (?, ?, ?) AND NOT = ? OR id != ? OR NOT in (?, ?, ?) AND id != 30 AND id IS NOT NULL AND id IS NOT FALSE AND id IS TRUE AND id > NULL AND id BETWEEN ? AND ?', $conditions);
	}

	public function testGenerateConditionsInjectValues()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions([
			'id' => [1, 2, 3],
			'NOT' => 5,
			'OR id !=' => 10,
			'OR NOT' => [15, 20, 25],
			'id != 30',
			'id IS NOT' => null,
			'id !=' => false,
			'id <' => true,
			'id >' => null,
			'id BETWEEN' => [35, 40],
		], true);
		$this->assertEquals('id in (1, 2, 3) AND NOT = 5 OR id != 10 OR NOT in (15, 20, 25) AND id != 30 AND id IS NOT NULL AND id IS NOT FALSE AND id IS TRUE AND id > NULL AND id BETWEEN 35 AND 40', $conditions);
	}

	public function testGenerateConditionsNoOperatorTrue()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions(['id' => true]);
		$this->assertEquals('id IS TRUE', $conditions);
	}

	public function testGenerateConditionsNoOperatorFalse()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions(['id' => false]);
		$this->assertEquals('id IS FALSE', $conditions);
	}

	public function testGenerateConditionsNoOperatorNull()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions(['id' => null]);
		$this->assertEquals('id IS NULL', $conditions);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage BETWEEN expects an array with 2 values.
	 */
	public function testGenerateConditionsBetweenMissingValue()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions(['id BETWEEN' => [1]]);
	}

	/**
	 * @expectedException        Exception
	 * @expectedExceptionMessage BETWEEN expects an array.
	 */
	public function testGenerateConditionsBetweenNotArray()
	{
		$model = new MockModel();
		$conditions = $model->generateConditions(['id BETWEEN' => '1']);
	}
}

?>
