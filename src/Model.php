<?php

/**
 * Model Parent Class
 *
 * Licensed under The MIT License
 * Redistribution of these files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2014, Josh Sherman
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @link      https://github.com/joshtronic/pickles
 * @package   Pickles
 */

namespace Pickles;

/**
 * Model Class
 *
 * This is a parent class that all Pickles data models should be extending.
 * When using the class as designed, objects will function as active record
 * pattern objects.
 */
class Model extends Object
{
    // {{{ Properties

    /**
     * Model Name
     *
     * @var string
     */
    public $model = null;

    /**
     * Columns
     *
     * Mapping of key columns for the table.
     *
     * @var array
     */
    public $columns = null;

    /**
     * Whether or not to use cache
     *
     * @var boolean
     */
    public $use_cache = false;

    /**
     * SQL Array
     *
     * @var array
     */
    public $sql = [];

    /**
     * Input Parameters Array
     *
     * @var array
     */
    public $input_parameters = [];

    /**
     * Insert Priority
     *
     * Defaults to false (normal priority) but can be set to "low" or "high"
     *
     * @var string
     */
    public $priority = false;

    /**
     * Delayed Insert
     *
     * @var boolean
     */
    public $delayed = false;

    /**
     * Ignore Unique Index
     *
     * @var boolean
     */
    public $ignore = false;

    /**
     * Replace instead of Insert/Update?
     *
     * @var boolean
     */
    public $replace = false;

    /**
     * Field List
     *
     * SQL: SELECT
     *
     * @var mixed, string or array
     */
    public $fields = '*';

    /**
     * Table Name
     *
     * SQL: FROM
     *
     * @var string
     */
    public $table = false;

    /**
     * Conditions
     *
     * SQL: WHERE
     *
     * @var array
     */
    public $conditions = false;

    /**
     * Group
     *
     * SQL: GROUP BY
     *
     * @var mixed, string or array
     */
    public $group = false;

    /**
     * Having
     *
     * SQL: HAVING
     *
     * @var mixed, string or array
     */
    public $having = false;

    /**
     * Order
     *
     * SQL: ORDER BY
     *
     * @var mixed, string or array
     */
    public $order = false;

    /**
     * Limit
     *
     * SQL: LIMIT
     *
     * @var mixed, integer or array
     */
    public $limit = false;

    /**
     * Offset
     *
     * SQL: OFFSET
     *
     * @var integer
     */
    public $offset = false;

    /**
     * Query Results
     *
     * @var array
     */
    public $results = null;

    /**
     * Index
     *
     * @var integer
     */
    public $index = null;

    /**
     * Record
     *
     * @var array
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
     * @var array
     */
    public $original = null;

    /**
     * Iterate
     *
     * Used to hold the status during a walk()
     *
     * @var boolean
     */
    public $iterate = false;

    /**
     * Snapshot
     *
     * Snapshot of the object properties
     *
     * @var array
     */
    public $snapshot = [];

    /**
     * MySQL?
     *
     * Whether or not we're using MySQL
     *
     * @var boolean
     */
    public $mysql = false;

    /**
     * PostgreSQL?
     *
     * Whether or not we're using PostgreSQL
     *
     * @var boolean
     */
    public $postgresql = false;

    /**
     * Commit Type
     *
     * Indicates what we want to commit. Defaults to a single row commit, any
     * calls to queue() will force the commit to process the queue.
     *
     * @var string
     */
    public $commit_type = 'row';

    // }}}
    // {{{ Class Constructor

