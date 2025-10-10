<?php
// get_current_user.php
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

// Include required files
require_once __DIR__ . '/../../config/database.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
    exit;
}

try {
    // Get authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        sendError('Authorization token required', 401);
        exit;
    }
    
    $token = $matches[1];
    
    // Get database connection
    $db = Database::getConnection();
    
    // Find user session
    $sql = "SELECT u.*, us.session_token, us.created_at as session_created 
            FROM users u 
            INNER JOIN user_sessions us ON u.id = us.user_id 
            WHERE us.session_token = ? AND us.is_active = 1";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendError('Invalid or expired session', 401);
        exit;
    }
    
    // Check if session is expired (24 hours)
    $sessionAge = time() - strtotime($user['session_created']);
    if ($sessionAge > 86400) { // 24 hours
        // Deactivate expired session
        $deactivateSql = "UPDATE user_sessions SET is_active = 0 WHERE session_token = ?";
        $deactivateStmt = $db->prepare($deactivateSql);
        $deactivateStmt->execute([$token]);
        
        sendError('Session expired', 401);
        exit;
    }
    
    // Return user data (exclude sensitive information)
    $userData = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone'],
        'is_active' => $user['is_active'],
        'is_admin' => $user['is_admin'],
        'created_at' => $user['created_at']
    ];
    
    sendSuccess('User data retrieved successfully', $userData);
    
} catch (Exception $e) {
    sendError('Failed to retrieve user data: ' . $e->getMessage(), 500);
}
?>
