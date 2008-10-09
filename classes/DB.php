<?php

/**
 * Database (DB) Class File for PICKLES
 *
 * PICKLES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 * 
 * PICKLES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with PICKLES.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @author    Joshua John Sherman <josh@phpwithpickles.org>
 * @copyright Copyright 2007, 2008 Joshua John Sherman
 * @link      http://phpwithpickles.org
 * @license   http://www.gnu.org/copyleft/lesser.html
 * @package   PICKLES
 */

/**
 * Database Abstraction Layer for MySQL
 *
 * All database usage inside PICKLES-based sites should be done
 * via the database object that is a part of every model ($this->db).
 * Because the database object can execute raw SQL, there should be
 * no limitations.
 *
 * @todo      Internally document the functions better.
 * @todo      Potentially switch to PDO to be able to easily accomodate
 *            different database types.
 * @todo      Eventually finish adding in my ActiveRecord class, even
 *            though I feel active record dumbs people down since it's a
 *            crutch for actually being able to write SQL.
 * @todo      Rename me to Database (maybe)
 */
class DB extends Singleton {

	/**
	 * Private instance of the DB class
	 */
	private static $instance;

	/**
	 * Private MySQL resources
	 */
	private $connection;
	private $results;

	/**
	 * Private constructor
	 */
	private function __construct() { }
	
	/**
	 * Gets an instance of the database object
	 *
	 * Determines if a DB object has already been instantiated, if so it
	 * will use it.  If not, it will create one.
	 *
	 * @return object An instace of the DB class
	 */
	public static function getInstance() {
		$class = __CLASS__;

		if (!self::$instance instanceof $class) {
			self::$instance = new $class();
		}

		return self::$instance;
	}

	/**
	 * Opens database connection
	 *
	 * Establishes a connection to the MySQL database based on the
	 * configuration options that are available in the Config object.
	 *
	 * @return boolean Based on the success or failure of mysql_connect()
	 * @todo   Remove the error supressing @ from mysql_connect()
	 */
	public function open() {
		if (!is_resource($this->connection)) {
			$config = Config::getInstance();

			if ($config->database->hostname == '') {
				$config->database->hostname = 'localhost';
			}

			if (isset($config->database->username, $config->database->password, $config->database->database)) {
				/**
				 * @todo I removed the @ and changed to be pconnect... let's see
				 */
				$this->connection = mysql_pconnect($config->database->hostname, $config->database->username, $config->database->password);

				if (is_resource($this->connection)) {
					if (!mysql_select_db($config->database->database, $this->connection)) {
						Error::addWarning("There was an error selecting the '" . $this->database->database , "' database");
						return false;
					}
					else {
						return true;
					}
				}
				else {
					Error::addError('There was an error connecting to the database server');
				}

				return false;
			}
			else {
				Error::addError('There was an error loading the configuration');
			}

			return false;
		}
		
		return true;
	}

	/**
	 * Closes database connection
	 *
	 * Checks to see if the connection is available, and if so, closes it.
	 *
	 * @return boolean Returns the status of mysql_close() (default = false)
	 */
	public function close() {
		if (is_resource($this->connection)) {
			return mysql_close($this->connection);
		}

		return false;
	}

	/**
	 * Executes SQL
	 *
	 * Executes the passed SQL without any manipulation.  If no SQL is
	 * passed in the function will return false (why return true if it
	 * didn't actually do something?)
	 *
	 * @param  string $sql SQL statement to be executed
	 * @return boolean Returns the status of the execution
	 * @todo   Need to get rid of the error suppressing @ symbol.
	 */
	public function execute($sql) {
		$this->open();
		
		if (trim($sql) != '') {
			$this->results = @mysql_query($sql, $this->connection);
			if (empty($this->results)) {
				Error::addError('There was an error executing the SQL');
				Error::addError(mysql_error());
			}
			else {
				return true;
			}
		}
		else {
			Error::addWarning('There was no SQL to execute');
		}

		return false;
	}

	/**
	 * Gets a field from a result set
	 *
	 * Returns the value of a single field from either a previously executed
	 * query, or from the passed SQL.  This function assumes your query
	 * results only contain a single field.  If multiple fields are
	 * returned, this function will only return the first one.
	 *
	 * @param  string $sql SQL statement to be executed (optional)
	 * @return string Returns the value of the field or null if none
	 * @todo   Need to remove the error supression
	 * @todo   Right now it assumes your query only returns a single field,
	 *         that probably should be changed to allow someone to specify
	 *         what field they want from a row of data.  Actually, this is
	 *         still debatable as someone could use getRow and reference the
	 *         field they want to accomplish the same goal.
	 * @todo   Another debate point, should it return false instead of null,
	 *         or perhaps have some sort of error indicator in the result
	 *         set?
	 */
	public function getField($sql = null) {
		if (isset($sql)) {
			$this->execute($sql);
		}

		if (is_resource($this->results)) {
			$results = @mysql_fetch_row($this->results);
			if (is_array($results)) {
				return $results[0];
			}
			else {
				Error::addWarning('There is nothing to return');
			}
		}
		else {
			Error::addError('There is no valid MySQL result resource');
		}

		return null;
	}
	
