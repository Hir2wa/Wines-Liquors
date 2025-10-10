<?php
// Test User Model
require_once 'config/database.php';
require_once 'models/User.php';

echo "Testing User Model...\n";

try {
    $database = new Database();
    $user = new User($database->getConnection());
    
    echo "✅ User model loaded successfully\n";
    
    // Test phone validation
    $testPhone = "0780146863";
    echo "Testing phone validation for: $testPhone\n";
    
    // Test registration data with unique phone number
    $uniquePhone = '078' . substr(time(), -7); // Generate unique phone number
    $userData = [
        'email' => 'test' . time() . '@example.com',
        'password' => 'TestPass123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => $uniquePhone,
        'address' => 'Test Address',
        'city' => 'Kigali',
        'country' => 'Rwanda'
    ];
    
    echo "Testing registration with data:\n";
    print_r($userData);
    
    $result = $user->register($userData);
    
    if ($result['success']) {
        echo "✅ Registration successful!\n";
        echo "User ID: " . $result['user_id'] . "\n";
        echo "Email: " . $result['email'] . "\n";
        echo "Phone: " . $result['phone'] . "\n";
        
        // Test verification code generation
        $verificationResult = $user->generateVerificationCode(
            $result['user_id'], 
            'phone', 
            $result['phone']
        );
        
        if ($verificationResult['success']) {
            echo "✅ Phone verification code generated: " . $verificationResult['code'] . "\n";
            echo "Expires at: " . $verificationResult['expires_at'] . "\n";
        } else {
            echo "❌ Failed to generate verification code: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ Registration failed: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>