<?php
/**
 * Admin Notifications Migration
 * Creates admin_notifications table for storing admin notifications
 */

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Create admin_notifications table
    $sql = "
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id SERIAL PRIMARY KEY,
            order_id VARCHAR(50) NOT NULL,
            notification_type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            data JSONB,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL
        )
    ";
    
    $conn->exec($sql);
    echo "✅ Admin notifications table created successfully!\n";
    
    // Create index for better performance
    $index_sql = "CREATE INDEX IF NOT EXISTS idx_admin_notifications_order_id ON admin_notifications(order_id)";
    $conn->exec($index_sql);
    echo "✅ Index created successfully!\n";
    
    // Create index for unread notifications
    $index_sql2 = "CREATE INDEX IF NOT EXISTS idx_admin_notifications_unread ON admin_notifications(is_read) WHERE is_read = FALSE";
    $conn->exec($index_sql2);
    echo "✅ Unread notifications index created successfully!\n";
    
    echo "\n🎉 Admin notifications migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
