<?php
/**
 * Forgot Password Endpoint
 * POST /api/auth/forgot-password
 */

// Include required files
require_once '../../config/database.php';
require_once '../../models/User.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email_or_phone'])) {
    sendError('Email or phone number is required', 400);
}

$emailOrPhone = trim($input['email_or_phone']);

if (empty($emailOrPhone)) {
    sendError('Email or phone number cannot be empty', 400);
}

try {
    $user = new User();
    
    // Check if user exists by email or phone
    $userData = $user->findByEmailOrPhone($emailOrPhone);
    
    if (!$userData) {
        // For security, don't reveal if user exists or not
        sendSuccess(['message' => 'If the email or phone number exists in our system, a reset code has been sent.'], 'Reset code sent');
    }
    
    // Generate reset code
    $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes')); // 15 minutes expiry
    
    // Store reset code in database
    $result = $user->storePasswordResetCode($userData['id'], $resetCode, $expiresAt);
    
    if (!$result) {
        sendError('Failed to generate reset code', 500);
    }
    
    // Send reset code via email or SMS
    $isEmail = filter_var($emailOrPhone, FILTER_VALIDATE_EMAIL);
    
    if ($isEmail) {
        // Send email
        $emailResult = $user->sendPasswordResetEmail($userData['email'], $resetCode);
        if (!$emailResult['success']) {
            sendError('Failed to send reset code via email', 500);
        }
    } else {
        // Send SMS (for now, just log it - in production you'd use SMS service)
        error_log("Password reset code for phone {$emailOrPhone}: {$resetCode}");
    }
    
    sendSuccess([
        'message' => 'Reset code sent successfully',
        'method' => $isEmail ? 'email' : 'sms',
        'expires_in_minutes' => 15
    ], 'Reset code sent');
    
} catch (Exception $e) {
    sendError('Failed to process password reset request: ' . $e->getMessage(), 500);
}
?>






