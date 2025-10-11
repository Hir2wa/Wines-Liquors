<?php
// Get Phone Verification Code for Existing User
require_once 'config/database.php';
require_once 'models/User.php';

echo "Getting Phone Verification Code for 0780146863...\n";

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
        
        // Generate new phone verification code
        echo "\n🔄 Generating new phone verification code...\n";
        $verificationResult = $user->generateVerificationCode(
            $existingUser['id'], 
            'phone', 
            $existingUser['phone']
        );
        
        if ($verificationResult['success']) {
            echo "\n📱 PHONE VERIFICATION CODE: " . $verificationResult['code'] . "\n";
            echo "📱 Use this code to verify your phone number: 0780146863\n";
            echo "⏰ Code expires at: " . $verificationResult['expires_at'] . "\n";
            
            // Test SMS sending (will log to server)
            $smsResult = $user->sendSMSVerification($existingUser['phone'], $verificationResult['code']);
            echo "\nSMS Result: " . $smsResult['message'] . "\n";
            
            echo "\n🎯 INSTRUCTIONS:\n";
            echo "1. Go to the registration page\n";
            echo "2. Choose 'Phone Verification'\n";
            echo "3. Enter this code: " . $verificationResult['code'] . "\n";
            echo "4. Click 'Verify Phone'\n";
            
        } else {
            echo "\n❌ Failed to generate verification code: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ No existing user found with phone 0780146863\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>



