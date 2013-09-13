<?php

/**
 * Model Parent Class for PICKLES
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <pickles@joshtronic.com>
 * @copyright Copyright 2007-2013, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      https://github.com/joshtronic/pickles
 */

/**
 * Model Class
 *
 * This is a parent class that all PICKLES data models should be extending. When
 * using the class as designed, objects will function as active record pattern
 * objects.
 */
class Model extends Object
{
	// {{{ Properties

	/**
	 * Model Name
	 *
	 * @access private
	 * @var    string
	 */
	private $model = null;

	/**
	 * Database Object
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Columns
	 *
	 * Mapping of key columns for the table.
	 *
	 * @access protected
	 * @var    array
	 */
	protected $columns = null;

	/**
	 * Cache Object
	 *
	 * @access
	 * @var    object
	 */
	protected $cache = null;

	/**
	 * Whether or not to use cache
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $use_cache = false;

	/**
	 * SQL Array
	 *
	 * @access private
	 * @var    array
	 */
	private $sql = array();

	/**
	 * Input Parameters Array
	 *
	 * @access private
	 * @var    array
	 */
	private $input_parameters = array();

	/**
	 * Insert Priority
	 *
	 * Defaults to false (normal priority) but can be set to "low" or "high"
	 *
	 * @access protected
	 * @var    string
	 */
	protected $priority = false;

	/**
	 * Delayed Insert
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $delayed = false;

	/**
	 * Ignore Unique Index
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $ignore = false;

	/**
	 * Replace instead of Insert/Update?
	 *
	 * @access protected
	 * @var    boolean
	 */
	protected $replace = false;

	/**
	 * Field List
	 *
	 * SQL: SELECT
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $fields = '*';

	/**
	 * Table Name
	 *
	 * SQL: FROM
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $table = false;

	/**
	 * Joins
	 *
	 * SQL: JOIN
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $joins = false;

	/**
	 * [Index] Hints
	 *
	 * SQL: USE INDEX
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $hints = false;

	/**
	 * Conditions
	 *
	 * SQL: WHERE
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $conditions = false;

	/**
	 * Group
	 *
	 * SQL: GROUP BY
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $group = false;

	/**
	 * Having
	 *
	 * SQL: HAVING
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $having = false;

	/**
	 * Order
	 *
	 * SQL: ORDER BY
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $order = false;

	/**
	 * Limit
	 *
	 * SQL: LIMIT
	 *
	 * @access protected
	 * @var    mixed
	 */
	protected $limit = false;

	/**
	 * Offset
	 *
	 * SQL: OFFSET
	 *
	 * @access protected
	 * @var    mixed (string or array)
	 */
	protected $offset = false;

	/**
	 * Query Results
	 *
	 * @access protected
	 * @var    array
	 */
	protected $results = null;

	/**
	 * Index
	 *
	 * @var integer
	 */
	private $index = null;

	/**
	 * Record
	 *
	 * @access private
	 * @var    array
	 */
	public $record = null;

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = null;

	/**
	 * Original Record
	 *
	 * @access private
	 * @var    array
	 */
	private $original = null;

	/**
	 * Iterate
	 *
	 * Used to hold the status during a walk()
	 *
	 * @access private
	 * @var    boolean
	 */
	private $iterate = false;

	/**
	 * Snapshot
	 *
	 * Snapshot of the object properties
	 *
	 * @access private
	 * @var    array
	 */
	private $snapshot = array();

	/**
	 * MySQL?
	 *
	 * Whether or not we're using MySQL
	 *
	 * @access private
	 * @var    boolean
	 */
	private $mysql = false;

	/**
	 * PostgreSQL?
	 *
	 * Whether or not we're using PostgreSQL
	 *
	 * @access private
	 * @var    boolean
	 */
	private $postgresql = false;

	/**
	 * Commit Type
	 *
	 * Indicates what we want to commit. Defaults to a single row commit, any
	 * calls to queue() will force the commit to process the queue.
	 *
	 * @access private
	 * @var    string
	 */
	private $commit_type = 'row';

