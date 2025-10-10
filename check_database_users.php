<?php
// Check Database Users
require_once 'config/database.php';

echo "Checking all users in database...\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get all users
    $query = "SELECT id, email, phone, first_name, last_name, created_at FROM users ORDER BY id DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($users) . " users:\n\n";
    
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Phone: " . $user['phone'] . "\n";
        echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        echo "Created: " . $user['created_at'] . "\n";
        echo "---\n";
    }
    
    // Check specifically for phone numbers containing 780146863
    echo "\nSearching for phone numbers containing '780146863':\n";
    $query = "SELECT id, email, phone, first_name, last_name FROM users WHERE phone LIKE '%780146863%'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $matchingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($matchingUsers) > 0) {
        echo "Found " . count($matchingUsers) . " matching users:\n";
        foreach ($matchingUsers as $user) {
            echo "ID: " . $user['id'] . ", Phone: " . $user['phone'] . ", Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
        }
    } else {
        echo "No users found with phone containing '780146863'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>