    /**
     * Constructor
     *
     * Creates a new (empty) object or populates the record set.
     *
     * @param mixed $type_or_parameters optional type of query or parameters
     * @param array $parameters optional data to create a query from
     * @param string $passed_key optional key to use for caching
     */
    public function __construct($type_or_parameters = null, $parameters = null, $passed_key = null)
    {
        // Errors if a table is not set. You're welcome, Geoff.
        if ($this->table == false)
        {
            throw new \Exception('You must set the table variable.');
        }

        // Runs the parent constructor so we have the config
        parent::__construct(['cache', 'db']);

        // Interrogates our database object
        $this->use_cache  = $this->db->cache;
        $this->mysql      = ($this->db->driver == 'pdo_mysql');
        $this->postgresql = ($this->db->driver == 'pdo_pgsql');

        // Grabs the class name to use in our cache keys
        $this->model = get_class($this);

        // Default column mapping
        $columns = [
            'id'         => 'id',
            'created_at' => 'created_at',
            'created_id' => 'created_id',
            'updated_at' => 'updated_at',
            'updated_id' => 'updated_id',
            'deleted_at' => 'deleted_at',
            'deleted_id' => 'deleted_id',
            'is_deleted' => 'is_deleted',
        ];

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
            if (!in_array($variable, ['db', 'cache', 'config', 'snapshot']))
            {
                $this->snapshot[$variable] = $value;
            }
        }

