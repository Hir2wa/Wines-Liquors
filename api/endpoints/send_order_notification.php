<?php
/**
 * Send Order Notification Endpoint
 * POST /api/orders/notify
 */

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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || !isset($input['orderId'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Order ID is required']);
        exit;
    }
    
    $orderId = $input['orderId'];
    $orderData = $input['orderData'] ?? [];
    
    // Admin email (you can change this to your email)
    $adminEmail = 'alainfabriceh@gmail.com';
    
    // Prepare email content
    $subject = "New Order Received - Order #$orderId";
    
    $message = "
    <html>
    <head>
        <title>New Order Notification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8f9fa; }
            .order-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .btn { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍷 New Order Received!</h1>
                <p>NELVINTO Liquors store</p>
            </div>
            
            <div class='content'>
                <h2>Order Details</h2>
                <div class='order-details'>
                    <p><strong>Order ID:</strong> #$orderId</p>
                    <p><strong>Customer:</strong> {$orderData['customerInfo']['firstName']} {$orderData['customerInfo']['lastName']}</p>
                    <p><strong>Email:</strong> {$orderData['customerInfo']['email']}</p>
                    <p><strong>Phone:</strong> {$orderData['customerInfo']['phone']}</p>
                    <p><strong>Location:</strong> {$orderData['customerInfo']['location']}</p>
                    <p><strong>Total Amount:</strong> " . number_format($orderData['totalAmount']) . " RWF</p>
                    <p><strong>Payment Method:</strong> " . ucwords(str_replace('_', ' ', $orderData['paymentMethod'])) . "</p>
                    <p><strong>Status:</strong> " . ucfirst($orderData['status']) . "</p>
                </div>
                    
                <h3>Order Items:</h3>
                <div class='order-details'>";
    
    if (isset($orderData['items']) && is_array($orderData['items'])) {
    foreach ($orderData['items'] as $item) {
            $message .= "<p>• {$item['name']} - Qty: {$item['quantity']} × {$item['price']}</p>";
        }
    }
    
    $message .= "
                </div>
                
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='http://localhost:8000/AdminDashboard.html' class='btn'>View in Admin Dashboard</a>
                    </div>
                </div>
                
            <div class='footer'>
                <p>This is an automated notification from NELVINTO Liquors store Order Management System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: NELVINTO Liquors store <noreply@nelvinto.com>',
        'Reply-To: noreply@nelvinto.com',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Send email
    $mailSent = mail($adminEmail, $subject, $message, implode("\r\n", $headers));
    
    if ($mailSent) {
        // Log the notification
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO admin_notifications (order_id, notification_type, message, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$orderId, 'new_order', "New order notification sent to $adminEmail"]);
        
        sendSuccess(['emailSent' => true, 'adminEmail' => $adminEmail], 'Order notification sent successfully');
    } else {
        sendError('Failed to send email notification', 500);
    }
    
} catch (Exception $e) {
    sendError('Failed to send notification: ' . $e->getMessage(), 500);
}
?>