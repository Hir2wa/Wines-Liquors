<?php
/**
 * User Model
 * Handles user registration, authentication, and management
 */

class User {
    private $conn;
    private $table_name = "users";
    private $verification_table = "verification_codes";
    private $sessions_table = "user_sessions";
    private $login_attempts_table = "login_attempts";
    private $preferences_table = "user_preferences";

    public $id;
    public $email;
    public $phone;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $date_of_birth;
    public $gender;
    public $address;
    public $city;
    public $country;
    public $is_verified;
    public $is_active;
    public $email_verified;
    public $phone_verified;
    public $created_at;
    public $updated_at;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new user
     */
    public function register($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Check if phone already exists (if provided)
        if (!empty($data['phone']) && $this->phoneExists($data['phone'])) {
            return ['success' => false, 'message' => 'Phone number already registered'];
        }

        // Validate required fields
        $required_fields = ['email', 'password', 'first_name', 'last_name'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "Field '$field' is required"];
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Validate phone format (if provided)
        if (!empty($data['phone']) && !$this->isValidPhone($data['phone'])) {
            return ['success' => false, 'message' => 'Invalid phone number format'];
        }

        // Hash password
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $query = "INSERT INTO " . $this->table_name . "
                (email, phone, password_hash, first_name, last_name, date_of_birth, gender, address, city, country)
                VALUES
                (:email, :phone, :password_hash, :first_name, :last_name, :date_of_birth, :gender, :address, :city, :country)";

        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->email = htmlspecialchars(strip_tags($data['email']));
        $this->phone = !empty($data['phone']) ? htmlspecialchars(strip_tags($data['phone'])) : null;
        $this->password_hash = $password_hash;
        $this->first_name = htmlspecialchars(strip_tags($data['first_name']));
        $this->last_name = htmlspecialchars(strip_tags($data['last_name']));
        $this->date_of_birth = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        $this->gender = !empty($data['gender']) ? htmlspecialchars(strip_tags($data['gender'])) : null;
        $this->address = !empty($data['address']) ? htmlspecialchars(strip_tags($data['address'])) : null;
        $this->city = !empty($data['city']) ? htmlspecialchars(strip_tags($data['city'])) : null;
        $this->country = !empty($data['country']) ? htmlspecialchars(strip_tags($data['country'])) : 'Rwanda';

        // Bind values
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);

        if ($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            $this->id = $user_id;
            
            // Create default preferences
            $this->createDefaultPreferences($user_id);
            
            return [
                'success' => true, 
                'message' => 'User registered successfully',
                'user_id' => $user_id,
                'email' => $this->email,
                'phone' => $this->phone
            ];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    /**
     * Authenticate user login
     */
    public function login($email_or_phone, $password, $device_info = []) {
        // Find user by email or phone
        $user = $this->findByEmailOrPhone($email_or_phone);
        
        if (!$user) {
            $this->logLoginAttempt($email_or_phone, $device_info, false, 'User not found');
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Check if user is active
        if (!$user['is_active']) {
            $this->logLoginAttempt($email_or_phone, $device_info, false, 'Account deactivated');
            return ['success' => false, 'message' => 'Account is deactivated'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->logLoginAttempt($email_or_phone, $device_info, false, 'Invalid password');
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Update last login
        $this->updateLastLogin($user['id']);

        // Create session
        $session_token = $this->createSession($user['id'], $device_info);

        // Log successful login
        $this->logLoginAttempt($email_or_phone, $device_info, true);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'is_verified' => $user['is_verified'],
                'email_verified' => $user['email_verified'],
                'phone_verified' => $user['phone_verified'],
                'is_admin' => $user['is_admin'] ?? false
            ],
            'session_token' => $session_token
        ];
    }

    /**
     * Generate verification code
     */
    public function generateVerificationCode($user_id, $type, $contact_info) {
        // Generate 6-digit code
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Set expiration (15 minutes from now) - get from database to avoid timezone issues
        $query_time = "SELECT NOW() + INTERVAL '15 minutes' as expires_at";
        $stmt_time = $this->conn->prepare($query_time);
        $stmt_time->execute();
        $time_result = $stmt_time->fetch(PDO::FETCH_ASSOC);
        $expires_at = $time_result['expires_at'];

        // Invalidate any existing codes for this user and type
        $this->invalidateVerificationCodes($user_id, $type);

        // Insert new verification code
        $query = "INSERT INTO " . $this->verification_table . "
                (user_id, code, type, contact_info, expires_at)
                VALUES
                (:user_id, :code, :type, :contact_info, :expires_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":contact_info", $contact_info);
        $stmt->bindParam(":expires_at", $expires_at);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'code' => $code,
                'expires_at' => $expires_at
            ];
        }

        return ['success' => false, 'message' => 'Failed to generate verification code'];
    }

