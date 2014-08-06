<?php

class MockModelWithoutColumns extends Model
{
	public $table   = 'pickles';
	public $columns = false;
}

// InnoDB
class MockModel extends Model
{
	public $table   = 'pickles';
	public $columns = ['created_at' => 'created_at'];
}

// MyISAM
class MyMockModel extends Model
{
	public $table   = 'mypickles';
}

class ModelTest extends PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		// Clears out the Config for ease of testing
		Object::$instances = [];
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
					'username' => 'root',
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

	public function testCommitSingleRecord()
	{
		$value = String::random();
		$model = new MockModel(1);
		$model->record['field1'] = $value;
		$model->commit();

		$model = new MockModel(1);
		$this->assertEquals($value, $model->record['field1']);
	}

	// Handles filling coverage gaps but isn't a reliable test. Would need to
	// test against a table without a UID column so we can see this in action,
	// else it just takes a shit because the ID isn't injected back in.
	public function testCommitSingleRecordReplace()
	{
		$value = String::random();
		$model = new MockModel(1);
		$model->replace = true;
		$model->record['field1'] = $value;
		$model->commit();
		$model = new MockModel(1);
	}

	public function testCommitInsertPriority()
	{
		$value = String::random();
		$model = new MockModel();
		$model->priority = 'low';
		$model->record['field1'] = $value;
		$id = $model->commit();
		$model = new MockModel($id);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitInsertDelayed()
	{
		$value = String::random();
		$model = new MyMockModel();
		$model->delayed = true;
		$model->record['field1'] = $value;
		$model->commit();
		$model = new MyMockModel(1);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitInsertIgnore()
	{
		$value = String::random();
		$model = new MockModel();
		$model->ignore = true;
		$model->record['field1'] = $value;
		$id = $model->commit();
		$model = new MockModel($id);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitReplacePriority()
	{
		$value = String::random();
		$model = new MockModel();
		$model->replace = true;
		$model->priority = 'low';
		$model->record['field1'] = $value;
		$id = $model->commit();
		$model = new MockModel($id);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitReplaceDelayed()
	{
		$value = String::random();
		$model = new MyMockModel();
		$model->replace = true;
		$model->delayed = true;
		$model->record['field1'] = $value;
		$model->commit();
		$model = new MyMockModel(2);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitReplaceIgnore()
	{
		$value = String::random();
		$model = new MockModel();
		$model->replace = true;
		$model->ignore = true;
		$model->record['field1'] = $value;
		$id = $model->commit();
		$model = new MockModel($id);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitMultipleFields()
	{
		$value1 = String::random();
		$value2 = String::random();
		$model = new MockModelWithoutColumns(1);
		$model->record['field1'] = $value1;
		$model->record['field2'] = $value2;
		$model->commit();
		$model = new MockModelWithoutColumns(1);
		$this->assertEquals($value1, $model->record['field1']);
		$this->assertEquals($value2, $model->record['field2']);
	}

	public function testCommitIncrement()
	{
		$model = new MockModelWithoutColumns(1);
		$model->record['field1'] = 100;;
		$model->commit();
		$model = new MockModelWithoutColumns(1);
		$model->record['field1'] = '++';
		$model->commit();
		$model = new MockModelWithoutColumns(1);
		$this->assertEquals(101, $model->record['field1']);
	}

	public function testCommitUpdatedID()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$value = String::random();
		$model = new MockModel(1);
		$model->record['field1'] = $value;
		$model->commit();
		$model = new MockModel(1);
		$this->assertEquals($value, $model->record['field1']);
		$this->assertEquals(1, $model->record['updated_id']);
	}

	public function testCommitCreatedID()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$value = String::random();
		$model = new MockModel();
		$model->record['field1'] = $value;
		$id = $model->commit();
		$model = new MockModel($id);
		$this->assertEquals(1, $model->record['created_id']);
	}

	// Doesn't test against actual PostgreSQL instance, just for valid syntax
	public function testCommitInsertPostgreSQL()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$value = String::random();
		$model = new MockModel();
		$model->mysql = false;
		$model->postgresql = true;
		$model->record['field1'] = $value;

		try
		{
			$model->commit();
		}
		catch (Exception $e)
		{

		}

		$this->assertRegExp('/RETURNING id/', $model->db->results->queryString);
	}

	// Doesn't test against actual PostgreSQL instance, just for valid syntax
	public function testCommitUpdatePostgreSQL()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$value = String::random();
		$model = new MockModel(1);
		$model->mysql = false;
		$model->postgresql = true;
		$model->record['field1'] = $value;

		try
		{
			$model->commit();
		}
		catch (Exception $e)
		{

		}

		$model = new MockModel(1);
		$this->assertEquals($value, $model->record['field1']);
	}

	public function testCommitNothing()
	{
		$model = new MockModel();
		$this->assertFalse($model->commit());
	}

	public function testDeleteLogical()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$model = new MockModel(1);
		$model->delete();
		$model = new MockModel(1);
		$this->assertEquals([], $model->record);
	}

	public function testDeleteActual()
	{
		$model = new MockModelWithoutColumns(2);
		$model->delete();
		$model = new MockModelWithoutColumns(2);
		$this->assertEquals(0, $model->count());
	}

	public function testDeleteNothing()
	{
		$model = new MockModelWithoutColumns(100);
		$this->assertFalse($model->delete());
	}

	public function testLoadParametersWithString()
	{
		$model = new MockModel();
		$this->assertFalse($model->loadParameters(''));
	}

	public function testMultipleQueueInsert()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$model = new MockModel('count');
		$count = $model->record['count'];
		$model = new MockModel();

		for ($i = 0; $i < 5; $i++)
		{
			$model->record['field1'] = String::random();
			$model->record['updated_id'] = 1;
			$model->queue();
		}

		$model->commit();
		$model = new MockModel('count');
		$this->assertEquals($count + 5, $model->record['count']);
	}

	public function testMultipleQueueUpdate()
	{
		$_SESSION['__pickles']['security']['user_id'] = 1;
		$model = new MockModel();

		for ($i = 3; $i <= 5; $i++)
		{
			$model->record['id'] = $i;
			$model->record['field1'] = String::random();
			$model->record['updated_id'] = 1;
			$model->queue();
		}

		$model->commit();
	}
}

?>
