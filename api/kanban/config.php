<?php
/**
 * Kanban API Configuration
 * Standalone mysqli connection - no external dependencies
 */

// Database configuration - match your db_con.php settings
$servername = "localhost";
$username = "root";
$password = "root";
$databasename = "radio";

// Set JSON headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Create database connection
$conn = new mysqli($servername, $username, $password, $databasename);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Set charset
$conn->set_charset('utf8mb4');

// Simple DB wrapper class for easy querying
class KanbanDB {
    private $conn;
    private $lastResult = null;
    private $lastError = false;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function query($sql) {
        $this->lastError = false;
        $result = $this->conn->query($sql);

        if ($result === false) {
            $this->lastError = true;
            $this->lastResult = null;
            return false;
        }

        if ($result === true) {
            // INSERT, UPDATE, DELETE
            $this->lastResult = null;
            return true;
        }

        // SELECT - fetch all results
        $this->lastResult = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return true;
    }

    public function results() {
        if (!$this->lastResult) return [];

        // Convert to objects
        $objects = [];
        foreach ($this->lastResult as $row) {
            $objects[] = (object)$row;
        }
        return $objects;
    }

    public function first() {
        $results = $this->results();
        return $results[0] ?? null;
    }

    public function count() {
        return $this->lastResult ? count($this->lastResult) : 0;
    }

    public function error() {
        return $this->lastError;
    }

    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}

// Create global DB instance
$db = new KanbanDB($conn);

function getDB() {
    global $db;
    return $db;
}
?>
