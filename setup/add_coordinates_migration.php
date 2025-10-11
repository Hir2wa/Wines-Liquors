<?php
/**
 * Add Coordinates Migration
 * Adds customer_latitude and customer_longitude fields to the orders table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Adding coordinate fields to orders table...\n";
    
    // Check if the fields already exist
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'orders' AND column_name IN ('customer_latitude', 'customer_longitude')");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('customer_latitude', $existing_columns)) {
        echo "✅ customer_latitude field already exists\n";
    } else {
        // Add customer_latitude field
        $conn->exec("ALTER TABLE orders ADD COLUMN customer_latitude DECIMAL(10, 8)");
        echo "✅ customer_latitude field added\n";
    }
    
    if (in_array('customer_longitude', $existing_columns)) {
        echo "✅ customer_longitude field already exists\n";
    } else {
        // Add customer_longitude field
        $conn->exec("ALTER TABLE orders ADD COLUMN customer_longitude DECIMAL(11, 8)");
        echo "✅ customer_longitude field added\n";
    }
    
    // Show the updated table structure
    echo "\nUpdated table structure:\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'orders' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- " . $column['column_name'] . " (" . $column['data_type'] . ") - " . ($column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    echo "\n🎉 Coordinates migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
