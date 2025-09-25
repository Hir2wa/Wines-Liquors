<?php
// update_profile.php
// Required headers already set in index.php

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

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
    if (isset($data['user_id'])) {
        $user_id = $data['user_id'];
    }
}

if (!$user_id) {
    http_response_code(401);
    echo json_encode(array("message" => "User not authenticated"));
    exit();
}

// Validate required fields
if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode(array("message" => "First name, last name, and email are required"));
    exit();
}

// Validate email format
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid email format"));
    exit();
}

// Check if email is already taken by another user
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id FROM users WHERE email = :email AND id != :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":email", $data['email']);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Email is already taken by another user"));
    exit();
}

// Check if phone is already taken by another user (if provided)
if (!empty($data['phone'])) {
    $query = "SELECT id FROM users WHERE phone = :phone AND id != :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":phone", $data['phone']);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("message" => "Phone number is already taken by another user"));
        exit();
    }
}

// Update user profile
$query = "UPDATE users SET 
          first_name = :first_name,
          last_name = :last_name,
          email = :email,
          phone = :phone,
          date_of_birth = :date_of_birth,
          gender = :gender,
          address = :address,
          city = :city,
          country = :country,
          updated_at = CURRENT_TIMESTAMP
          WHERE id = :user_id";

$stmt = $db->prepare($query);

// Sanitize and bind data
$first_name = htmlspecialchars(strip_tags($data['firstName']));
$last_name = htmlspecialchars(strip_tags($data['lastName']));
$email = htmlspecialchars(strip_tags($data['email']));
$phone = !empty($data['phone']) ? htmlspecialchars(strip_tags($data['phone'])) : null;
$date_of_birth = !empty($data['dateOfBirth']) ? $data['dateOfBirth'] : null;
$gender = !empty($data['gender']) ? htmlspecialchars(strip_tags($data['gender'])) : null;
$address = !empty($data['address']) ? htmlspecialchars(strip_tags($data['address'])) : null;
$city = !empty($data['city']) ? htmlspecialchars(strip_tags($data['city'])) : null;
$country = !empty($data['country']) ? htmlspecialchars(strip_tags($data['country'])) : 'Rwanda';

$stmt->bindParam(":first_name", $first_name);
$stmt->bindParam(":last_name", $last_name);
$stmt->bindParam(":email", $email);
$stmt->bindParam(":phone", $phone);
$stmt->bindParam(":date_of_birth", $date_of_birth);
$stmt->bindParam(":gender", $gender);
$stmt->bindParam(":address", $address);
$stmt->bindParam(":city", $city);
$stmt->bindParam(":country", $country);
$stmt->bindParam(":user_id", $user_id);

if ($stmt->execute()) {
    // Get updated user data
    $query = "SELECT id, email, phone, first_name, last_name, date_of_birth, gender, address, city, country, 
                     is_verified, email_verified, phone_verified, created_at, updated_at, last_login
              FROM users WHERE id = :user_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
        "message" => "Profile updated successfully",
        "data" => $profile_data
    ));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to update profile"));
}
?>
