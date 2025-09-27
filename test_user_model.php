<?php
// Test User model directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing User model...\n";

try {
    require_once 'config/database.php';
    require_once 'models/User.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        echo "❌ Database connection failed!\n";
        exit(1);
    }
    
    echo "✅ Database connection successful!\n";
    
    $user = new User($db);
    echo "✅ User model created successfully!\n";
    
    // Test registration
    $userData = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '+250123456789'
    ];
    
    echo "Testing user registration...\n";
    $result = $user->register($userData);
    
    if ($result['success']) {
        echo "✅ Registration successful!\n";
        echo "User ID: " . $result['user_id'] . "\n";
        echo "Email: " . $result['email'] . "\n";
        echo "Phone: " . $result['phone'] . "\n";
        
        // Test verification code generation
        echo "\nTesting verification code generation...\n";
        $verificationResult = $user->generateVerificationCode($result['user_id'], 'email', $result['email']);
        
        if ($verificationResult['success']) {
            echo "✅ Verification code generated: " . $verificationResult['code'] . "\n";
            echo "Expires at: " . $verificationResult['expires_at'] . "\n";
        } else {
            echo "❌ Verification code generation failed: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ Registration failed: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>


