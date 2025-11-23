<?php
/**
 * Reset Password Endpoint
 * POST /api/auth/reset-password
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

if (!$input || !isset($input['reset_token']) || !isset($input['new_password'])) {
    sendError('Reset token and new password are required', 400);
}

$resetToken = trim($input['reset_token']);
$newPassword = trim($input['new_password']);

if (empty($resetToken) || empty($newPassword)) {
    sendError('Reset token and new password cannot be empty', 400);
}

if (strlen($newPassword) < 8) {
    sendError('Password must be at least 8 characters long', 400);
}

try {
    $user = new User();
    
    // Verify reset token
    $tokenData = $user->verifyPasswordResetToken($resetToken);
    
    if (!$tokenData) {
        sendError('Invalid or expired reset token', 400);
    }
    
    // Update password
    $result = $user->updatePassword($tokenData['user_id'], $newPassword);
    
    if (!$result) {
        sendError('Failed to update password', 500);
    }
    
    // Clear all reset tokens for this user
    $user->clearPasswordResetTokens($tokenData['user_id']);
    
    sendSuccess([
        'message' => 'Password reset successfully',
        'user_id' => $tokenData['user_id']
    ], 'Password reset successfully');
    
} catch (Exception $e) {
    sendError('Failed to reset password: ' . $e->getMessage(), 500);
}
?>





