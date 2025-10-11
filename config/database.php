<?php
/**
 * Database Configuration
 * NELVINTO Liquors store - Order Management System
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Check if we're on Railway (production)
        if (getenv('DATABASE_URL')) {
            $url = parse_url(getenv('DATABASE_URL'));
            $this->host = $url['host'];
            $this->db_name = substr($url['path'], 1);
            $this->username = $url['user'];
            $this->password = $url['pass'];
            $this->port = $url['port'] ?? '5432';
        } 
        // Check if we're on Railway with individual environment variables
        else if (getenv('PGHOST')) {
            $this->host = getenv('PGHOST');
            $this->db_name = getenv('PGDATABASE');
            $this->username = getenv('PGUSER');
            $this->password = getenv('PGPASSWORD');
            $this->port = getenv('PGPORT') ?? '5432';
        } 
        // Local development
        else {
            $this->host = 'localhost';
            $this->db_name = 'total_wine_orders';
            $this->username = 'postgres';
            $this->password = '2003';
            $this->port = '5432';
        }
    }

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
define('DB_PASS', '2003');
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