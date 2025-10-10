<?php
// Generate Verification Code for User ID 2
require_once 'config/database.php';
require_once 'models/User.php';

echo "Generating Phone Verification Code for User ID 2...\n";

try {
    $database = new Database();
    $user = new User($database->getConnection());
    
    // Get user by ID
    $query = "SELECT * FROM users WHERE id = 2";
    $stmt = $database->getConnection()->prepare($query);
    $stmt->execute();
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        echo "✅ Found user:\n";
        echo "User ID: " . $existingUser['id'] . "\n";
        echo "Email: " . $existingUser['email'] . "\n";
        echo "Phone: " . $existingUser['phone'] . "\n";
        echo "Name: " . $existingUser['first_name'] . " " . $existingUser['last_name'] . "\n";
        
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
            
            echo "\n🎯 INSTRUCTIONS FOR YOU:\n";
            echo "1. Go to: http://localhost:8000/Register.html\n";
            echo "2. The registration should show verification method selection\n";
            echo "3. Choose 'Phone Verification'\n";
            echo "4. Enter this code: " . $verificationResult['code'] . "\n";
            echo "5. Click 'Verify Phone'\n";
            echo "\n✨ Your phone number 0780146863 will be verified!\n";
            
        } else {
            echo "\n❌ Failed to generate verification code: " . $verificationResult['message'] . "\n";
        }
        
    } else {
        echo "❌ User ID 2 not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>