	// }}}
	// {{{ Class Constructor

	/**
	 * Constructor
	 *
	 * Creates a new (empty) object or populates the record set.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function __construct($type_or_parameters = null, $parameters = null)
	{
		// Errors if a table is not set. You're welcome, Geoff.
		if ($this->table == false)
		{
			throw new Exception('You must set the table variable');
		}

		// Runs the parent constructor so we have the config
		parent::__construct();

		// Gets an instance of the database and check which it is
		$this->db         = Database::getInstance();
		$this->use_cache  = $this->db->cache;
		$this->mysql      = ($this->db->driver == 'pdo_mysql');
		$this->postgresql = ($this->db->driver == 'pdo_pgsql');

		// Sets up the cache object and grabs the class name to use in our cache keys
		$this->cache = Cache::getInstance();
		$this->model = get_class($this);

		// Default column mapping
		$columns = array(
			'id'         => 'id',
			'created_at' => 'created_at',
			'created_id' => 'created_id',
			'updated_at' => 'updated_at',
			'updated_id' => 'updated_id',
			'deleted_at' => 'deleted_at',
			'deleted_id' => 'deleted_id',
			'is_deleted' => 'is_deleted',
		);

		// Grabs the config columns if no columns are set
		if ($this->columns === null && isset($this->db->columns))
		{
			$this->columns = $this->db->columns;
		}

		// Sets all but the `id` column to false
		if ($this->columns === false)
		{
			foreach ($columns as $column => $field)
			{
				if ($column != 'id')
				{
					$columns[$column] = false;
				}
			}
		}
		// Merges the model's columns with the defaults
		elseif (is_array($this->columns))
		{
			foreach ($this->columns as $column => $field)
			{
				$columns[$column] = $field;
			}
		}

		$this->columns = $columns;

		// Takes a snapshot of the [non-object] object properties
		foreach ($this as $variable => $value)
		{
			if (!in_array($variable, array('db', 'cache', 'config', 'snapshot')))
			{
				$this->snapshot[$variable] = $value;
			}
		}

		return $this->execute($type_or_parameters, $parameters);
	}

	// }}}
	// {{{ Database Execution Methods

	/**
	 * Execute
	 *
	 * Potentially populates the record set from the passed arguments.
	 *
	 * @param mixed $type_or_parameters optional type of query or parameters
	 * @param array $parameters optional data to create a query from
	 */
	public function execute($type_or_parameters = null, $parameters = null)
	{
		// Resets internal properties
		foreach ($this->snapshot as $variable => $value)
		{
			$this->$variable = $value;
		}

		// Builds out the query
		if ($type_or_parameters != null)
		{
			// Loads the parameters into the object
			if (is_array($type_or_parameters))
			{
				if (is_array($parameters))
				{
					throw new Exception('You cannot pass in 2 query parameter arrays');
				}

				if ($this->columns['is_deleted'])
				{
					$type_or_parameters['conditions'][$this->columns['is_deleted']] = '0';
				}

				$this->loadParameters($type_or_parameters);
			}
			elseif (is_array($parameters))
			{
				if ($this->columns['is_deleted'])
				{
					$parameters['conditions'][$this->columns['is_deleted']] = '0';
				}

				$this->loadParameters($parameters);
			}
			elseif (ctype_digit((string)$type_or_parameters))
			{
				$cache_key  = $this->model . '-' . $type_or_parameters;
				$parameters = array($this->columns['id'] => $type_or_parameters);

				if ($this->columns['is_deleted'])
				{
					$parameters[$this->columns['is_deleted']] = '0';
				}

				$this->loadParameters($parameters);
			}
			elseif (ctype_digit((string)$parameters))
			{
				$cache_key  = $this->model . '-' . $parameters;
				$parameters = array($this->columns['id'] => $parameters);

				if ($this->columns['is_deleted'])
				{
					$parameters[$this->columns['is_deleted']] = '0';
				}

				$this->loadParameters($parameters);
			}
			elseif ($this->columns['is_deleted'])
			{
				$this->loadParameters(array($this->columns['is_deleted'] => '0'));
			}

			// Starts with a basic SELECT ... FROM
			$this->sql = array(
				'SELECT ' . (is_array($this->fields) ? implode(', ', $this->fields) : $this->fields),
				'FROM '   . $this->table,
			);

			switch ($type_or_parameters)
			{
				// Updates query to use COUNT syntax
				case 'count':
					$this->sql[0] = 'SELECT COUNT(*) AS count';
					$this->generateQuery();
					break;

				// Adds the rest of the query
				case 'all':
				case 'list':
				case 'indexed':
				default:
					$this->generateQuery();
					break;
			}

			$query_database = true;

			if (isset($cache_key) && $this->use_cache)
			{
				$cached = $this->cache->get($cache_key);
			}

			if (isset($cached) && $cached)
			{
				$this->records = $cached;
			}
			else
			{
				$this->records = $this->db->fetch(
					implode(' ', $this->sql),
					(count($this->input_parameters) == 0 ? null : $this->input_parameters)
				);

				if (isset($cache_key) && $this->use_cache)
				{
					$this->cache->set($cache_key, $this->records);
				}
			}

			$index_records = in_array($type_or_parameters, array('list', 'indexed'));

			// Flattens the data into a list
			if ($index_records == true)
			{
				$list = array();

				foreach ($this->records as $record)
				{
					// Users the first value as the key and the second as the value
					if ($type_or_parameters == 'list')
					{
						$list[array_shift($record)] = array_shift($record);
					}
					// Uses the first value as the key
					else
					{
						$list[current($record)] = $record;
					}
				}

				$this->records = $list;
			}

			// Sets up the current record
			if (isset($this->records[0]))
			{
				$this->record = $this->records[0];
			}
			else
			{
				if ($index_records == true)
				{
					$this->record[key($this->records)] = current($this->records);
				}
				else
				{
					$this->record = $this->records;
				}
			}

			$this->index    = 0;
			$this->original = $this->records;
		}

		return true;
	}

