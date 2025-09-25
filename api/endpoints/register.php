<?php
// register.php
// Required headers already set in index.php

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
    
    // Generate verification code for phone if provided
    $phone_verification_result = null;
    if (!empty($result['phone'])) {
        $phone_verification_result = $user->generateVerificationCode(
            $result['user_id'], 
            'phone', 
            $result['phone']
        );
    }
    
    http_response_code(201);
    echo json_encode(array(
        "status" => 201,
        "message" => "User registered successfully. Please verify your email and phone number.",
        "data" => array(
            "user_id" => $result['user_id'],
            "email" => $result['email'],
            "phone" => $result['phone'],
            "verification" => array(
                "email_code" => $verification_result['success'] ? $verification_result['code'] : null,
                "phone_code" => $phone_verification_result && $phone_verification_result['success'] ? $phone_verification_result['code'] : null,
                "expires_at" => $verification_result['expires_at']
            )
        )
    ));
} else {
    http_response_code(400);
    echo json_encode(array("message" => $result['message']));
}
?>
