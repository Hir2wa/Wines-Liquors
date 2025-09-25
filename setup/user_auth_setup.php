<?php
/**
 * User Authentication Database Setup
 * Creates tables for user management, verification, and sessions
 */

echo "🔐 Setting up User Authentication System\n";
echo "========================================\n\n";

require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        echo "❌ Database connection failed!\n";
        exit(1);
    }
    
    echo "✅ Database connection successful!\n\n";
    
    // Create users table
    echo "Creating users table...\n";
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        date_of_birth DATE,
        gender VARCHAR(10) CHECK (gender IN ('male', 'female', 'other')),
        address TEXT,
        city VARCHAR(100),
        country VARCHAR(100) DEFAULT 'Rwanda',
        is_verified BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        email_verified BOOLEAN DEFAULT FALSE,
        phone_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);
    CREATE INDEX IF NOT EXISTS idx_users_phone ON users (phone);
    CREATE INDEX IF NOT EXISTS idx_users_verified ON users (is_verified);
    ";
    
    $conn->exec($sql_users);
    echo "✅ Users table created successfully!\n";
    
    // Create verification_codes table
    echo "Creating verification codes table...\n";
    $sql_verification = "
    CREATE TABLE IF NOT EXISTS verification_codes (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        code VARCHAR(10) NOT NULL,
        type VARCHAR(20) NOT NULL CHECK (type IN ('email', 'phone', 'password_reset')),
        contact_info VARCHAR(255) NOT NULL, -- email or phone number
        expires_at TIMESTAMP NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        attempts INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_verification_code ON verification_codes (code);
    CREATE INDEX IF NOT EXISTS idx_verification_contact ON verification_codes (contact_info);
    CREATE INDEX IF NOT EXISTS idx_verification_expires ON verification_codes (expires_at);
    ";
    
    $conn->exec($sql_verification);
    echo "✅ Verification codes table created successfully!\n";
    
    // Create user_sessions table
    echo "Creating user sessions table...\n";
    $sql_sessions = "
    CREATE TABLE IF NOT EXISTS user_sessions (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        session_token VARCHAR(255) UNIQUE NOT NULL,
        device_info TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_sessions_token ON user_sessions (session_token);
    CREATE INDEX IF NOT EXISTS idx_sessions_user ON user_sessions (user_id);
    CREATE INDEX IF NOT EXISTS idx_sessions_expires ON user_sessions (expires_at);
    ";
    
    $conn->exec($sql_sessions);
    echo "✅ User sessions table created successfully!\n";
    
    // Create login_attempts table for security
    echo "Creating login attempts table...\n";
    $sql_login_attempts = "
    CREATE TABLE IF NOT EXISTS login_attempts (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255),
        phone VARCHAR(20),
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        success BOOLEAN DEFAULT FALSE,
        failure_reason VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_login_attempts_email ON login_attempts (email);
    CREATE INDEX IF NOT EXISTS idx_login_attempts_phone ON login_attempts (phone);
    CREATE INDEX IF NOT EXISTS idx_login_attempts_ip ON login_attempts (ip_address);
    CREATE INDEX IF NOT EXISTS idx_login_attempts_created ON login_attempts (created_at);
    ";
    
    $conn->exec($sql_login_attempts);
    echo "✅ Login attempts table created successfully!\n";
    
    // Create user_preferences table
    echo "Creating user preferences table...\n";
    $sql_preferences = "
    CREATE TABLE IF NOT EXISTS user_preferences (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        notification_email BOOLEAN DEFAULT TRUE,
        notification_sms BOOLEAN DEFAULT TRUE,
        marketing_emails BOOLEAN DEFAULT FALSE,
        preferred_contact VARCHAR(10) DEFAULT 'phone' CHECK (preferred_contact IN ('email', 'phone')),
        language VARCHAR(5) DEFAULT 'en',
        currency VARCHAR(3) DEFAULT 'RWF',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE INDEX IF NOT EXISTS idx_preferences_user ON user_preferences (user_id);
    ";
    
    $conn->exec($sql_preferences);
    echo "✅ User preferences table created successfully!\n";
    
    echo "\n🎉 User Authentication Database Setup Complete!\n";
    echo "===============================================\n";
    echo "Tables created:\n";
    echo "- users (main user accounts)\n";
    echo "- verification_codes (email/phone verification)\n";
    echo "- user_sessions (session management)\n";
    echo "- login_attempts (security tracking)\n";
    echo "- user_preferences (user settings)\n";
    echo "\nReady to implement authentication system! 🔐\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