	// }}}
	// {{{ SQL Generation Methods

	/**
	 * Generate Query
	 *
	 * Goes through all of the object variables that correspond with parts of
	 * the query and adds them to the master SQL array.
	 *
	 * @return boolean true
	 */
	private function generateQuery()
	{
		// Adds the JOIN syntax
		if ($this->joins != false)
		{
			if (is_array($this->joins))
			{
				foreach ($this->joins as $join => $tables)
				{
					$join_pieces = array((stripos('JOIN ', $join) === false ? 'JOIN' : strtoupper($join)));

					if (is_array($tables))
					{
						foreach ($tables as $table => $conditions)
						{
							$join_pieces[] = $table;

							if (is_array($conditions))
							{
								$type       = strtoupper(key($conditions));
								$conditions = current($conditions);

								$join_pieces[] = $type;
								$join_pieces[] = $this->generateConditions($conditions, true);
							}
							else
							{
								$join_pieces = $conditions;
							}
						}
					}
					else
					{
						$join_pieces[] = $tables;
					}
				}

				$this->sql[] = implode(' ', $join_pieces);

				unset($join_pieces);
			}
			else
			{
				$this->sql[] = (stripos('JOIN ', $join) === false ? 'JOIN ' : '') . $this->joins;
			}
		}

		// Adds the index hints
		if ($this->hints != false)
		{
			if (is_array($this->hints))
			{
				foreach ($this->hints as $hint => $columns)
				{
					if (is_array($columns))
					{
						$this->sql[] = $hint . ' (' . implode(', ', $columns) . ')';
					}
					else
					{
						$format = (stripos($columns, 'USE ') === false);

						$this->sql[] = ($format ? 'USE INDEX (' : '') . $columns . ($format ? ')' : '');
					}
				}
			}
			else
			{
				$format = (stripos($this->hints, 'USE ') === false);

				$this->sql[] = ($format ? 'USE INDEX (' : '') . $this->hints . ($format ? ')' : '');
			}
		}

		// Adds the WHERE conditionals
		if ($this->conditions != false)
		{
			$use_id = true;

			foreach ($this->conditions as $column => $value)
			{
				if (!is_int($column))
				{
					$use_id = false;
				}
			}

			if ($use_id)
			{
				$this->conditions = array($this->columns['id'] => $this->conditions);
			}

			$this->sql[] = 'WHERE ' . (is_array($this->conditions) ? $this->generateConditions($this->conditions) : $this->conditions);
		}

		// Adds the GROUP BY syntax
		if ($this->group != false)
		{
			$this->sql[] = 'GROUP BY ' . (is_array($this->group) ? implode(', ', $this->group) : $this->group);
		}

		// Adds the HAVING conditions
		if ($this->having != false)
		{
			$this->sql[] = 'HAVING ' . (is_array($this->having) ? $this->generateConditions($this->having) : $this->having);
		}

		// Adds the ORDER BY syntax
		if ($this->order != false)
		{
			$this->sql[] = 'ORDER BY ' . (is_array($this->order) ? implode(', ', $this->order) : $this->order);
		}

		// Adds the LIMIT syntax
		if ($this->limit != false)
		{
			$this->sql[] = 'LIMIT ' . (is_array($this->limit) ? implode(', ', $this->limit) : $this->limit);
		}

		// Adds the OFFSET syntax
		if ($this->offset != false)
		{
			$this->sql[] = 'OFFSET ' . $this->offset;
		}

		return true;
	}

