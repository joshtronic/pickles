<?php

/**
 * Model Parent Class for PICKLES
 *
 * PHP version 5
 *
 * Licensed under the GNU General Public License Version 3
 * Redistribution of these files must retain the above copyright notice.
 *
 * @package   PICKLES
 * @author    Josh Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.gnu.org/licenses/gpl.html GPL v3
 * @link      http://phpwithpickles.org
 */

/**
 * Model Class
 *
 * This is a parent class that all PICKLES data models should be extending.
 * The only thing it does currently is establish a database object for the
 * data models to use.
 */
class Model extends Object
{
	/**
	 * Database
	 *
	 * @access protected
	 * @var    object
	 */
	protected $db = null;

	/**
	 * Table Name
	 *
	 * @access protected
	 * @var    string
	 */
	protected $table = null;

	/**
	 * Column List
	 *
	 * @access protected
	 * @var    mixed string, array
	 */
	protected $columns = '*';

	/**
	 * Order By Clause
	 *
	 * @access protected
	 * @var    mixed string, array
	 */
	protected $order_by = null;

	/**
	 * Limit Results
	 *
	 * @access protected
	 * @var    mixed integer, string or array
	 */
	protected $limit = null;

	/**
	 * Data
	 *
	 * @access protected
	 * @var    array
	 */
	protected $data = null;

	/**
	 * Record Array
	 *
	 * @access protected
	 * @var    array
	 */
	protected $record  = null;

	/**
	 * Records Array
	 *
	 * @access protected
	 * @var    array
	 */
	protected $records = null;

	/**
	 * Record Count
	 *
	 * @access protected
	 * @var    integer
	 */
	protected $count = 0;

	/**
	 * Constructor
	 *
	 * Creates a new (empty) object or creates the record set from the
	 * passed parameters.  The record and records arrays are populated as
	 * well as the count variable.
	 *
	 * @param array $conditions optional key/values for the WHERE cause
	 */
	public function __construct($conditions = null)
	{
		parent::__construct();

		$this->db = new Database();

		if (isset($conditions))
		{
			if (is_array($this->columns))
			{
				$this->columns = implode(', ', $this->columns);
			}

			$sql = 'SELECT ' . $this->columns . ' FROM ' . $this->table;

			if (is_array($conditions))
			{
				$sql .= ' WHERE ';

				$input_parameters = null;
				$include_and      = false;

				// Overrides the ORDER BY and LIMIT values
				if (is_array($conditions))
				{
					if (isset($conditions['ORDER BY']))
					{
						$this->order_by = $conditions['ORDER BY'];
						unset($conditions['ORDER BY']);
					}

					if (isset($conditions['LIMIT']))
					{
						$this->limit = $conditions['LIMIT'];
						unset($conditions['LIMIT']);
					}
				}

				foreach ($conditions as $column => $value)
				{
					if ($input_parameters != null || $include_and == true)
					{
						$sql .= ' AND ';
					}

					if (is_array($value))
					{
						$sql .= $column . ' IN ("' . implode($value, '", "') . '") ';
					}
					elseif (strpos($column, 'IS') === false && strpos($value, 'IS ') === false)
					{
						$sql   .= $column . (preg_match('/(=|!=|<|>|LIKE)/', $column) ? ' ' : '= ') . ':';
						$column = trim(str_replace(array('!', '=', '<', '>', 'LIKE'), '', $column));
						$sql   .= $column;
					}
					else
					{
						$sql .= $column . ' ' . $value;
					}

					if (!is_array($value) && $value != 'NULL')
					{
						$input_parameters[':' . $column] = $value;
					}
					else
					{
						$include_and = true;
					}
				}

				if ($this->order_by != null)
				{
					if (is_array($this->order_by))
					{
						$this->order_by = implode(', ', $this->order_by);
					}

					$sql .= ' ORDER BY ' . $this->order_by;
				}

				if ($this->limit != null)
				{
					if (is_array($this->limit))
					{
						$this->limit = implode(', ', $this->limit);
					}

					$sql .= ' LIMIT ' . $this->limit;
				}

				$this->data = $this->db->fetchAll($sql, $input_parameters);
			}
			elseif ($conditions === true)
			{
				if ($this->order_by != null)
				{
					if (is_array($this->order_by))
					{
						$this->order_by = implode(', ', $this->order_by);
					}

					$sql .= ' ORDER BY ' . $this->order_by;
				}

				if ($this->limit != null)
				{
					if (is_array($this->limit))
					{
						$this->limit = implode(', ', $this->limit);
					}

					$sql .= ' LIMIT ' . $this->limit;
				}

				$this->data = $this->db->fetchAll($sql);
			}
			else
			{
				$this->data = $this->db->fetch($sql . ' WHERE id = "' . $conditions . '" LIMIT 1;');
			}

			$this->records = $this->data;

			if (isset($this->records[0]))
			{
				$this->record = $this->records[0];
			}
			else
			{
				$this->record = $this->records;
			}

			if (!empty($this->records))
			{
				$this->count = count($this->records);
			}
		}
	}

