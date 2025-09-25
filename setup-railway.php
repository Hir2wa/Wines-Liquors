<?php
/**
 * Railway Setup Script
 * Sets up the database and initial data for Railway deployment
 */

require_once 'config/database.php';

echo "🚂 Setting up Railway deployment...\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!\n";
        
        // Create tables
        echo "📋 Creating database tables...\n";
        
        // Users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            is_verified BOOLEAN DEFAULT FALSE,
            email_verified BOOLEAN DEFAULT FALSE,
            phone_verified BOOLEAN DEFAULT FALSE,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "✅ Users table created\n";
        
        // Verification codes table
        $sql = "CREATE TABLE IF NOT EXISTS verification_codes (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            email_code VARCHAR(10),
            phone_code VARCHAR(10),
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "✅ Verification codes table created\n";
        
        // Sessions table
        $sql = "CREATE TABLE IF NOT EXISTS user_sessions (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            device_info TEXT,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "✅ Sessions table created\n";
        
        // Orders table
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id SERIAL PRIMARY KEY,
            order_id VARCHAR(50) UNIQUE NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(20) NOT NULL,
            customer_address TEXT,
            items TEXT NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            payment_status VARCHAR(50) DEFAULT 'pending',
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "✅ Orders table created\n";
        
        // Create admin user
        echo "👤 Creating admin user...\n";
        $adminEmail = 'alainfabricehirwa@gmail.com';
        $adminPassword = password_hash('EriceNtabwoba2025', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, phone, password_hash, first_name, last_name, is_verified, email_verified, phone_verified, is_admin) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                ON CONFLICT (email) DO UPDATE SET 
                is_admin = EXCLUDED.is_admin,
                updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $adminEmail,
            '0780146863',
            $adminPassword,
            'Alain',
            'Hirwa',
            true,
            true,
            true,
            true
        ]);
        
        echo "✅ Admin user created/updated\n";
        echo "📧 Admin Email: $adminEmail\n";
        echo "🔑 Admin Password: EriceNtabwoba2025\n";
        
        echo "\n🎉 Railway setup complete!\n";
        echo "🚀 Your app is ready for team testing!\n";
        
    } else {
        echo "❌ Database connection failed!\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
