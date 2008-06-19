<?php

class DB {

	private static $hostname;
	private static $username;
	private static $password;
	private static $database;

	private static $connection;
	private static $results;

	public static function open() {
		self::$hostname = Config::get('hostname', 'database');
		self::$username = Config::get('username', 'database');
		self::$password = Config::get('password', 'database');
		self::$database = Config::get('database', 'database');

		if (isset(self::$hostname) && isset(self::$username) && isset(self::$password) && isset(self::$database)) {
			self::$connection = @mysql_connect(self::$hostname, self::$username, self::$password);

			if (is_resource(self::$connection)) {
				if (!mysql_select_db(self::$database, self::$connection)) {
					Error::addWarning("There was an error selecting the '" . self::$database , "' database");
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

	public static function close() {
		if (is_resource(self::$connection)) {
			return mysql_close(self::$connection);
		}

		return false;
	}

	public static function execute($sql) {
		if (!is_resource(self::$connection)) {
			self::open();
		}
		
		if (trim($sql) != '') {
			self::$results = @mysql_query($sql, self::$connection);
			if (empty(self::$results)) {
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

	public static function getField($sql = null) {
		if (isset($sql)) {
			self::execute($sql);
		}

		if (is_resource(self::$results)) {
			$results = @mysql_fetch_row(self::$results);
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

	public static function getRow($sql = null) {
		if (isset($sql)) {
			self::execute($sql);
		}

		if (is_resource(self::$results)) {
			$results = @mysql_fetch_assoc(self::$results);
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

	public static function getArray($sql = null) {
		if (isset($sql)) {
			self::execute($sql);
		}

		if (is_resource(self::$results)) {
			$return = null;
			while ($row = mysql_fetch_assoc(self::$results)) {
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

	public static function insert($table, $columnValues) {
		if (!is_resource(self::$connection)) {
			self::open();
		}

		if (trim($table) != '') {
			// @todo Check that the table exists, and possibly check that the columns exist as well

			if (is_array($columnValues)) {
				foreach ($columnValues as $key => $value) {
					$columnValues[$key] = $value == null ? 'NULL' : "'" . mysql_real_escape_string(stripslashes($value), self::$connection) . "'";
				}

				self::execute("
					INSERT INTO {$table} (
						" . implode(array_keys($columnValues), ', ') . "
					) VALUES (
						" . implode($columnValues, ", ") . "
					);
				");

				return mysql_insert_id(self::$connection);
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

	public static function update($table, $columnValues, $conditions) {
		if (!is_resource(self::$connection)) {
			self::open();
		}

		if (trim($table) != '') {
			// @todo Check that the table exists, and possibly check that the columns exist as well

			$fields = $where = null;			
			if (is_array($columnValues)) {
				foreach ($columnValues as $key => $value) {
					$fields .= ($fields ? ', ' : null) . $key . " = '" . mysql_real_escape_string(stripslashes($value), self::$connection) . "'";
				}

				if (is_array($conditions)) {
					foreach ($conditions as $key => $value) {
						$where = ($where == null) ? 'WHERE ' : ' AND ';

						if ($value == null) {
							$where .= $key . ' IS NULL';
						}
						else {
							$where .= $key . " = '" . mysql_real_escape_string(stripslashes($value), self::$connection) . "'";
						}
					}

					$sql = 'UPDATE ' . $table . ' SET ' . $fields . $where;
					if (self::execute($sql)) {
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

	public static function delete($table, $columnValues, $conditions) {

	}

}

?>
