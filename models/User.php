<?php
/**
 * User Model
 * Handles user registration, authentication, and management
 */

// Note: External dependencies (libphonenumber and PHPMailer) are optional
// The system will work with basic validation if these are not available

if (!class_exists('User')) {
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

        // Validate and format phone number (if provided)
        if (!empty($data['phone'])) {
            if (!$this->isValidPhone($data['phone'])) {
                return ['success' => false, 'message' => 'Invalid phone number format'];
            }
            // Format phone number to international format
            $data['phone'] = $this->formatPhoneNumber($data['phone']);
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
     * Send email verification (using PHPMailer)
     */
    public function sendEmailVerification($user_id, $email, $code) {
        try {
            // Try to load PHPMailer autoloader
            $autoloader_path = __DIR__ . '/../vendor/autoload.php';
            if (file_exists($autoloader_path)) {
                require_once $autoloader_path;
            }
            
            // Check if PHPMailer is available
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendEmailWithPHPMailer($email, $code);
            } else {
                // Fallback to basic mail() function with proper error handling
                return $this->sendEmailWithBasicMail($email, $code);
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to send verification email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmailWithPHPMailer($email, $code) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Load email configuration
            $emailConfig = require __DIR__ . '/../config/email.php';
            $smtp = $emailConfig['smtp'];
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtp['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['username'];
            $mail->Password = $smtp['password'];
            $mail->SMTPSecure = $smtp['encryption'];
            $mail->Port = $smtp['port'];
            
            // Recipients
            $mail->setFrom($smtp['from_email'], $smtp['from_name']);
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $emailConfig['verification']['email_subject'];
            $mail->Body = "
                <html>
                <head><title>Email Verification</title></head>
                <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='background: #f8f9fa; padding: 30px; border-radius: 10px; text-align: center;'>
                        <h2 style='color: #dc3545; margin-bottom: 20px;'>Email Verification</h2>
                        <p style='font-size: 16px; color: #333; margin-bottom: 20px;'>Thank you for registering with Wines & Liquors!</p>
                        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #dc3545;'>
                            <p style='margin: 0; font-size: 14px; color: #666;'>Your verification code is:</p>
                            <h1 style='color: #dc3545; font-size: 32px; margin: 10px 0; letter-spacing: 5px;'>{$code}</h1>
                        </div>
                        <p style='font-size: 14px; color: #666; margin-bottom: 20px;'>This code will expire in 15 minutes.</p>
                        <p style='font-size: 12px; color: #999;'>If you didn't request this verification, please ignore this email.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 14px; color: #666; margin: 0;'>Best regards,<br><strong>Wines & Liquors Team</strong></p>
                    </div>
                </body>
                </html>
            ";
            
            $mail->send();
            return ['success' => true, 'message' => 'Verification email sent successfully'];
            
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to send verification email via SMTP'];
        }
    }
    
    /**
     * Send email using basic mail() function (fallback)
     */
    private function sendEmailWithBasicMail($email, $code) {
        $from_email = 'alainfabricehirwa@gmail.com';
        $from_name = 'Wines & Liquors';
        $subject = 'Email Verification - Wines & Liquors';
        $message = "
            <html>
            <head><title>Email Verification</title></head>
            <body>
                <h2>Email Verification</h2>
                <p>Thank you for registering with Wines & Liquors!</p>
                <p>Your verification code is: <strong style='color: #dc3545; font-size: 18px;'>{$code}</strong></p>
                <p>This code will expire in 15 minutes.</p>
                <p>If you didn't request this verification, please ignore this email.</p>
                <br>
                <p>Best regards,<br>Wines & Liquors Team</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$from_name} <{$from_email}>" . "\r\n";
        $headers .= "Reply-To: {$from_email}" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Suppress warnings and capture output to prevent breaking JSON response
        ob_start();
        $mail_result = @mail($email, $subject, $message, $headers);
        $output = ob_get_clean();
        
        if ($mail_result) {
            return ['success' => true, 'message' => 'Verification email sent successfully'];
        } else {
            // Log the error but don't output it to prevent breaking JSON
            error_log("Email sending failed for $email: " . $output);
            return ['success' => false, 'message' => 'Failed to send verification email - please check your email configuration'];
        }
    }
    
    /**
     * Send SMS verification (DISABLED - using email only)
     */
    public function sendSMSVerification($phone, $code) {
        // SMS verification is disabled - using email verification only
        return [
            'success' => false, 
            'message' => 'SMS verification is disabled. Please use email verification.'
        ];
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
     * Validate phone number format (basic validation for Rwandan numbers)
     */
    private function isValidPhone($phone) {
        if (empty($phone)) {
            return false;
        }
        
        // Remove all non-digit characters except +
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
        
        // Check various Rwandan phone number formats
        $patterns = [
            '/^\+250[0-9]{9}$/',           // +250XXXXXXXXX
            '/^250[0-9]{9}$/',             // 250XXXXXXXXX
            '/^0[0-9]{9}$/',               // 0XXXXXXXXX
            '/^[0-9]{9}$/'                 // XXXXXXXXX (9 digits)
        ];
        
        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanPhone)) {
                $isValid = true;
                break;
            }
        }
        
        // Additional length checks
        if ($cleanPhone[0] === '0' && strlen($cleanPhone) !== 10) {
            return false;
        }
        if (strpos($cleanPhone, '+250') === 0 && strlen($cleanPhone) !== 13) {
            return false;
        }
        if (strpos($cleanPhone, '250') === 0 && strlen($cleanPhone) !== 12) {
            return false;
        }
        
        // Validate Rwandan mobile prefixes (78, 79, 72, 73)
        if ($isValid) {
            $phoneDigits = $this->extractPhoneDigits($cleanPhone);
            if (strlen($phoneDigits) >= 2) {
                $prefix = substr($phoneDigits, 0, 2);
                $validPrefixes = ['78', '79', '72', '73'];
                if (!in_array($prefix, $validPrefixes)) {
                    return false;
                }
            }
        }
        
        return $isValid;
    }
    
    /**
     * Extract phone digits for validation
     */
    private function extractPhoneDigits($phone) {
        // Remove all non-digit characters except +
        $clean = preg_replace('/[^\d+]/', '', $phone);
        
        // Handle different formats
        if (strpos($clean, '+250') === 0) {
            return substr($clean, 4); // Remove +250
        } elseif (strpos($clean, '250') === 0) {
            return substr($clean, 3); // Remove 250
        } elseif (strpos($clean, '0') === 0) {
            return substr($clean, 1); // Remove leading 0
        }
        
        return $clean;
    }
    
    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone) {
        // Remove all non-digit characters except +
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
        
        // Convert to international format
        if (preg_match('/^0([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+250' . $matches[1];
        } elseif (preg_match('/^250([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+250' . $matches[1];
        } elseif (preg_match('/^([0-9]{9})$/', $cleanPhone, $matches)) {
            return '+250' . $matches[1];
        }
        
        return $cleanPhone; // Return as-is if already in correct format
    }

    /**
     * Find user by email or phone
     */
    public function findByEmailOrPhone($email_or_phone) {
        $isEmail = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":email", $email_or_phone);
        } else {
            $sql = "SELECT * FROM " . $this->table_name . " WHERE phone = :phone LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":phone", $email_or_phone);
        }
        
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


    /**
     * Store password reset code
     */
    public function storePasswordResetCode($userId, $code, $expiresAt) {
        try {
            // Create password_reset_codes table if it doesn't exist
            $this->createPasswordResetTable();
            
            $sql = "INSERT INTO password_reset_codes (user_id, reset_code, expires_at, created_at) 
                    VALUES (:user_id, :reset_code, :expires_at, NOW())
                    ON CONFLICT (user_id) DO UPDATE SET 
                    reset_code = :reset_code, expires_at = :expires_at, created_at = NOW()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":reset_code", $code);
            $stmt->bindParam(":expires_at", $expiresAt);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error storing password reset code: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password reset code
     */
    public function verifyPasswordResetCode($userId, $code) {
        try {
            $sql = "SELECT * FROM password_reset_codes 
                    WHERE user_id = :user_id AND reset_code = :reset_code 
                    AND expires_at > NOW() LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":reset_code", $code);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error verifying password reset code: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Store password reset token
     */
    public function storePasswordResetToken($userId, $token, $expiresAt) {
        try {
            $sql = "INSERT INTO password_reset_tokens (user_id, reset_token, expires_at, created_at) 
                    VALUES (:user_id, :reset_token, :expires_at, NOW())
                    ON CONFLICT (user_id) DO UPDATE SET 
                    reset_token = :reset_token, expires_at = :expires_at, created_at = NOW()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":reset_token", $token);
            $stmt->bindParam(":expires_at", $expiresAt);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error storing password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken($token) {
        try {
            $sql = "SELECT * FROM password_reset_tokens 
                    WHERE reset_token = :reset_token AND expires_at > NOW() LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":reset_token", $token);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error verifying password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE " . $this->table_name . " 
                    SET password_hash = :password_hash, updated_at = NOW() 
                    WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":password_hash", $passwordHash);
            $stmt->bindParam(":user_id", $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all password reset tokens for a user
     */
    public function clearPasswordResetTokens($userId) {
        try {
            $sql = "DELETE FROM password_reset_tokens WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            $sql = "DELETE FROM password_reset_codes WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            error_log("Error clearing password reset tokens: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $code) {
        try {
            $subject = 'Password Reset - Wines & Liquors';
            $message = "
                <html>
                <head><title>Password Reset</title></head>
                <body>
                    <h2>Password Reset Request</h2>
                    <p>You have requested to reset your password for your Wines & Liquors account.</p>
                    <p>Your reset code is: <strong style='color: #dc3545; font-size: 18px;'>{$code}</strong></p>
                    <p>This code will expire in 15 minutes.</p>
                    <p>If you didn't request this password reset, please ignore this email.</p>
                    <br>
                    <p>Best regards,<br>Wines & Liquors Team</p>
                </body>
                </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Wines & Liquors <alainfabricehirwa@gmail.com>" . "\r\n";
            $headers .= "Reply-To: alainfabricehirwa@gmail.com" . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            if (mail($email, $subject, $message, $headers)) {
                return ['success' => true, 'message' => 'Password reset email sent successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to send password reset email'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to send password reset email: ' . $e->getMessage()];
        }
    }

    /**
     * Update verification code
     */
    public function updateVerificationCode($userId, $type, $code, $expiresAt) {
        try {
            $sql = "UPDATE " . $this->verification_table . " 
                    SET {$type}_code = :code, {$type}_expires_at = :expires_at 
                    WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":code", $code);
            $stmt->bindParam(":expires_at", $expiresAt);
            $stmt->bindParam(":user_id", $userId);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating verification code: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create password reset tables if they don't exist
     */
    private function createPasswordResetTable() {
        try {
            // Create password_reset_codes table
            $sql = "CREATE TABLE IF NOT EXISTS password_reset_codes (
                user_id INTEGER PRIMARY KEY,
                reset_code VARCHAR(6) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->conn->exec($sql);
            
            // Create password_reset_tokens table
            $sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
                user_id INTEGER PRIMARY KEY,
                reset_token VARCHAR(64) NOT NULL UNIQUE,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $this->conn->exec($sql);
            
            return true;
        } catch (Exception $e) {
            error_log("Error creating password reset tables: " . $e->getMessage());
            return false;
        }
    }
}
} // End of class_exists check
?>
