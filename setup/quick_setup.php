<?php
/**
 * Quick Setup Script for PostgreSQL
 * This script helps you set up the database quickly
 */

echo "🐘 PostgreSQL Quick Setup for Total Wine & More\n";
echo "===============================================\n\n";

// Check if PostgreSQL extension is available
if (!extension_loaded('pdo_pgsql')) {
    echo "❌ ERROR: PostgreSQL PDO extension not found!\n";
    echo "Please install php-pgsql extension:\n";
    echo "- Windows: Enable pdo_pgsql in php.ini\n";
    echo "- Mac: brew install php-pgsql\n";
    echo "- Linux: sudo apt install php-pgsql\n\n";
    exit(1);
}

echo "✅ PostgreSQL PDO extension found!\n\n";

// Get database credentials
echo "Please enter your PostgreSQL credentials:\n";
echo "Host (default: localhost): ";
$host = trim(fgets(STDIN)) ?: 'localhost';

echo "Port (default: 5432): ";
$port = trim(fgets(STDIN)) ?: '5432';

echo "Username (default: postgres): ";
$username = trim(fgets(STDIN)) ?: 'postgres';

echo "Password: ";
$password = trim(fgets(STDIN));

echo "Database name (default: total_wine_orders): ";
$db_name = trim(fgets(STDIN)) ?: 'total_wine_orders';

echo "\n";

// Test connection
echo "Testing connection to PostgreSQL...\n";
try {
    $pdo = new PDO("pgsql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connection to PostgreSQL successful!\n\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "Please check your credentials and try again.\n";
    exit(1);
}

// Create database
echo "Creating database '$db_name'...\n";
try {
    $pdo->exec("CREATE DATABASE $db_name");
    echo "✅ Database '$db_name' created successfully!\n\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️  Database '$db_name' already exists.\n\n";
    } else {
        echo "❌ Failed to create database: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Update configuration file
echo "Updating configuration file...\n";
$config_content = "<?php
/**
 * Database Configuration
 * Total Wine & More - Order Management System
 */

class Database {
    private \$host = '$host';
    private \$db_name = '$db_name';
    private \$username = '$username';
    private \$password = '$password';
    private \$port = '$port';
    private \$conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        \$this->conn = null;

        try {
            \$this->conn = new PDO(
                \"pgsql:host=\" . \$this->host . \";port=\" . \$this->port . \";dbname=\" . \$this->db_name,
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException \$exception) {
            echo \"Connection error: \" . \$exception->getMessage();
        }

        return \$this->conn;
    }

    /**
     * Create database if it doesn't exist
     */
    public function createDatabase() {
        try {
            \$this->conn = new PDO(
                \"pgsql:host=\" . \$this->host . \";port=\" . \$this->port,
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            \$sql = \"CREATE DATABASE \" . \$this->db_name;
            \$this->conn->exec(\$sql);
            
            return true;
        } catch(PDOException \$exception) {
            echo \"Database creation error: \" . \$exception->getMessage();
            return false;
        }
    }
}

// Database configuration constants
define('DB_HOST', '$host');
define('DB_NAME', '$db_name');
define('DB_USER', '$username');
define('DB_PASS', '$password');
define('DB_PORT', '$port');

// API Response helper
function sendResponse(\$data, \$status = 200, \$message = 'Success') {
    http_response_code(\$status);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => \$status,
        'message' => \$message,
        'data' => \$data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Error response helper
function sendError(\$message, \$status = 400) {
    sendResponse(null, \$status, \$message);
}

// Success response helper
function sendSuccess(\$data, \$message = 'Success') {
    sendResponse(\$data, 200, \$message);
}
?>";

file_put_contents('config/database.php', $config_content);
echo "✅ Configuration file updated!\n\n";

// Run database setup
echo "Setting up database tables...\n";
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!\n";
        
        // Create tables (same as database_setup.php)
        $sql_orders = "
        CREATE TABLE IF NOT EXISTS orders (
            id VARCHAR(20) PRIMARY KEY,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_first_name VARCHAR(100) NOT NULL,
            customer_last_name VARCHAR(100) NOT NULL,
            customer_address TEXT NOT NULL,
            customer_city VARCHAR(100) NOT NULL,
            customer_country VARCHAR(100) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'shipped', 'completed', 'cancelled')),
            payment_status VARCHAR(20) DEFAULT 'pending' CHECK (payment_status IN ('pending', 'approved', 'rejected')),
            payment_method VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE INDEX IF NOT EXISTS idx_customer_email ON orders (customer_email);
        CREATE INDEX IF NOT EXISTS idx_status ON orders (status);
        CREATE INDEX IF NOT EXISTS idx_payment_status ON orders (payment_status);
        CREATE INDEX IF NOT EXISTS idx_created_at ON orders (created_at);
        ";
        
        $conn->exec($sql_orders);
        echo "✅ Orders table created successfully!\n";
        
        $sql_items = "
        CREATE TABLE IF NOT EXISTS order_items (
            id SERIAL PRIMARY KEY,
            order_id VARCHAR(20) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            product_price DECIMAL(10,2) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            product_image VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_order_id ON order_items (order_id);
        ";
        
        $conn->exec($sql_items);
        echo "✅ Order items table created successfully!\n";
        
        $sql_admin = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin' CHECK (role IN ('admin', 'manager')),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE INDEX IF NOT EXISTS idx_username ON admin_users (username);
        CREATE INDEX IF NOT EXISTS idx_email ON admin_users (email);
        ";
        
        $conn->exec($sql_admin);
        echo "✅ Admin users table created successfully!\n";
        
        $sql_logs = "
        CREATE TABLE IF NOT EXISTS order_status_logs (
            id SERIAL PRIMARY KEY,
            order_id VARCHAR(20) NOT NULL,
            old_status VARCHAR(50),
            new_status VARCHAR(50) NOT NULL,
            changed_by VARCHAR(50),
            change_reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        );
        
        CREATE INDEX IF NOT EXISTS idx_order_id ON order_status_logs (order_id);
        CREATE INDEX IF NOT EXISTS idx_created_at ON order_status_logs (created_at);
        ";
        
        $conn->exec($sql_logs);
        echo "✅ Order status logs table created successfully!\n";
        
        // Create default admin user
        $admin_username = 'admin';
        $admin_email = 'admin@totalwine.com';
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $admin_name = 'System Administrator';
        
        $sql_check_admin = "SELECT id FROM admin_users WHERE username = ?";
        $stmt = $conn->prepare($sql_check_admin);
        $stmt->execute([$admin_username]);
        
        if ($stmt->rowCount() == 0) {
            $sql_insert_admin = "
            INSERT INTO admin_users (username, email, password_hash, full_name, role) 
            VALUES (?, ?, ?, ?, 'admin')
            ";
            $stmt = $conn->prepare($sql_insert_admin);
            $stmt->execute([$admin_username, $admin_email, $admin_password, $admin_name]);
            echo "✅ Default admin user created!\n";
            echo "   Username: admin\n";
            echo "   Password: admin123\n";
        } else {
            echo "ℹ️  Admin user already exists!\n";
        }
        
        echo "\n🎉 Setup Complete!\n";
        echo "==================\n";
        echo "Database: $db_name\n";
        echo "Tables created:\n";
        echo "- orders\n";
        echo "- order_items\n";
        echo "- admin_users\n";
        echo "- order_status_logs\n";
        echo "\nDefault admin credentials:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        echo "\nYou can now use the API endpoints!\n";
        echo "Test your setup: http://localhost/Wines-Liquors/api/health\n";
        
    } else {
        echo "❌ Failed to connect to database!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

