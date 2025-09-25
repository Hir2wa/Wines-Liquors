<?php
// user_login.php
// Required headers already set in index.php

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['emailOrPhone']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Email/Phone and password are required"));
    exit();
}

// Get device information
$device_info = [
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'device_info' => $data['deviceInfo'] ?? ''
];

// Attempt login
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$result = $user->login($data['emailOrPhone'], $data['password'], $device_info);

if ($result['success']) {
    http_response_code(200);
    echo json_encode(array(
        "status" => 200,
        "message" => "Login successful",
        "data" => array(
            "user" => $result['user'],
            "session_token" => $result['session_token']
        )
    ));
} else {
    http_response_code(401);
    echo json_encode(array("message" => $result['message']));
}
?>
