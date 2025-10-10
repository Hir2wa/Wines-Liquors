<?php
// register.php
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
if (
    empty($data['email']) ||
    empty($data['password']) ||
    empty($data['firstName']) ||
    empty($data['lastName'])
) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required fields: email, password, firstName, lastName"));
    exit();
}

// Validate password strength
if (strlen($data['password']) < 8) {
    http_response_code(400);
    echo json_encode(array("message" => "Password must be at least 8 characters long"));
    exit();
}

// Prepare user data
$userData = [
    'email' => $data['email'],
    'password' => $data['password'],
    'first_name' => $data['firstName'],
    'last_name' => $data['lastName'],
    'phone' => $data['phone'] ?? null,
    'date_of_birth' => $data['dateOfBirth'] ?? null,
    'gender' => $data['gender'] ?? null,
    'address' => $data['address'] ?? null,
    'city' => $data['city'] ?? null,
    'country' => $data['country'] ?? 'Rwanda'
];

// Create user
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$result = $user->register($userData);

if ($result['success']) {
    // Generate verification code for email
    $verification_result = $user->generateVerificationCode(
        $result['user_id'], 
        'email', 
        $result['email']
    );
    
    // Send email verification
    $email_sent = false;
    if ($verification_result['success']) {
        $email_result = $user->sendEmailVerification(
            $result['user_id'], 
            $result['email'], 
            $verification_result['code']
        );
        $email_sent = $email_result['success'];
    }
    
    // Phone verification is disabled - using email verification only
    
    http_response_code(201);
    echo json_encode(array(
        "status" => 201,
        "message" => "User registered successfully. Please verify your email address.",
        "data" => array(
            "user_id" => $result['user_id'],
            "email" => $result['email'],
            "phone" => $result['phone'],
            "verification" => array(
                "email_code" => $verification_result['success'] ? $verification_result['code'] : null,
                "expires_at" => $verification_result['expires_at'],
                "email_sent" => $email_sent
            )
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result['message']));
}
?>
