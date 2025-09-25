<?php
/**
 * Admin Login Endpoint
 * POST /api/admin/login
 */

require_once '../../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['username']) || !isset($input['password'])) {
    sendError('Username and password are required', 400);
}

$username = $input['username'];
$password = $input['password'];

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get admin user
    $sql = "SELECT id, username, email, password_hash, full_name, role FROM admin_users WHERE username = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        sendError('Invalid credentials', 401);
    }
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        sendError('Invalid credentials', 401);
    }
    
    // Generate session token (simple implementation)
    $sessionToken = bin2hex(random_bytes(32));
    
    // Store session (in a real app, you'd use proper session management)
    $sessionData = [
        'admin_id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'full_name' => $admin['full_name'],
        'role' => $admin['role'],
        'login_time' => time()
    ];
    
    // In a real application, store this in Redis or database
    // For now, we'll just return the token
    $response = [
        'token' => $sessionToken,
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'full_name' => $admin['full_name'],
            'role' => $admin['role']
        ]
    ];
    
    sendSuccess($response, 'Login successful');
    
} catch (Exception $e) {
    sendError('Login failed: ' . $e->getMessage(), 500);
}
?>