    /**
     * Verify code
     */
    public function verifyCode($user_id, $code, $type) {
        $query = "SELECT * FROM " . $this->verification_table . "
                WHERE user_id = :user_id 
                AND code = :code 
                AND type = :type 
                AND expires_at > CURRENT_TIMESTAMP 
                AND is_used = FALSE
                ORDER BY created_at DESC 
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":type", $type);
        $stmt->execute();

        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            // Increment attempts
            $this->incrementVerificationAttempts($user_id, $code, $type);
            return ['success' => false, 'message' => 'Invalid or expired verification code'];
        }

        // Mark code as used
        $this->markVerificationCodeUsed($verification['id']);

        // Update user verification status
        if ($type === 'email') {
            $this->updateEmailVerification($user_id, true);
        } elseif ($type === 'phone') {
            $this->updatePhoneVerification($user_id, true);
        }

        return ['success' => true, 'message' => 'Verification successful'];
    }

    /**
     * Check if email exists
     */
    private function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if phone exists
     */
    private function phoneExists($phone) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":phone", $phone);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Validate phone number format
     */
    private function isValidPhone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Rwandan phone number
        // Rwandan numbers: +250XXXXXXXXX or 250XXXXXXXXX or 0XXXXXXXXX
        if (preg_match('/^(\+?250|0)?[0-9]{9}$/', $phone)) {
            return true;
        }
        
        return false;
    }

    /**
     * Find user by email or phone
     */
    private function findByEmailOrPhone($email_or_phone) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE email = :email_or_phone OR phone = :email_or_phone 
                LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email_or_phone", $email_or_phone);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create user session
     */
    private function createSession($user_id, $device_info) {
        $session_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));

        $query = "INSERT INTO " . $this->sessions_table . "
                (user_id, session_token, device_info, ip_address, user_agent, expires_at)
                VALUES
                (:user_id, :session_token, :device_info, :ip_address, :user_agent, :expires_at)";

        $stmt = $this->conn->prepare($query);
        
        $device_info_value = $device_info['device_info'] ?? '';
        $ip_address_value = $device_info['ip_address'] ?? '';
        $user_agent_value = $device_info['user_agent'] ?? '';
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":session_token", $session_token);
        $stmt->bindParam(":device_info", $device_info_value);
        $stmt->bindParam(":ip_address", $ip_address_value);
        $stmt->bindParam(":user_agent", $user_agent_value);
        $stmt->bindParam(":expires_at", $expires_at);

        if ($stmt->execute()) {
            return $session_token;
        }

        return null;
    }

    /**
     * Log login attempt
     */
    private function logLoginAttempt($email_or_phone, $device_info, $success, $failure_reason = null) {
        $query = "INSERT INTO " . $this->login_attempts_table . "
                (email, phone, ip_address, user_agent, success, failure_reason)
                VALUES
                (:email, :phone, :ip_address, :user_agent, :success, :failure_reason)";

        $stmt = $this->conn->prepare($query);
        
        // Determine if it's email or phone
        $email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL) ? $email_or_phone : null;
        $phone = $email ? null : $email_or_phone;

        $ip_address_value = $device_info['ip_address'] ?? '';
        $user_agent_value = $device_info['user_agent'] ?? '';
        
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":ip_address", $ip_address_value);
        $stmt->bindParam(":user_agent", $user_agent_value);
        $stmt->bindParam(":success", $success, PDO::PARAM_BOOL);
        $stmt->bindParam(":failure_reason", $failure_reason);

        $stmt->execute();
    }

    /**
     * Update last login
     */
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Create default preferences
     */
    private function createDefaultPreferences($user_id) {
        $query = "INSERT INTO " . $this->preferences_table . "
                (user_id) VALUES (:user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Invalidate verification codes
     */
    private function invalidateVerificationCodes($user_id, $type) {
        $query = "UPDATE " . $this->verification_table . " 
                SET is_used = TRUE 
                WHERE user_id = :user_id AND type = :type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
    }

    /**
     * Mark verification code as used
     */
    private function markVerificationCodeUsed($verification_id) {
        $query = "UPDATE " . $this->verification_table . " 
                SET is_used = TRUE 
                WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $verification_id);
        $stmt->execute();
    }

    /**
     * Increment verification attempts
     */
    private function incrementVerificationAttempts($user_id, $code, $type) {
        $query = "UPDATE " . $this->verification_table . " 
                SET attempts = attempts + 1 
                WHERE user_id = :user_id AND code = :code AND type = :type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":code", $code);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
    }

    /**
     * Update email verification status
     */
    private function updateEmailVerification($user_id, $verified) {
        $query = "UPDATE " . $this->table_name . " 
                SET email_verified = :verified, is_verified = :is_verified 
                WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":verified", $verified);
        $stmt->bindParam(":is_verified", $verified);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    /**
     * Update phone verification status
     */
    private function updatePhoneVerification($user_id, $verified) {
        $query = "UPDATE " . $this->table_name . " 
                SET phone_verified = :verified, is_verified = :is_verified 
                WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":verified", $verified);
        $stmt->bindParam(":is_verified", $verified);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }
}
?>