	/**
	 * Generate Conditions
	 *
	 * Generates the conditional blocks of SQL from the passed array of
	 * conditions. Supports as much as I could remember to implement. This
	 * method is utilized by both the WHERE and HAVING clauses.
	 *
	 * @param  array $conditions array of potentially nested conditions
	 * @param  boolean $inject_values whether or not to use input parameters
	 * @param  string $conditional syntax to use between conditions
	 * @return string $sql generated SQL for the conditions
	 */
	private function generateConditions($conditions, $inject_values = false, $conditional = 'AND')
	{
		$sql = '';

		foreach ($conditions as $key => $value)
		{
			$key = trim($key);

			if (strtoupper($key) == 'NOT')
			{
				$key = 'AND NOT';
			}

			// Checks if conditional to start recursion
			if (preg_match('/^(AND|&&|OR|\|\||XOR)( NOT)?$/i', $key))
			{
				if (is_array($value))
				{
					// Determines if we need to include ( )
					$nested = (count($value) > 1);

					$conditional = $key;

					$sql .= ' ' . ($sql == '' ? '' : $key) . ' ' . ($nested ? '(' : '');
					$sql .= $this->generateConditions($value, $inject_values, $conditional);
					$sql .= ($nested ? ')' : '');
				}
				else
				{
					$sql .= ' ' . ($sql == '' ? '' : $key) . ' ' . $value;
				}
			}
			else
			{
				if ($sql != '')
				{
					if (preg_match('/^(AND|&&|OR|\|\||XOR)( NOT)?/i', $key))
					{
						$sql .= ' ';
					}
					else
					{
						$sql .= ' ' . $conditional . ' ';
					}
				}

				// Checks for our keywords to control the flow
				$operator  = preg_match('/(<|<=|=|>=|>|!=|!|<>| LIKE)$/i', $key);
				$between   = preg_match('/ BETWEEN$/i', $key);
				$is_is_not = preg_match('/( IS| IS NOT)$/i', $key);

				// Checks for boolean and null
				$is_true  = ($value === true);
				$is_false = ($value === false);
				$is_null  = ($value === null);


				// Generates an IN statement
				if (is_array($value) && $between == false)
				{
					$sql .= $key . ' IN (';

					if ($inject_values == true)
					{
						$sql .= implode(', ', $value);
					}
					else
					{
						$sql .= implode(', ', array_fill(1, count($value), '?'));
						$this->input_parameters = array_merge($this->input_parameters, $value);
					}

					$sql .= ')';
				}
				else
				{
					// If the key is numeric it wasn't set, so don't use it
					if (is_numeric($key))
					{
						$sql .= $value;
					}
					else
					{
						// Omits the operator as the operator is there
						if ($operator == true || $is_is_not == true)
						{
							if ($is_true || $is_false || $is_null)
							{
								// Scrubs the operator if someone doesn't use IS / IS NOT
								if ($operator == true)
								{
									$key = preg_replace('/ ?(!=|!|<>)$/i',         ' IS NOT', $key);
									$key = preg_replace('/ ?(<|<=|=|>=| LIKE)$/i', ' IS',     $key);
								}

								$sql .= $key . ' ';

								if ($is_true)
								{
									$sql .= 'TRUE';
								}
								elseif ($is_false)
								{
									$sql .= 'FALSE';
								}
								else
								{
									$sql .= 'NULL';
								}
							}
							else
							{
								$sql .= $key . ' ';

								if ($inject_values == true)
								{
									$sql .= $value;
								}
								else
								{
									$sql .= '?';
									$this->input_parameters[] = $value;
								}
							}
						}
						// Generates a BETWEEN statement
						elseif ($between == true)
						{
							if (is_array($value))
							{
								// Checks the number of values, BETWEEN expects 2
								if (count($value) != 2)
								{
									throw new Exception('Between expects 2 values');
								}
								else
								{
									$sql .= $key . ' ';

									if ($inject_values == true)
									{
										$sql .= $value[0] . ' AND ' . $value[1];
									}
									else
									{
										$sql .= '? AND ?';
										$this->input_parameters = array_merge($this->input_parameters, $value);
									}
								}
							}
							else
							{
								throw new Exception('Between usage expects values to be in an array');
							}
						}
						else
						{
							$sql .= $key . ' ';

							// Checks if we're working with NULL values
							if ($is_true)
							{
								$sql .= 'IS TRUE';
							}
							elseif ($is_false)
							{
								$sql .= 'IS FALSE';
							}
							elseif ($is_null)
							{
								$sql .= 'IS NULL';
							}
							else
							{
								if ($inject_values == true)
								{
									$sql .= '= ' . $value;
								}
								else
								{
									$sql .= '= ?';
									$this->input_parameters[] = $value;
								}
							}
						}
					}
				}
			}
		}

		return $sql;
	}

