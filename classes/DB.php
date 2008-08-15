<?php

class DB extends Singleton {

	private static $instance;

	private $hostname;
	private $username;
	private $password;
	private $database;

	private $connection;
	private $results;

	private function __construct() { }

	public static function getInstance() {
		$session = Session::getInstance();
		
		$class = __CLASS__;

		if (isset($session->$class)) {
			self::$instance = Singleton::thaw($class);
		}
		else if (!self::$instance instanceof $class) {
			self::$instance = new $class();
		}

		return self::$instance;
	}

	public function open() {
		if (!is_resource($this->connection)) {
			$config = Config::getInstance();

			$this->hostname = $config->get('database', 'hostname');
			$this->username = $config->get('database', 'username');
			$this->password = $config->get('database', 'password');
			$this->database = $config->get('database', 'database');

			if (isset($this->hostname) && isset($this->username) && isset($this->password) && isset($this->database)) {
				$this->connection = @mysql_connect($this->hostname, $this->username, $this->password);

				if (is_resource($this->connection)) {
					if (!mysql_select_db($this->database, $this->connection)) {
						Error::addWarning("There was an error selecting the '" . $this->database , "' database");
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

	public function close() {
		if (is_resource($this->connection)) {
			return mysql_close($this->connection);
		}

		return false;
	}

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

	public function insert($table, $columnValues) {
		$this->open();

		if (trim($table) != '') {
			// @todo Check that the table exists, and possibly check that the columns exist as well

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

	public function update($table, $columnValues, $conditions) {
		$this->open();

		if (trim($table) != '') {
			// @todo Check that the table exists, and possibly check that the columns exist as well

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

	public function delete($table, $columnValues, $conditions) {

	}

}

?>
