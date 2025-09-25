<?php
/**
 * Database Configuration Template
 * Copy this file to database.php and update with your PostgreSQL credentials
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'total_wine_orders';
    private $username = 'postgres';
    private $password = '2003'; // CHANGE THIS!
    private $port = '5432';
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Create database if it doesn't exist
     */
    public function createDatabase() {
        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";port=" . $this->port,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "CREATE DATABASE " . $this->db_name;
            $this->conn->exec($sql);
            
            return true;
        } catch(PDOException $exception) {
            echo "Database creation error: " . $exception->getMessage();
            return false;
        }
    }
}

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'total_wine_orders');
define('DB_USER', 'postgres');
define('DB_PASS', 'your_postgres_password_here'); // CHANGE THIS!
define('DB_PORT', '5432');

// API Response helper
function sendResponse($data, $status = 200, $message = 'Success') {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Error response helper
function sendError($message, $status = 400) {
    sendResponse(null, $status, $message);
}

// Success response helper
function sendSuccess($data, $message = 'Success') {
    sendResponse($data, 200, $message);
}
?>
