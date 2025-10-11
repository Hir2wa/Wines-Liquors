<?php
/**
 * Verify Reset Code Endpoint
 * POST /api/auth/verify-reset-code
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

if (!$input || !isset($input['email_or_phone']) || !isset($input['code'])) {
    sendError('Email/phone and verification code are required', 400);
}

$emailOrPhone = trim($input['email_or_phone']);
$code = trim($input['code']);

if (empty($emailOrPhone) || empty($code)) {
    sendError('Email/phone and verification code cannot be empty', 400);
}

if (!preg_match('/^\d{6}$/', $code)) {
    sendError('Verification code must be 6 digits', 400);
}

try {
    $user = new User();
    
    // Find user by email or phone
    $userData = $user->findByEmailOrPhone($emailOrPhone);
    
    if (!$userData) {
        sendError('Invalid email or phone number', 400);
    }
    
    // Verify reset code
    $resetData = $user->verifyPasswordResetCode($userData['id'], $code);
    
    if (!$resetData) {
        sendError('Invalid or expired reset code', 400);
    }
    
    // Generate reset token for the next step
    $resetToken = bin2hex(random_bytes(32));
    $tokenExpiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes')); // 30 minutes for password reset
    
    // Store reset token
    $result = $user->storePasswordResetToken($userData['id'], $resetToken, $tokenExpiresAt);
    
    if (!$result) {
        sendError('Failed to generate reset token', 500);
    }
    
    sendSuccess([
        'reset_token' => $resetToken,
        'expires_in_minutes' => 30,
        'user_id' => $userData['id']
    ], 'Reset code verified successfully');
    
} catch (Exception $e) {
    sendError('Failed to verify reset code: ' . $e->getMessage(), 500);
}
?>




