<?php
// get_profile.php
// Required headers already set in index.php

// Get user ID from session or request
$user_id = null;

// Check if user is logged in via session token
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $auth_header);
    
    // Get user ID from session token
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT user_id FROM user_sessions WHERE session_token = :token AND expires_at > CURRENT_TIMESTAMP";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($session) {
        $user_id = $session['user_id'];
    }
}

// If no session token, try to get from request body
if (!$user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['user_id'])) {
        $user_id = $data['user_id'];
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(array("message" => "User not authenticated"));
    exit();
}

// Get user profile data
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get user data from database
$query = "SELECT id, email, phone, first_name, last_name, date_of_birth, gender, address, city, country, 
                 is_verified, email_verified, phone_verified, created_at, updated_at, last_login
          FROM users WHERE id = :user_id";

$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    http_response_code(404);
    echo json_encode(array("message" => "User not found"));
    exit();
}

// Format the response
$profile_data = array(
    'id' => $user_data['id'],
    'email' => $user_data['email'],
    'phone' => $user_data['phone'],
    'firstName' => $user_data['first_name'],
    'lastName' => $user_data['last_name'],
    'dateOfBirth' => $user_data['date_of_birth'],
    'gender' => $user_data['gender'],
    'address' => $user_data['address'],
    'city' => $user_data['city'],
    'country' => $user_data['country'],
    'isVerified' => $user_data['is_verified'],
    'emailVerified' => $user_data['email_verified'],
    'phoneVerified' => $user_data['phone_verified'],
    'createdAt' => $user_data['created_at'],
    'updatedAt' => $user_data['updated_at'],
    'lastLogin' => $user_data['last_login']
);

http_response_code(200);
echo json_encode(array(
    "status" => 200,
    "message" => "Profile retrieved successfully",
    "data" => $profile_data
));
?>
