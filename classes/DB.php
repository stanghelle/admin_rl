<?php
class DB {
	public static $instance = null;

	private 	$_pdo = null,
				$_query = null,
				$_error = false,
				$_errorMessage = '',
				$_results = null,
				$_count = 0;

	private function __construct() {
		try {
			$this->_pdo = new PDO(
				'mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db') . ';charset=utf8mb4',
				Config::get('mysql/username'),
				Config::get('mysql/password'),
				[
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
				]
			);
		} catch(PDOException $e) {
			die('Database connection error: ' . $e->getMessage());
		}
	}

	public static function getInstance() {
		// Already an instance of this? Return, if not, create.
		if(!isset(self::$instance)) {
			self::$instance = new DB();
		}
		return self::$instance;
	}

	public function query($sql, $params = array()) {

		$this->_error = false;
		$this->_errorMessage = '';

		try {
			if($this->_query = $this->_pdo->prepare($sql)) {
				$x = 1;
				if(count($params)) {
					foreach($params as $param) {
						$this->_query->bindValue($x, $param);
						$x++;
					}
				}

				if($this->_query->execute()) {
					$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
					$this->_count = $this->_query->rowCount();
				} else {
					$this->_error = true;
					$errorInfo = $this->_query->errorInfo();
					$this->_errorMessage = $errorInfo[2] ?? 'Unknown error';
				}
			}
		} catch (PDOException $e) {
			$this->_error = true;
			$this->_errorMessage = $e->getMessage();
		}

		return $this;
	}

	public function errorMessage() {
		return $this->_errorMessage;
	}

	public function get($table, $where, $orderby = 'id', $direction = 'DESC') {
		return $this->action('SELECT *', $table, $where, $orderby, $direction);
	}

	public function delete($table, $where) {
		return $this->action('DELETE', $table, $where);
	}

	public function action($action, $table, $where = array(), $orderby = 'id', $direction = 'ASC') {
		if(count($where) === 3) {
			$operators = array('=', '>', '<', '>=', '<=');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			if(in_array($operator, $operators)) {
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ? ORDER BY {$orderby} {$direction}";

				if(!$this->query($sql, array($value))->error()) {
					return $this;
				}

			}

			return false;
		}
	}

	public function insert($table, $fields = array()) {
		$keys 	= array_keys($fields);
		$values = null;
		$x 		= 1;

		foreach($fields as $value) {
			$values .= "?";
			if($x < count($fields)) {
				$values .= ', ';
			}
			$x++;
		}

		$sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";

		if(!$this->query($sql, $fields)->error()) {
			return true;
		}

		return false;
	}

	public function update($table, $id, $fields = array()) {
		$set 	= null;
		$x		= 1;

		foreach($fields as $name => $value) {
			$set .= "{$name} = ?";
			if($x < count($fields)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

		if(!$this->query($sql, $fields)->error()) {
			return true;
		}

		return false;
	}

	public function results() {
		// Return result object
		return $this->_results;
	}

	public function first() {
		return $this->_results[0];
	}

	public function count() {
		// Return count
		return $this->_count;
	}

	public function error() {
		return $this->_error;
	}
}