	// }}}
	// {{{ Record Interaction Methods

	/**
	 * Count Records
	 *
	 * Counts the records
	 */
	public function count()
	{
		return count($this->records);
	}

	/**
	 * Sort Records
	 *
	 * Sorts the records by the specified index in the specified order.
	 *
	 * @param  string $index the index to be sorted on
	 * @param  string $order the direction to order
	 * @return boolean true
	 * @todo   Implement this method
	 */
	public function sort($index, $order = 'ASC')
	{
		return true;
	}

	/**
	 * Shuffle Records
	 *
	 * Sorts the records in a pseudo-random order.
	 *
	 * @return boolean true
	 * @todo   Implement this method
	 */
	public function shuffle()
	{
		return true;
	}

	/**
	 * Next Record
	 *
	 * Increment the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was next element
	 */
	public function next()
	{
		$return = (boolean)($this->record = next($this->records));

		if ($return == true)
		{
			$this->index++;
		}

		return $return;
	}

	/**
	 * Previous Record
	 *
	 * Decrement the record array to the next member of the record set.
	 *
	 * @return boolean whether or not there was previous element
	 */
	public function prev()
	{
		$return = (boolean)($this->record = prev($this->records));

		if ($return == true)
		{
			$this->index--;
		}

		return $return;
	}

	/**
	 * Reset Record
	 *
	 * Set the pointer to the first element of the record set.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function reset()
	{
		$return = (boolean)($this->record = reset($this->records));

		if ($return == true)
		{
			$this->index = 0;
		}

		return $return;
	}

	/**
	 * First Record
	 *
	 * Alias of reset(). "first" is more intuitive to me, but reset stays in
	 * line with the built in PHP functions. Not sure why I'd want to add some
	 * consistency to one of the most inconsistent languages.
	 *
	 * @return boolean whether or not records is an array (and could be reset)
	 */
	public function first()
	{
		return $this->reset();
	}

