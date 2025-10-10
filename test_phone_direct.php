<?php
// Test Phone Verification Directly
require_once 'config/database.php';
require_once 'models/User.php';

echo "Testing Phone Verification for 0780146863...\n";

try {
    $database = new Database();
    $user = new User($database->getConnection());
    
    // Test registration data
    $userData = [
        'email' => 'testuser' . time() . '@example.com',
        'password' => 'TestPass123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '0780146863',
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
        
        // Generate phone verification code
        $verificationResult = $user->generateVerificationCode(
            $result['user_id'], 
            'phone', 
            $result['phone']
        );
        
        if ($verificationResult['success']) {
            echo "\n📱 PHONE VERIFICATION CODE: " . $verificationResult['code'] . "\n";
            echo "📱 Use this code to verify your phone number: 0780146863\n";
            echo "⏰ Code expires at: " . $verificationResult['expires_at'] . "\n";
            
            // Test SMS sending (will log to server)
            $smsResult = $user->sendSMSVerification($result['phone'], $verificationResult['code']);
            echo "\nSMS Result: " . $smsResult['message'] . "\n";
            
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