	/**
	 * Gets a row from a result set
	 *
	 * Returns a row in an associative array from either a previously
	 * executed query, or from the passed SQL.  This function assumes your
	 * query results only contain a single row.  If multiple rows are
	 * returned, this function will only return the first one.
	 *
	 * @param  string $sql SQL statement to be executed (optional)
	 * @return mixed The row in an associative array or null if none
	 * @todo   Need to remove the error supression
	 * @todo   Right now it assumes your query only returns a single row,
	 *         that probably should be changed to allow someone to specify
	 *         what row they want from a set of data.  Actually, this is
	 *         still debatable as someone could use getArray and reference
	 *         the row they want to accomplish the same goal.
	 * @todo   Another debate point, should it return false instead of null,
	 *         or perhaps have some sort of error indicator in the result
	 *         set?
	 * @todo   Calling bullshit on my own code, apparently this should only
	 *         be returning a single row, but returns all the rows instead
	 *         of just the first one.  So basically, this function is
	 *         functioning exactly the same as DB::getArray().  Just goes to
	 *         show how often I actually use this function.
	 */
	public function getRow($sql = null) {
		if (isset($sql)) {
			$this->execute($sql);
		}

		if (is_resource($this->results)) {
			$results = @mysql_fetch_assoc($this->results);
			if (is_array($results)) {
				return $results;
			}
			else {
				Error::addWarning('There is nothing to return');
			}
		}
		else {
			Error::addError('There is no valid MySQL result resource');
		}

		return null;
	}
	
	/**
	 * Gets all the rows from a result set
	 *
	 * Returns all the rows (each row in an associative array) from either a
	 * previously executed query, or from the passed SQL.
	 *
	 * @param  string $sql SQL statement to be executed (optional)
	 * @return string Returns the rows in an array or null if none
	 * @todo   Need to remove the error supression
	 * @todo   Another debate point, should it return false instead of null,
	 *         or perhaps have some sort of error indicator in the result
	 *         set?
	 */
	public function getArray($sql = null) {
		if (isset($sql)) {
			$this->execute($sql);
		}

		if (is_resource($this->results)) {
			$return = null;
			while ($row = mysql_fetch_assoc($this->results)) {
				if (!is_array($return)) {
					$return = array();
				}

				array_push($return, $row);
			}

			return $return;
		}
		else {
			Error::addError('There is no valid MySQL result resource');
		}

		return null;
	}

	/**
	 * Inserts a row into a table
	 *
	 * Easy insertion of a row into a table without being too savvy with
	 * SQL.
	 *
	 * @param  string $table Name of the table you want to insert to
	 * @param  array $columnValues Associative array of name value pairs
	 *         (Corresponds with the column names for the table)
	 * @return boolean Returns the status of the execution
	 * @todo   Convert from camel case to underscores
	 * @todo   Check that the table exists, and possibly check that the
	 *         columns exist as well
	 */
	public function insert($table, $columnValues) {
		$this->open();

		if (trim($table) != '') {
			if (is_array($columnValues)) {
				foreach ($columnValues as $key => $value) {
					$columnValues[$key] = $value == null ? 'NULL' : "'" . mysql_real_escape_string(stripslashes($value), $this->connection) . "'";
				}

				$this->execute("
					INSERT INTO {$table} (
						" . implode(array_keys($columnValues), ', ') . "
					) VALUES (
						" . implode($columnValues, ", ") . "
					);
				");

				return mysql_insert_id($this->connection);
			}
			else {
				Error::addError('No data was specified');
			}
		}
		else {
			Error::addError('No database table was specified');
		}

		return false;
	}

	/**
	 * Updates an existing row row in a table
	 *
	 * Easy update of an existing row or rows (depending on the passed 
	 * conditions) in a table without being too savvy with SQL.
	 *
	 * @params string $table Name of the table you want to insert to
	 * @params array $columnValues Associative array of name value pairs
	 *         (Corresponds with the column names for the table)
	 * @params array $conditions Associative array of name value pairs that
	           will be used to create a WHERE clause in the SQL.
	 * @return boolean Returns the status of the execution
	 * @todo   Convert from camel case to underscores
	 * @todo   Check that the table exists, and possibly check that the
	 *         columns exist and conditional columns exist as well
	 */
	public function update($table, $columnValues, $conditions) {
		$this->open();

		if (trim($table) != '') {
			$fields = $where = null;			
			if (is_array($columnValues)) {
				foreach ($columnValues as $key => $value) {
					$fields .= ($fields ? ', ' : null) . $key . " = '" . mysql_real_escape_string(stripslashes($value), $this->connection) . "'";
				}

				if (is_array($conditions)) {
					foreach ($conditions as $key => $value) {
						$where = ($where == null) ? 'WHERE ' : ' AND ';

						if ($value == null) {
							$where .= $key . ' IS NULL';
						}
						else {
							$where .= $key . " = '" . mysql_real_escape_string(stripslashes($value), $this->connection) . "'";
						}
					}

					$sql = 'UPDATE ' . $table . ' SET ' . $fields . $where;
					if ($this->execute($sql)) {
						return true;
					}
				}
				else {
					Error::addError('No conditions were specified');
				}
			}
			else {
				Error::addError('No data was specified');
			}
		}
		else {
			Error::addError('No database table was specified');
		}

		return false;
	}

	/**
	 * Deletes an existing row row in a table
	 *
	 * Easy deletion of an existing row or rows (depending on the passed
	 * conditions) in a table without being too savvy with SQL.
	 *
	 * @access private
	 * @params string $table Name of the table you want to insert to
	 * @params array $columnValues Associative array of name value pairs
	 *         (Corresponds with the column names for the table)
	 * @params array $conditions Associative array of name value pairs that
	 *         will be used to create a WHERE clause in the SQL.
	 * @return boolean Returns the status of the execution
	 * @todo   This function doesn't exist yet
	 * @todo   Convert from camel case to underscores
	 */
	public function delete($table, $columnValues, $conditions) {

	}
}

?>
