<?php
// Test Email-Only Registration
require_once 'config/database.php';
require_once 'models/User.php';

echo "Testing Email-Only Registration System...\n";

try {
    $database = new Database();
    $user = new User($database->getConnection());
    
    // Test registration data
    $userData = [
        'email' => 'testemail' . time() . '@example.com',
        'password' => 'TestPass123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '078' . substr(time(), -7), // Generate unique phone number
        'address' => 'Test Address',
        'city' => 'Kigali',
        'country' => 'Rwanda'
    ];
    
    echo "Registration data:\n";
    print_r($userData);
    
    // Register user
    $result = $user->register($userData);
    
    if ($result['success']) {
        echo "\n✅ Registration successful!\n";
        echo "User ID: " . $result['user_id'] . "\n";
        echo "Email: " . $result['email'] . "\n";
        echo "Phone: " . $result['phone'] . "\n";
        
        // Generate email verification code only
        $verificationResult = $user->generateVerificationCode(
            $result['user_id'], 
            'email', 
            $result['email']
        );
        
        if ($verificationResult['success']) {
            echo "\n📧 EMAIL VERIFICATION CODE: " . $verificationResult['code'] . "\n";
            echo "📧 Use this code to verify your email: " . $result['email'] . "\n";
            echo "⏰ Code expires at: " . $verificationResult['expires_at'] . "\n";
            
            // Test email sending
            $emailResult = $user->sendEmailVerification($result['user_id'], $result['email'], $verificationResult['code']);
            echo "\nEmail Result: " . $emailResult['message'] . "\n";
            
            echo "\n🎯 INSTRUCTIONS:\n";
            echo "1. Go to: http://localhost:8000/Register.html\n";
            echo "2. The system will automatically show email verification\n";
            echo "3. Enter this code: " . $verificationResult['code'] . "\n";
            echo "4. Click 'Verify Email'\n";
            echo "\n✨ Your email will be verified and you can log in!\n";
            
        } else {
            echo "\n❌ Failed to generate verification code: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "\n❌ Registration failed: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
