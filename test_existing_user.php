<?php
// Test existing user with phone 0780146863
require_once 'config/database.php';
require_once 'models/User.php';

echo "Testing existing user with phone 0780146863...\n";

try {
    $database = new Database();
    $user = new User($database->getConnection());
    
    // Find existing user by phone
    $existingUser = $user->findByEmailOrPhone('0780146863');
    
    if ($existingUser) {
        echo "✅ Found existing user:\n";
        echo "User ID: " . $existingUser['id'] . "\n";
        echo "Email: " . $existingUser['email'] . "\n";
        echo "Phone: " . $existingUser['phone'] . "\n";
        echo "First Name: " . $existingUser['first_name'] . "\n";
        echo "Last Name: " . $existingUser['last_name'] . "\n";
        echo "Email Verified: " . ($existingUser['email_verified'] ? 'Yes' : 'No') . "\n";
        echo "Phone Verified: " . ($existingUser['phone_verified'] ? 'Yes' : 'No') . "\n";
        
        // Generate new verification code for phone
        echo "\n🔄 Generating new phone verification code...\n";
        $verificationResult = $user->generateVerificationCode(
            $existingUser['id'], 
            'phone', 
            $existingUser['phone']
        );
        
        if ($verificationResult['success']) {
            echo "✅ New phone verification code generated: " . $verificationResult['code'] . "\n";
            echo "Expires at: " . $verificationResult['expires_at'] . "\n";
            
            // Test verification
            echo "\n🔄 Testing phone verification...\n";
            $verifyResult = $user->verifyCode($existingUser['id'], $verificationResult['code'], 'phone');
            
            if ($verifyResult['success']) {
                echo "✅ Phone verification successful!\n";
            } else {
                echo "❌ Phone verification failed: " . $verifyResult['message'] . "\n";
            }
        } else {
            echo "❌ Failed to generate verification code: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ No existing user found with phone 0780146863\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>



