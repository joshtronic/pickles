<?php

class MockModelWithoutColumns extends Model
{
	protected $table   = 'pickles';
	protected $columns = false;
}

class MockOtherModel extends Model
{
	protected $table   = 'brines';
	protected $columns = false;
}

class MockModel extends Model
{
	protected $table   = 'pickles';
	protected $columns = ['created_at' => 'created_at'];
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

	public function testJoinsString()
	{
		$model = new MockModelWithoutColumns([
			'conditions' => ['pickles.id' => 1],
			'joins'      => 'brines ON brines.pickle_id = pickles.id',
		]);
	}

	public function testJoinsArray()
	{
		$model = new MockModelWithoutColumns([
			'conditions' => ['pickles.id' => 1],
			'joins'      => [
				'INNER JOIN' => 'brines ON brines.pickle_id = pickles.id',
			],
		]);
	}

	public function testIndexHintsString()
	{
		$model = new MockModelWithoutColumns([
			'conditions' => ['pickles.id' => 1],
			'hints'      => 'is_deleted',
		]);
	}

	public function testIndexHintsArray()
	{
		$model = new MockModelWithoutColumns([
			'conditions' => ['pickles.id' => 1],
			'hints'      => ['is_deleted'],
		]);
	}

	public function testIndexHintsMultiple()
	{
		$model = new MockOtherModel([
			'conditions' => ['id' => 1],
			'hints'      => ['IGNORE INDEX' => ['pickle_id', 'is_deleted']],
		]);
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
}

?>
