<?php
/**
 * Resend Verification Code Endpoint
 * POST /api/auth/resend-verification
 */

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

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

if (!$input || !isset($input['user_id']) || !isset($input['type'])) {
    sendError('User ID and verification type are required', 400);
}

$userId = trim($input['user_id']);
$type = trim($input['type']);

if (empty($userId) || empty($type)) {
    sendError('User ID and verification type cannot be empty', 400);
}

if (!in_array($type, ['email', 'phone'])) {
    sendError('Verification type must be either email or phone', 400);
}

try {
    $user = new User();
    
    // Get user data
    $userData = $user->getById($userId);
    
    if (!$userData) {
        sendError('User not found', 404);
    }
    
    // Generate new verification code
    $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Update verification code in database
    $result = $user->updateVerificationCode($userId, $type, $verificationCode, $expiresAt);
    
    if (!$result) {
        sendError('Failed to generate new verification code', 500);
    }
    
    // Send verification code
    if ($type === 'email') {
        $emailResult = $user->sendEmailVerification($userId, $userData['email'], $verificationCode);
        if (!$emailResult['success']) {
            sendError('Failed to send email verification code', 500);
        }
    } else {
        // For phone verification, you would send SMS here
        // For now, we'll just log it
        error_log("Phone verification code for {$userData['phone']}: {$verificationCode}");
    }
    
    sendSuccess([
        'message' => 'Verification code resent successfully',
        'type' => $type,
        'expires_in_minutes' => 15
    ], 'Verification code resent');
    
} catch (Exception $e) {
    sendError('Failed to resend verification code: ' . $e->getMessage(), 500);
}
?>
