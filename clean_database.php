<?php
// Clean Database Script
// This will remove all data from all tables to start fresh

require_once 'config/database.php';

echo "🧹 Cleaning Database - Starting Fresh...\n\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // List of tables to clean (in order to respect foreign key constraints)
    $tables = [
        'password_reset_tokens',
        'password_reset_codes', 
        'verification_codes',
        'user_sessions',
        'login_attempts',
        'user_preferences',
        'payment_codes',
        'orders',
        'users'
    ];
    
    echo "📋 Tables to clean:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    echo "\n";
    
    // Disable foreign key checks temporarily
    $conn->exec("SET session_replication_role = replica;");
    
    // Clean each table
    foreach ($tables as $table) {
        try {
            $sql = "DELETE FROM $table";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute();
            
            if ($result) {
                $count = $stmt->rowCount();
                echo "✅ Cleaned table '$table' - Removed $count records\n";
            } else {
                echo "⚠️  Table '$table' - No records to remove or table doesn't exist\n";
            }
        } catch (Exception $e) {
            echo "⚠️  Table '$table' - " . $e->getMessage() . "\n";
        }
    }
    
    // Re-enable foreign key checks
    $conn->exec("SET session_replication_role = DEFAULT;");
    
    // Reset sequences/auto-increment counters
    echo "\n🔄 Resetting auto-increment counters...\n";
    
    $resetQueries = [
        "ALTER SEQUENCE users_id_seq RESTART WITH 1",
        "ALTER SEQUENCE orders_id_seq RESTART WITH 1", 
        "ALTER SEQUENCE verification_codes_id_seq RESTART WITH 1",
        "ALTER SEQUENCE user_sessions_id_seq RESTART WITH 1",
        "ALTER SEQUENCE login_attempts_id_seq RESTART WITH 1",
        "ALTER SEQUENCE user_preferences_id_seq RESTART WITH 1",
        "ALTER SEQUENCE payment_codes_id_seq RESTART WITH 1"
    ];
    
    foreach ($resetQueries as $query) {
        try {
            $conn->exec($query);
            echo "✅ Reset sequence\n";
        } catch (Exception $e) {
            echo "⚠️  Sequence reset: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Database cleaned successfully!\n";
    echo "📊 Summary:\n";
    echo "  - All user data removed\n";
    echo "  - All orders removed\n";
    echo "  - All verification codes removed\n";
    echo "  - All sessions cleared\n";
    echo "  - All payment codes removed\n";
    echo "  - Auto-increment counters reset\n";
    echo "\n✨ You can now register with phone number 0780146863 and other real data!\n";
    
} catch (Exception $e) {
    echo "❌ Error cleaning database: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>