        return $this->execute($type_or_parameters, $parameters, $passed_key);
    }

    // }}}
    // {{{ Database Execution Methods

    /**
     * Execute
     *
     * Potentially populates the record set from the passed arguments.
     *
     * @param mixed $type_or_parameters optional type of query or parameters
     * @param mixed $parameter_or_key optional data to create query or cache key
     * @param string $passed_key optional key to use for caching
     */
    public function execute($type_or_parameters = null, $parameters_or_key = null, $passed_key = null)
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
                if (is_array($parameters_or_key))
                {
                    throw new \Exception('You cannot pass in 2 query parameter arrays.');
                }

                $this->prepareParameters($type_or_parameters);

                if ($this->use_cache
                    && isset($type_or_parameters['conditions'][$this->columns['id']])
                    && count($type_or_parameters) == 1
                    && count($type_or_parameters['conditions']) == 1)
                {
                    $cache_keys     = [];
                    $sorted_records = [];

                    if (!is_array($type_or_parameters['conditions'][$this->columns['id']]))
                    {
                        $type_or_parameters['conditions'][$this->columns['id']] = [$type_or_parameters['conditions'][$this->columns['id']]];
                    }

                    foreach ($type_or_parameters['conditions'][$this->columns['id']] as $id)
                    {
                        $cache_keys[]        = strtoupper($this->model) . '-' . $id;
                        $sorted_records[$id] = true;
                    }

                    $cached        = $this->cache->get($cache_keys);
                    $partial_cache = [];

                    if ($cached !== false)
                    {
                        foreach ($cached as $record)
                        {
                            $partial_cache[$record['id']] = $record;
                        }
                    }

                    unset($cached);

                    foreach ($type_or_parameters['conditions'][$this->columns['id']] as $key => $id)
                    {
                        if (isset($partial_cache[$id]))
                        {
                            unset($type_or_parameters['conditions'][$this->columns['id']][$key]);
                        }
                    }

                    if (count($type_or_parameters['conditions'][$this->columns['id']]) == 0)
                    {
                        $cache_key = true;
                        $cached    = array_values($partial_cache);
                    }
                }

                if ($this->columns['is_deleted'])
                {
                    $type_or_parameters['conditions'][$this->columns['is_deleted']] = '0';
                }

                $this->loadParameters($type_or_parameters);
            }
            elseif (is_array($parameters_or_key))
            {
                $this->prepareParameters($parameters_or_key);

                // @todo Fix cache merging to allow for this
                /*
                if ($this->use_cache
                    && isset($parameters_or_key['conditions'][$this->columns['id']])
                    && count($parameters_or_key) == 1
                    && count($parameters_or_key['conditions']) == 1)
                {
                    $cache_keys     = [];
                    $sorted_records = [];

                    foreach ($parameters_or_key['conditions'][$this->columns['id']] as $id)
                    {
                        $cache_keys[]        = strtoupper($this->model) . '-' . $id;
                        $sorted_records[$id] = true;
                    }

                    $cached        = $this->cache->get($cache_keys);
                    $partial_cache = [];

                    if ($cached !== false)
                    {
                        foreach ($cached as $record)
                        {
                            $partial_cache[$record['id']] = $record;
                        }
                    }

                    unset($cached);

                    foreach ($parameters_or_key['conditions'][$this->columns['id']] as $key => $id)
                    {
                        if (isset($partial_cache[$id]))
                        {
                            unset($parameters_or_key['conditions'][$this->columns['id']][$key]);
                        }
                    }

                    if (count($parameters_or_key['conditions'][$this->columns['id']]) == 0)
                    {
                        $cache_key = true;
                        $cached    = array_values($partial_cache);
                    }
                }
                */

                if ($this->columns['is_deleted'])
                {
                    $parameters_or_key['conditions'][$this->columns['is_deleted']] = '0';
                }

                $this->loadParameters($parameters_or_key);
            }
            elseif (ctype_digit((string)$type_or_parameters))
            {
                $cache_key         = strtoupper($this->model) . '-' . $type_or_parameters;
                $parameters_or_key = [$this->columns['id'] => $type_or_parameters];

                if ($this->columns['is_deleted'])
                {
                    $parameters_or_key[$this->columns['is_deleted']] = '0';
                }

                $this->loadParameters($parameters_or_key);
            }
            elseif (ctype_digit((string)$parameters_or_key))
            {
                // @todo Fix cache merging to allow for this
                //$cache_key = strtoupper($this->model) . '-' . $parameters_or_key;

                $parameters_or_key = [$this->columns['id'] => $parameters_or_key];

                if ($this->columns['is_deleted'])
                {
                    $parameters_or_key[$this->columns['is_deleted']] = '0';
                }

                $this->loadParameters($parameters_or_key);
            }
            elseif ($this->columns['is_deleted'])
            {
                $this->loadParameters([$this->columns['is_deleted'] => '0']);
            }

            if (is_string($passed_key))
            {
                $cache_key = $passed_key;
            }

            // Starts with a basic SELECT ... FROM
            $this->sql = [
                'SELECT ' . (is_array($this->fields) ? implode(', ', $this->fields) : $this->fields),
                'FROM '   . $this->table,
            ];

            // Updates query to use COUNT syntax
            if ($type_or_parameters == 'count')
            {
                $this->sql[0] = 'SELECT COUNT(*) AS count';
                $this->generateQuery();
            }
            // Adds the rest of the query
            elseif (!isset($cache_key) || $cache_key !== true)
            {
                $this->generateQuery();
            }

            if (isset($cache_key) && $this->use_cache && !isset($cached))
            {
                $cached = $this->cache->get($cache_key);
            }

            if (isset($cached) && $cached !== false)
            {
                $this->records = $cached;
            }
            else
            {
                $this->records = $this->db->fetch(
                    implode(' ', $this->sql),
                    (count($this->input_parameters) == 0 ? null : $this->input_parameters)
                );

                if (isset($partial_cache) && count($this->records))
                {
                    $records = array_merge($partial_cache, $this->records);

                    if (isset($sorted_records))
                    {
                        foreach ($records as $record)
                        {
                            $sorted_records[$record['id']] = $record;
                        }

                        $records = $sorted_records;
                    }

                    $this->records = $records;
                }

                if ($this->use_cache)
                {
                    if (isset($cache_key))
                    {
                        if ($passed_key)
                        {
                            $cache_value = $this->records;
                        }
                        elseif (isset($this->records[0]))
                        {
                            $cache_value = $this->records[0];
                        }

                        // Only set the value for non-empty records. Caching
                        // values that are empty could be caused by querying
                        // records that don't exist at the moment, but could
                        // exist in the future. INSERTs do not do any sort of
                        // cache invalidation at this time.
                        if (isset($cache_value))
                        {
                            $this->cache->set($cache_key, $cache_value);
                        }
                    }
                    elseif (isset($cache_keys))
                    {
                        // @todo Move to Memcached extension and switch to use setMulti()
                        foreach ($this->records as $record)
                        {
                            if (isset($record['id']))
                            {
                                $this->cache->set(strtoupper($this->model) . '-' . $record['id'], $record);
                            }
                        }
                    }
                }
            }

            $index_records = in_array($type_or_parameters, ['list', 'indexed']);

            // Flattens the data into a list
            if ($index_records == true)
            {
                $list = [];

                foreach ($this->records as $record)
                {
                    // Uses the first value as the key and the second as the value
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

            if (!preg_match('/^[0-9]+$/', implode('', array_keys($this->records))))
            {
                $this->records = [$this->records];
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
     * @return array $this->sql an array of SQL parts
     */
    public function generateQuery()
    {
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
                $this->conditions = [$this->columns['id'] => $this->conditions];
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

        return $this->sql;
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
    public function generateConditions($conditions, $inject_values = false, $conditional = 'AND')
    {
        $sql = '';

        foreach ($conditions as $key => $value)
        {
            $key = trim($key);

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

            // Generates an in statement
            if (is_array($value) && $between == false)
            {
                $sql .= $key . ' in (';

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
                    // Generates a between statement
                    elseif ($between == true)
                    {
                        if (is_array($value))
                        {
                            // Checks the number of values, between expects 2
                            if (count($value) != 2)
                            {
                                throw new \Exception('BETWEEN expects an array with 2 values.');
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
                            throw new \Exception('BETWEEN expects an array.');
                        }
                    }
                    else
                    {
                        $sql .= $key . ' ';

                        // Checks if we're working with constants
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
        if (isset($this->records[0]) &&  $this->records[0] == [])
        {
            return 0;
        }
        else
        {
            return count($this->records);
        }
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
    public function sort($index, $order = 'asc')
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
     * Last record
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
     * @todo   does not currently support "indexed" or "list" return types
     */
    public function walk()
    {
        // checks if we should start iterating, solves off by one issues with next()
        if ($this->iterate == false)
        {
            $this->iterate = true;

            // resets the records, saves calling reset() when walking multiple times
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
     * manipulated. Eliminates looping through records and inserting each one
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
     * INSERTs or UPDATEs a record in the database.
     *
     * @return boolean results of the query
     */
    public function commit()
    {
        // Multiple row query / queries
        if ($this->commit_type == 'queue')
        {
            $update     = false;
            $cache_keys = [];

            /**
             * @todo I outta loop through twice to determine if it's an INSERT
             *       or an UPDATE. As it stands, you could run into a scenario
             *       where you could have a mixed lot that would attempt to
             *       build out a query with both INSERT and UPDATE syntax and
             *       would probably cause a doomsday scenario for our universe.
             * @todo Doesn't play nice with ->walk() at all. Ends up stuck in
             *       an infinite loop and never executes. Could be part of the
             *       aforementioned doomsday scenario and fortunately PHP isn't
             *       letting it happen thanks to memory constraints.
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
                        $input_parameters = [];
                    }

                    $update_fields = [];

                    foreach ($record as $field => $value)
                    {
                        if ($field != $this->columns['id'])
                        {
                            $update_fields[]    = $field . ' = ?';
                            $input_parameters[] = (is_array($value) ? json_encode($value) : $value);
                        }
                        else
                        {
                            $cache_keys[] = strtoupper($this->model) . '-' . $value;
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

                    $sql .= 'UPDATE ' . $this->table
                         .  ' SET ' . implode(', ', $update_fields)
                         .  ' WHERE ' .  $this->columns['id'] . ' = ?';

                    $input_parameters[] = $record[$this->columns['id']];
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
                        $input_parameters = [];

                        // INSERT INTO ...
                        $sql = 'INSERT INTO ' . $this->table . ' (' . implode(', ', $insert_fields) . ') VALUES ' . $values;
                    }
                    else
                    {
                        $sql .= ', ' . $values;
                    }

                    foreach ($record as $variable => $value)
                    {
                        $input_parameters[] = (is_array($value) ? json_encode($value) : $value);
                    }

                    // @todo Check if the column was passed in
                    if ($this->columns['created_at'] != false)
                    {
                        $input_parameters[] = Time::timestamp();
                    }

                    // @todo Check if the column was passed in
                    if ($this->columns['created_id'] != false && isset($_SESSION['__pickles']['security']['user_id']))
                    {
                        $input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
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
        else
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

                    // priority syntax takes priority over delayed
                    if ($this->mysql)
                    {
                        if ($this->priority !== false
                            && in_array(strtoupper($this->priority), ['LOW', 'HIGH']))
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
            $record = ($update ? array_diff_assoc(
                $this->record,
                isset($this->original[$this->index]) ? $this->original[$this->index] : []
            ) : $this->record);

            // Makes sure there's something to INSERT or UPDATE
            if (count($record) > 0)
            {
                if ($this->replace && $update)
                {
                    $update = false;
                }

                $insert_fields = [];

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

                            if (in_array($value, ['++', '--']))
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

                        $input_parameters[] = (is_array($value) ? json_encode($value) : $value);
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

                        $sql .= $this->columns['updated_id'] . ' = ?';

                        $input_parameters[] = $_SESSION['__pickles']['security']['user_id'];
                    }

                    $sql                .= ' WHERE ' . $this->columns['id'] . ' = ?' . ($this->mysql ? ' LIMIT 1' : '') . ';';
                    $input_parameters[]  = $this->record[$this->columns['id']];
                }
                else
                {
                    // @todo REPLACE should be grabbing the previous values so
                    //       that we're not wiping out pertinent data when the
                    //       internal columns are in use. This includes the
                    //       `id` column that is needed to keep it from doing
                    //       an INSERT instead of an UPDATE
                    if ($this->columns['created_at'] != false || $this->replace)
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

                    // PDO::lastInsertID() doesn't work so we return the ID with the query
                    if ($this->postgresql)
                    {
                        $sql .= ' RETURNING ' . $this->columns['id'];
                    }

                    $sql .= ';';
                }

                // Executes the query
                if ($this->postgresql && $update == false)
                {
                    return $this->db->fetch($sql, $input_parameters);
                }
                else
                {
                    $results = $this->db->execute($sql, $input_parameters);

                    // Clears the cache
                    if ($update && $this->use_cache)
                    {
                        $this->cache->delete(strtoupper($this->model) . '-' . $this->record[$this->columns['id']]);
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
     * DELETEs the current record from the database.
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
                $input_parameters = ['1'];

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
                $this->cache->delete(strtoupper($this->model) . '-' . $this->record[$this->columns['id']]);
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
     * Prepare Parameters
     *
     * Checks if the parameters array is only integers and reconstructs the
     * array with the proper conditions format.
     *
     * @param array $array parameters array, passed by reference
     */
    public function prepareParameters(&$parameters)
    {
        $all_integers = true;

        foreach ($parameters as $key => $value)
        {
            if (!ctype_digit((string)$key) || !ctype_digit((string)$value))
            {
                $all_integers = false;
            }
        }

        if ($all_integers)
        {
            $parameters = ['conditions' => [$this->columns['id'] => $parameters]];
        }
    }

    /**
     * Load Parameters
     *
     * Loads the passed parameters back into the object.
     *
     * @param array $parameters key / value list
     * @param boolean whether or not the parameters were loaded
     */
    public function loadParameters($parameters)
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
                if (in_array($key, ['fields', 'table', 'conditions', 'group', 'having', 'order', 'limit', 'offset']))
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
     * Field Values
     *
     * Pulls the value from a single field and returns an array without any
     * duplicates. Perfect for extracting foreign keys to use in later queries.
     *
     * @param  string $field field we want the values for
     * @return array values for the passed field
     */
    public function fieldvalues($field)
    {
        $values = [];

        foreach ($this->records as $record)
        {
            $values[] = $record[$field];
        }

        return array_unique($values);
    }

    // }}}
}

