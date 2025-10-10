<?php
/**
 * Payment Codes Migration
 * Creates the payment_codes table for mobile money payment tracking
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Create payment_codes table
    $sql = "
    CREATE TABLE IF NOT EXISTS payment_codes (
        id SERIAL PRIMARY KEY,
        order_id VARCHAR(20) NOT NULL,
        payment_code VARCHAR(20) NOT NULL UNIQUE,
        amount DECIMAL(10,2) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'verified', 'expired', 'cancelled')),
        verified_by VARCHAR(100),
        verified_at TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    );
    ";
    
    $db->exec($sql);
    echo "✅ Payment codes table created successfully!\n";
    
    // Create index on payment_code for faster lookups
    $index_sql = "CREATE INDEX IF NOT EXISTS idx_payment_codes_code ON payment_codes(payment_code);";
    $db->exec($index_sql);
    echo "✅ Index on payment_code created successfully!\n";
    
    // Create index on order_id for faster lookups
    $index_sql2 = "CREATE INDEX IF NOT EXISTS idx_payment_codes_order_id ON payment_codes(order_id);";
    $db->exec($index_sql2);
    echo "✅ Index on order_id created successfully!\n";
    
    // Create index on status for filtering
    $index_sql3 = "CREATE INDEX IF NOT EXISTS idx_payment_codes_status ON payment_codes(status);";
    $db->exec($index_sql3);
    echo "✅ Index on status created successfully!\n";
    
    echo "\n🎉 Payment codes migration completed successfully!\n";
    echo "The payment_codes table is now ready for mobile money payment tracking.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