	/**
	 * End Record
	 *
	 * Set the pointer to the last element of the record set.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function end()
	{
		$return = (boolean)($this->record = end($this->records));

		if ($return == true)
		{
			$this->index = $this->count() - 1;
		}

		return $return;
	}

	/**
	 * Last Record
	 *
	 * Alias of end(). "last" is more intuitive to me, but end stays in line
	 * with the built in PHP functions.
	 *
	 * @return boolean whether or not records is an array (and end() worked)
	 */
	public function last()
	{
		return $this->end();
	}

	/**
	 * Walk Records
	 *
	 * Returns the current record and advances to the next. Built to allow for
	 * simplified code when looping through a record set.
	 *
	 * @return mixed either an array of the current record or false
	 * @todo   Does not currently support "indexed" or "list" return types
	 */
	public function walk()
	{
		// Checks if we should start iterating, solves off by one issues with next()
		if ($this->iterate == false)
		{
			$this->iterate = true;

			// Resets the records, saves calling reset() when walking multiple times
			$this->reset();
		}
		else
		{
			$this->next();
		}

		return $this->record;
	}

	/**
	 * Queue Record
	 *
	 * Stashes the current record and creates an empty record ready to be
	 * manipulated. Eliminates looping through records and INSERTing each one
	 * separately and/or the need for helper methods in the models.
	 */
	public function queue()
	{
		$this->commit_type = 'queue';
		$this->records[]   = $this->record;
		$this->record      = null;
	}

	// }}}
	// {{{ Record Manipulation Methods

