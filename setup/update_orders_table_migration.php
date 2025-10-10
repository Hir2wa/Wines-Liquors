<?php
/**
 * Update Orders Table Migration
 * Updates the orders table to use location field instead of separate address/city/country fields
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Updating orders table structure...\n";
    
    // First, let's see the current structure
    echo "Current table structure:\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'orders' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- " . $column['column_name'] . " (" . $column['data_type'] . ") - " . ($column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    // Update existing data to combine address, city, country into location
    echo "\nUpdating existing data...\n";
    $update_sql = "
        UPDATE orders 
        SET customer_address = CONCAT(
            COALESCE(customer_address, ''), 
            CASE WHEN customer_city IS NOT NULL THEN ', ' || customer_city ELSE '' END,
            CASE WHEN customer_country IS NOT NULL THEN ', ' || customer_country ELSE '' END
        )
        WHERE customer_address IS NOT NULL
    ";
    
    $conn->exec($update_sql);
    echo "✅ Existing data updated\n";
    
    // Drop the city and country columns
    echo "Dropping city and country columns...\n";
    $conn->exec("ALTER TABLE orders DROP COLUMN IF EXISTS customer_city");
    $conn->exec("ALTER TABLE orders DROP COLUMN IF EXISTS customer_country");
    echo "✅ City and country columns dropped\n";
    
    // Rename customer_address to customer_location for clarity
    echo "Renaming customer_address to customer_location...\n";
    $conn->exec("ALTER TABLE orders RENAME COLUMN customer_address TO customer_location");
    echo "✅ Column renamed to customer_location\n";
    
    // Show the new structure
    echo "\nNew table structure:\n";
    $stmt = $conn->query("SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'orders' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- " . $column['column_name'] . " (" . $column['data_type'] . ") - " . ($column['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
    
    echo "\n🎉 Orders table migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
