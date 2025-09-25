<?php
/**
 * Database Setup Script
 * Run this once to create the database and tables
 */

require_once '../config/database.php';

try {
    $database = new Database();
    
    // Create database
    echo "Creating database...\n";
    $database->createDatabase();
    
    // Get connection
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "Database connection successful!\n";
        
        // Create orders table
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
        echo "Orders table created successfully!\n";
        
        // Create order_items table
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
        echo "Order items table created successfully!\n";
        
        // Create admin_users table
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
        echo "Admin users table created successfully!\n";
        
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
            echo "Default admin user created!\n";
            echo "Username: admin\n";
            echo "Password: admin123\n";
        } else {
            echo "Admin user already exists!\n";
        }
        
        // Create order_status_logs table for tracking
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
        echo "Order status logs table created successfully!\n";
        
        echo "\n=== Database Setup Complete! ===\n";
        echo "Database: " . DB_NAME . "\n";
        echo "Tables created:\n";
        echo "- orders\n";
        echo "- order_items\n";
        echo "- admin_users\n";
        echo "- order_status_logs\n";
        echo "\nDefault admin credentials:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        echo "\nYou can now use the API endpoints!\n";
        
    } else {
        echo "Failed to connect to database!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
