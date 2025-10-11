<?php
/**
 * Email Service using the same system as user registration
 * Uses Gmail SMTP with PHPMailer
 */

function sendOrderNotificationEmail($orderData) {
    try {
        // Try to load PHPMailer autoloader
        $autoloader_path = __DIR__ . '/vendor/autoload.php';
        if (file_exists($autoloader_path)) {
            require_once $autoloader_path;
        }
        
        // Check if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return sendEmailWithPHPMailer($orderData);
        } else {
            // Fallback to basic mail() function
            return sendEmailWithBasicMail($orderData);
        }
        
    } catch (Exception $e) {
        error_log('Email notification error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHPMailer (same as registration system)
 */
function sendEmailWithPHPMailer($orderData) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Load email configuration (same as registration)
        $emailConfig = require __DIR__ . '/config/email.php';
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
        $mail->setFrom($smtp['from_email'], 'NELVINTO Liquors store');
        $mail->addAddress('alainfabricehirwa@gmail.com'); // Your email
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Order Received: #{$orderData['orderId']} - NELVINTO Liquors store";
        $mail->Body = buildEmailHTML($orderData);
        
        $mail->send();
        
        // Also log to file for backup
        logOrderNotification($orderData);
        
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $e->getMessage());
        // Fallback to logging
        logOrderNotification($orderData);
        return false;
    }
}

/**
 * Send email using basic mail() function (fallback)
 */
function sendEmailWithBasicMail($orderData) {
    try {
        $to = 'alainfabricehirwa@gmail.com';
        $subject = "New Order Received: #{$orderData['orderId']} - NELVINTO Liquors store";
        $message = buildEmailHTML($orderData);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: NELVINTO Liquors store <alainfabricehirwa@gmail.com>',
            'Reply-To: alainfabricehirwa@gmail.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $result = mail($to, $subject, $message, implode("\r\n", $headers));
        
        // Also log to file
        logOrderNotification($orderData);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Basic mail error: " . $e->getMessage());
        // Fallback to logging
        logOrderNotification($orderData);
        return false;
    }
}

/**
 * Log order notification to file (backup)
 */
function logOrderNotification($orderData) {
    $logMessage = "EMAIL NOTIFICATION:\n";
    $logMessage .= "To: alainfabricehirwa@gmail.com\n";
    $logMessage .= "Subject: New Order Received: #{$orderData['orderId']} - NELVINTO Liquors store\n";
    $logMessage .= "Order ID: {$orderData['orderId']}\n";
    $logMessage .= "Customer: {$orderData['customerInfo']['firstName']} {$orderData['customerInfo']['lastName']}\n";
    $logMessage .= "Email: {$orderData['customerInfo']['email']}\n";
    $logMessage .= "Phone: {$orderData['customerInfo']['phone']}\n";
    $logMessage .= "Location: {$orderData['customerInfo']['location']}\n";
    $logMessage .= "Total: {$orderData['total']}\n";
    $logMessage .= "Payment Method: {$orderData['paymentMethod']}\n";
    
    if (isset($orderData['coordinates']) && $orderData['coordinates']['latitude']) {
        $logMessage .= "GPS Coordinates: {$orderData['coordinates']['latitude']}, {$orderData['coordinates']['longitude']}\n";
    }
    
    $logMessage .= "Items:\n";
    foreach ($orderData['items'] as $item) {
        $logMessage .= "- {$item['name']} x{$item['quantity']} = {$item['price']}\n";
    }
    
    $logMessage .= "\nAdmin Dashboard: http://localhost:8000/AdminDashboard.html\n";
    $logMessage .= "Google Maps: https://www.google.com/maps/search/?api=1&query=" . urlencode($orderData['customerInfo']['location']) . "\n";
    
    // Log to file
    file_put_contents('order_notifications.log', $logMessage . "\n" . str_repeat('-', 50) . "\n", FILE_APPEND);
    
    // Also log to error log
    error_log("ORDER NOTIFICATION: " . str_replace("\n", " | ", $logMessage));
}

function buildEmailHTML($orderData) {
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .order-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .btn { display: inline-block; background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍷 New Order Received!</h1>
                <p>NELVINTO Liquors store</p>
            </div>
            <div class='content'>
                <div class='order-details'>
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> {$orderData['orderId']}</p>
                    <p><strong>Customer:</strong> {$orderData['customerInfo']['firstName']} {$orderData['customerInfo']['lastName']}</p>
                    <p><strong>Email:</strong> {$orderData['customerInfo']['email']}</p>
                    <p><strong>Phone:</strong> {$orderData['customerInfo']['phone']}</p>
                    <p><strong>Location:</strong> {$orderData['customerInfo']['location']}</p>
                    <p><strong>Total:</strong> {$orderData['total']}</p>
                    <p><strong>Payment Method:</strong> {$orderData['paymentMethod']}</p>";
    
    if (isset($orderData['coordinates']) && $orderData['coordinates']['latitude']) {
        $html .= "<p><strong>GPS Coordinates:</strong> {$orderData['coordinates']['latitude']}, {$orderData['coordinates']['longitude']}</p>";
    }
    
    $html .= "
                </div>
                <div class='order-details'>
                    <h3>Order Items</h3>";
    
    foreach ($orderData['items'] as $item) {
        $html .= "<p>{$item['name']} x{$item['quantity']} = {$item['price']}</p>";
    }
    
    $html .= "
                </div>
                <div style='text-align: center; margin-top: 20px;'>
                    <a href='http://localhost:8000/AdminDashboard.html' class='btn'>View in Admin Dashboard</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>