	/**
	 * Next Record
	 *
	 * Increment the record array to the next member of the record set.
	 */
	public function next()
	{
		$this->record = next($this->data);
	}

	/**
	 * Previous Record
	 *
	 * Decrement the record array to the next member of the record set.
	 */
	public function prev()
	{
		$this->record = prev($this->data);
	}

	/**
	 * First Record
	 *
	 * Set the pointer to the first element of the record set.
	 */
	public function first()
	{
		$this->record = reset($this->data);
	}

	/**
	 * Last Record
	 *
	 * Set the pointer to the last element of the record set.
	 */
	public function last()
	{
		$this->record = end($this->data);
	}

	/**
	 * Commit Record
	 *
	 * Commits a record to the database. Intelligently does an UPDATE or
	 * INSERT INTO.
	 *
	 * @return boolean results of the query
	 * @todo   This will replace commit() eventually will add commitAll();
	 */
	public function commitRecord()
	{
		if (count($this->record) > 0)
		{
			$update = isset($this->record['id']) && Utility::isValid($this->record['id']);

			$sql = ($update === true ? 'UPDATE' : 'INSERT INTO') . ' ' . $this->table . ' SET ';
			$input_parameters = null;

			foreach ($this->record as $column => $value)
			{
				if ($column != 'id')
				{
					if ($input_parameters != null)
					{
						$sql .= ', ';
					}

					$sql .= $column . ' = :' . $column;
					$input_parameters[':' . $column] = is_array($value) ? json_encode($value) : $value;
				}
			}

			if ($update === true)
			{
				$sql .= ' WHERE id = :id LIMIT 1;';
				$input_parameters[':id'] = $this->record['id'];
			}

			return $this->db->execute($sql, $input_parameters);
		}

		return false;
	}

	/**
	 * Delete Record
	 *
	 * Deletes the current record from the database
	 *
	 * @return boolean status of the query
	 */
	public function delete()
	{
		$sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id LIMIT 1;';
		$input_parameters[':id'] = $this->record['id'];

		return $this->db->execute($sql, $input_parameters);
	}

	/**
	 * Magic Getter
	 *
	 * Overrides the built in PHP getter and checks our data variable for
	 * the variable or returns false.  The data array is loaded via the
	 * magic setter.
	 *
	 * @param  string $variable name of the variable
	 * @return mixed requested variable, the entire data array or false
	 */
	public function __get($variable)
	{
		if (in_array($variable, array('data', 'records', 'record', 'count')))
		{
			return $this->$variable;
		}
		elseif (isset($this->data[$variable]))
		{
			return $this->data[$variable];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Magic Setter
	 *
	 * Overrides the built in PHP setter so that we can assign variables to
	 * our private data array (avoids conflicts with the object variables).
	 *
	 * @param string $variable name of the variable
	 * @param mixed $value value for the variable
	 */
	public function __set($variable, $value)
	{
		if (in_array($variable, array('record', 'records')))
		{
			$this->$variable = $value;
		}
		else
		{
			$this->data[$variable] = $value;
			$this->record[$variable] = $value;
		}
	}

	/**
	 * Commit
	 *
	 * Commits the record to the database. Intelligently does an UPDATE or
	 * INSERT INTO.
	 *
	 * @deprecated Deprecated is commitRecord() is even implemented
	 * @return     boolean results of the query
	 */
	public function commit()
	{
		if (count($this->data) > 0)
		{
			$update = isset($this->data['id']) && Utility::isValid($this->data['id']);

			$sql = ($update === true ? 'UPDATE' : 'INSERT INTO') . ' ' . $this->table . ' SET ';
			$input_parameters = null;

			foreach ($this->data as $column => $value)
			{
				if ($column != 'id')
				{
					if ($input_parameters != null)
					{
						$sql .= ', ';
					}

					$sql .= $column . ' = :' . $column;
					$input_parameters[':' . $column] = is_array($value) ? json_encode($value) : $value;
				}
			}

			if ($update === true)
			{
				$sql .= ' WHERE id = :id LIMIT 1;';
				$input_parameters[':id'] = $this->id;
			}

			return $this->db->execute($sql, $input_parameters);
		}

		return false;
	}

	/**
	 * Unescape String
	 *
	 * Assuming magic quotes is turned on, strips slashes from the string
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
}

?>