	/**
	 * Commit
	 *
	 * Inserts or updates a record in the database.
	 *
	 * @return boolean results of the query
	 */
	public function commit()
	{
		// Multiple row query / queries
		if ($this->commit_type == 'queue')
		{
			$update     = false;
			$cache_keys = array();

			/**
			 * @todo I outta loop through twice to determine if it's an INSERT
			 * or an UPDATE. As it stands, you could run into a scenario where
			 * you could have a mixed lot that would attempt to build out a
			 * query with both INSERT and UPDATE syntax and would probably cause
			 * a doomsday scenario for our universe.
			 */
			foreach ($this->records as $record)
			{
				// Performs an UPDATE with multiple queries
				if (array_key_exists($this->columns['id'], $record))
				{
					$update = true;

					if (!isset($sql))
					{
						$sql              = '';
						$input_parameters = array();
					}

					$update_fields = array();

					foreach ($record as $field => $value)
					{
						if ($field != $this->columns['id'])
						{
							$update_fields[]    = $field . ' = ?';
							$input_parameters[] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
						}
						else
						{
							$cache_keys[] = $this->model . '-' . $value;
						}
					}

					// @todo Check if the column was passed in
					if ($this->columns['updated_at'] != false)
					{
						$update_fields[]    = $this->columns['updated_at'] . ' = ?';
						$input_parameters[] = Time::timestamp();
					}

					// @todo Check if the column was passed in
					if ($this->columns['updated_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						$update_fields[]    = $this->columns['updated_id'] . ' = ?';
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
					}

					if ($sql != '')
					{
						$sql .= '; ';
					}

					$sql .= 'UPDATE ' . $this->table . ' SET ' . implode(', ', $update_fields) . ' WHERE ';

					if (isset($record[$this->columns['id']]))
					{
						$sql                .= $this->columns['id'] . ' = ?';
						$input_parameters[]  = $record[$this->columns['id']];
					}
					else
					{
						throw new Exception('Missing UID field');
					}
				}
				// Performs a multiple row INSERT
				else
				{
					if (!isset($sql))
					{
						$field_count   = count($record);
						$insert_fields = array_keys($record);

						if ($this->columns['created_at'] != false)
						{
							$insert_fields[] = $this->columns['created_at'];
							$field_count++;
						}

						if ($this->columns['created_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
						{
							$insert_fields[] = $this->columns['created_id'];
							$field_count++;
						}

						$values           = '(' . implode(', ', array_fill(0, $field_count, '?')) . ')';
						$input_parameters = array();

						// INSERT INTO ...
						$sql = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $insert_fields) . ') VALUES ' . $values;
					}
					else
					{
						$sql .= ', ' . $values;
					}

					$record_field_count = count($record);

					foreach ($record as $variable => $value)
					{
						$input_parameters[] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
					}

					// @todo Check if the column was passed in
					if ($this->columns['created_at'] != false)
					{
						$input_parameters[] = Time::timestamp();
						$record_field_count++;
					}

					// @todo Check if the column was passed in
					if ($this->columns['created_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
						$record_field_count++;
					}

					if ($record_field_count != $field_count)
					{
						throw new Exception('Record does not match the excepted field count');
					}
				}
			}

			$results = $this->db->execute($sql . ';', $input_parameters);

			// Clears the cache
			if ($update && $this->use_cache)
			{
				$this->cache->delete($cache_keys);
			}

			return $results;
		}
		// Single row INSERT or UPDATE
		elseif (count($this->record) > 0)
		{
			// Determines if it's an UPDATE or INSERT
			$update = (isset($this->record[$this->columns['id']]) && trim($this->record[$this->columns['id']]) != '');

			// Starts to build the query, optionally sets PRIORITY, DELAYED and IGNORE syntax
			if ($this->replace === true && $this->mysql)
			{
				$sql = 'REPLACE';

				if (strtoupper($this->priority) == 'LOW')
				{
					$sql .= ' LOW_PRIORITY';
				}
				elseif ($this->delayed == true)
				{
					$sql .= ' DELAYED';
				}

				$sql .= ' INTO ' . $this->table;
			}
			else
			{
				if ($update == true)
				{
					$sql = 'UPDATE';
				}
				else
				{
					$sql = 'INSERT';

					// PRIORITY syntax takes priority over DELAYED
					if ($this->mysql)
					{
						if ($this->priority !== false && in_array(strtoupper($this->priority), array('LOW', 'HIGH')))
						{
							$sql .= ' ' . strtoupper($this->priority) . '_PRIORITY';
						}
						elseif ($this->delayed == true)
						{
							$sql .= ' DELAYED';
						}

						if ($this->ignore == true)
						{
							$sql .= ' IGNORE';
						}
					}

					$sql .= ' INTO';
				}

				$sql .= ' ' . $this->table . ($update ? ' SET ' : ' ');
			}

			$input_parameters = null;

			// Limits the columns being updated
			$record = ($update ? array_diff_assoc($this->record, isset($this->original[$this->index]) ? $this->original[$this->index] : array()) : $this->record);

			// Makes sure there's something to INSERT or UPDATE
			if (count($record) > 0)
			{
				$insert_fields = array();

				// Loops through all the columns and assembles the query
				foreach ($record as $column => $value)
				{
					if ($column != $this->columns['id'])
					{
						if ($update == true)
						{
							if ($input_parameters != null)
							{
								$sql .= ', ';
							}

							$sql .= $column . ' = ';

							if (in_array($value, array('++', '--')))
							{
								$sql  .= $column . ' ' . substr($value, 0, 1) . ' ?';
								$value = 1;
							}
							else
							{
								$sql .= '?';
							}
						}
						else
						{
							$insert_fields[] = $column;
						}

						$input_parameters[] = (is_array($value) ? (JSON_AVAILABLE ? json_encode($value) : serialize($value)) : $value);
					}
				}

				// If it's an UPDATE tack on the ID
				if ($update == true)
				{
					if ($this->columns['updated_at'] != false)
					{
						if ($input_parameters != null)
						{
							$sql .= ', ';
						}

						$sql                .= $this->columns['updated_at'] . ' = ?';
						$input_parameters[]  = Time::timestamp();
					}

					if ($this->columns['updated_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						if ($input_parameters != null)
						{
							$sql .= ', ';
						}

						$sql                .= $this->columns['updated_id'] . ' = ?';
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
					}

					$sql                .= ' WHERE ' . $this->columns['id'] . ' = ?' . ($this->mysql ? ' LIMIT 1' : '') . ';';
					$input_parameters[]  = $this->record[$this->columns['id']];
				}
				else
				{
					if ($this->columns['created_at'] != false)
					{
						$insert_fields[]    = $this->columns['created_at'];
						$input_parameters[] = Time::timestamp();
					}

					if ($this->columns['created_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
					{
						$insert_fields[]    = $this->columns['created_id'];
						$input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
					}

					$sql .= '(' . implode(', ', $insert_fields) . ') VALUES (' . implode(', ', array_fill(0, count($input_parameters), '?')) . ')';

					// PDO::lastInsertId() doesn't work so we return the ID with the query
					if ($this->postgresql)
					{
						$sql .= ' RETURNING ' . $this->columns['id'];
					}

					$sql .= ';';
				}

				// Executes the query
				if ($this->postgresql && $update == false)
				{
					$results = $this->db->fetch($sql, $input_parameters);

					return $results[0][$this->columns['id']];
				}
				else
				{
					$results = $this->db->execute($sql, $input_parameters);

					// Clears the cache
					if ($update && $this->use_cache)
					{
						$this->cache->delete($this->model . '-' . $this->record[$this->columns['id']]);
					}

					return $results;
				}
			}
		}

		return false;
	}

	/**
	 * Delete Record
	 *
	 * Deletes the current record from the database.
	 *
	 * @return boolean status of the query
	 */
	public function delete()
	{
		if (isset($this->record[$this->columns['id']]))
		{
			// Logical deletion
			if ($this->columns['is_deleted'])
			{
				$sql              = 'UPDATE ' . $this->table . ' SET ' . $this->columns['is_deleted'] . ' = ?';
				$input_parameters = array('1');

				if ($this->columns['deleted_at'])
				{
					$sql                .= ', ' . $this->columns['deleted_at'] . ' = ?';
					$input_parameters[]  = Time::timestamp();
				}

				if ($this->columns['deleted_id'] && isset($_SESSION['__pickles']['security']['user_id']))
				{
					$sql                .= ', ' . $this->columns['deleted_id'] . ' = ?';
					$input_parameters[]  = $_SESSION['__pickles']['security']['user_id'];
				}

				$sql .= ' WHERE ' . $this->columns['id'] . ' = ?';
			}
			// For reals deletion
			else
			{
				$sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->columns['id'] . ' = ?' . ($this->mysql ? ' LIMIT 1' : '') . ';';
			}

			$input_parameters[] = $this->record[$this->columns['id']];
			$results            = $this->db->execute($sql, $input_parameters);

			// Clears the cache
			if ($this->use_cache)
			{
				$this->cache->delete($this->model . '-' . $this->record[$this->columns['id']]);
			}

			return $results;
		}
		else
		{
			return false;
		}
	}

	// }}}
	// {{{ Utility Methods

	/**
	 * Load Parameters
	 *
	 * Loads the passed parameters back into the object.
	 *
	 * @access private
	 * @param  array $parameters key / value list
	 * @param  boolean whether or not the parameters were loaded
	 */
	private function loadParameters($parameters)
	{
		if (is_array($parameters))
		{
			$conditions = true;

			// Adds the parameters to the object
			foreach ($parameters as $key => $value)
			{
				// Clean up the variable just in case
				$key = trim(strtolower($key));

				// Assigns valid keys to the appropriate class property
				if (in_array($key, array('fields', 'table', 'joins', 'hints', 'conditions', 'group', 'having', 'order', 'limit', 'offset')))
				{
					$this->$key = $value;
					$conditions = false;
				}
			}

			// If no valid properties were found, assume it's the conditionals
			if ($conditions == true)
			{
				$this->conditions = $parameters;
			}

			return true;
		}

		return false;
	}

	/**
	 * Unescape String
	 *
	 * Assuming magic quotes is turned on, strips slashes from the string.
	 *
	 * @access protected
	 * @param  string $value string to be unescaped
	 * @return string unescaped string
	 */
	protected function unescape($value)
	{
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}

		return $value;
	}

	// }}}
}

?>
