<?php
// verify_code.php
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
require_once __DIR__ . '/../../models/User.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['userId']) || empty($data['code']) || empty($data['type'])) {
    http_response_code(400);
    echo json_encode(array("message" => "User ID, code, and type are required"));
    exit();
}

// Validate type
if (!in_array($data['type'], ['email', 'phone', 'password_reset'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid verification type"));
    exit();
}

// Verify code
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$result = $user->verifyCode($data['userId'], $data['code'], $data['type']);

if ($result['success']) {
    http_response_code(200);
    echo json_encode(array(
        "status" => 200,
        "message" => "Verification successful",
        "data" => array(
            "verified" => true,
            "type" => $data['type']
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result['message']));
}
?>
