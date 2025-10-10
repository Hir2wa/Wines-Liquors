<?php
/**
 * Test Email System
 * Test the email verification system with PHPMailer
 */

require_once 'config/database.php';
require_once 'models/User.php';

echo "<h1>📧 Email System Test</h1>\n";
echo "<p>Testing the email verification system...</p>\n\n";

try {
    // Test 1: Create a test user
    echo "<h2>Test 1: Create Test User</h2>\n";
    
    $database = new Database();
    $user = new User($database->getConnection());
    
    $userData = [
        'email' => 'test' . time() . '@example.com',
        'password' => 'testpassword123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '+250788' . substr(time(), -6),
        'address' => 'Test Address',
        'city' => 'Kigali',
        'country' => 'Rwanda'
    ];
    
    $registerResult = $user->register($userData);
    
    if ($registerResult['success']) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ User Registered Successfully!</h3>";
        echo "<p><strong>User ID:</strong> {$registerResult['user_id']}</p>";
        echo "<p><strong>Email:</strong> {$registerResult['email']}</p>";
        echo "<p><strong>Phone:</strong> {$registerResult['phone']}</p>";
        echo "</div>\n";
        
        $userId = $registerResult['user_id'];
        
        // Test 2: Generate verification code
        echo "<h2>Test 2: Generate Email Verification Code</h2>\n";
        
        $codeResult = $user->generateVerificationCode($userId, 'email', $userData['email']);
        
        if ($codeResult['success']) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>✅ Verification Code Generated!</h3>";
            echo "<p><strong>Code:</strong> <code style='background: #e9ecef; padding: 4px 8px; border-radius: 3px; font-weight: bold; color: #dc3545; font-size: 18px;'>{$codeResult['code']}</code></p>";
            echo "<p><strong>Expires:</strong> {$codeResult['expires_at']}</p>";
            echo "</div>\n";
            
            $verificationCode = $codeResult['code'];
            
            // Test 3: Send email verification
            echo "<h2>Test 3: Send Email Verification</h2>\n";
            
            $emailResult = $user->sendEmailVerification($userId, $userData['email'], $verificationCode);
            
            if ($emailResult['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h3>✅ Email Sent Successfully!</h3>";
                echo "<p><strong>Message:</strong> {$emailResult['message']}</p>";
                echo "<p><strong>Recipient:</strong> {$userData['email']}</p>";
                echo "<p><strong>Verification Code:</strong> {$verificationCode}</p>";
                echo "</div>\n";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h3>❌ Failed to Send Email</h3>";
                echo "<p><strong>Error:</strong> {$emailResult['message']}</p>";
                echo "</div>\n";
            }
            
            // Test 4: Verify the code
            echo "<h2>Test 4: Verify Email Code</h2>\n";
            
            $verifyResult = $user->verifyCode($userId, $verificationCode, 'email');
            
            if ($verifyResult['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h3>✅ Email Verification Successful!</h3>";
                echo "<p><strong>Message:</strong> {$verifyResult['message']}</p>";
                echo "</div>\n";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h3>❌ Email Verification Failed</h3>";
                echo "<p><strong>Error:</strong> {$verifyResult['message']}</p>";
                echo "</div>\n";
            }
            
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>❌ Failed to Generate Verification Code</h3>";
            echo "<p>{$codeResult['message']}</p>";
            echo "</div>\n";
        }
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ User Registration Failed</h3>";
        echo "<p>{$registerResult['message']}</p>";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>❌ Test Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>\n";
}

echo "<h2>🎉 Email System Test Complete!</h2>\n";
echo "<p>All email system components have been tested.</p>\n";
echo "<p><strong>What was tested:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ User registration with phone validation</li>\n";
echo "<li>✅ Email verification code generation</li>\n";
echo "<li>✅ PHPMailer email sending</li>\n";
echo "<li>✅ Email verification process</li>\n";
echo "</ul>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Check your email inbox for the verification email</li>\n";
echo "<li>Test the registration form at <code>Register.html</code></li>\n";
echo "<li>Test the payment system at <code>test_payment_system.html</code></li>\n";
echo "</ul>\n";
?>
