<?php
// send_order_notification.php
// Required headers already set in index.php

// Get posted data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['orderId']) || !isset($data['type'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Order ID and notification type are required"));
    exit();
}

$orderId = $data['orderId'];
$type = $data['type']; // 'order_created', 'status_update', 'payment_update'

// Get order details
try {
    $order = new Order();
    $orderData = $order->getById($orderId);
    
    if (!$orderData) {
        http_response_code(404);
        echo json_encode(array("message" => "Order not found"));
        exit();
    }
    
    // Send email notification
    $emailSent = sendOrderEmail($orderData, $type);
    
    // Send mobile notification (if user has push token)
    $mobileSent = sendMobileNotification($orderData, $type);
    
    http_response_code(200);
    echo json_encode(array(
        "status" => 200,
        "message" => "Notifications sent successfully",
        "data" => [
            'email_sent' => $emailSent,
            'mobile_sent' => $mobileSent
        ]
    ));
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Failed to send notifications: " . $e->getMessage()));
}

/**
 * Send email notification for order events
 */
function sendOrderEmail($orderData, $type) {
    $customerEmail = $orderData['customerInfo']['email'];
    $customerName = $orderData['customerInfo']['firstName'] . ' ' . $orderData['customerInfo']['lastName'];
    
    // Email templates
    $templates = [
        'order_created' => [
            'subject' => 'Order Confirmation - Total Wine & More',
            'body' => generateOrderCreatedEmail($orderData, $customerName)
        ],
        'status_update' => [
            'subject' => 'Order Status Update - Total Wine & More',
            'body' => generateStatusUpdateEmail($orderData, $customerName)
        ],
        'payment_update' => [
            'subject' => 'Payment Status Update - Total Wine & More',
            'body' => generatePaymentUpdateEmail($orderData, $customerName)
        ]
    ];
    
    if (!isset($templates[$type])) {
        return false;
    }
    
    $template = $templates[$type];
    
    // For now, we'll simulate email sending
    // In production, you would use PHPMailer, SendGrid, or similar
    $emailData = [
        'to' => $customerEmail,
        'subject' => $template['subject'],
        'body' => $template['body'],
        'sent_at' => date('Y-m-d H:i:s')
    ];
    
    // Log email (in production, you'd actually send it)
    error_log("EMAIL NOTIFICATION: " . json_encode($emailData));
    
    return true;
}

/**
 * Send mobile push notification
 */
function sendMobileNotification($orderData, $type) {
    // Get user's push token from database (if column exists)
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if push_token column exists
    $checkColumn = "SELECT column_name FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'push_token'";
    $checkStmt = $db->prepare($checkColumn);
    $checkStmt->execute();
    $columnExists = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$columnExists) {
        return false; // Push token column doesn't exist
    }
    
    $query = "SELECT push_token FROM users WHERE email = :email AND push_token IS NOT NULL";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $orderData['customerInfo']['email']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !$user['push_token']) {
        return false; // No push token available
    }
    
    // Notification messages
    $messages = [
        'order_created' => [
            'title' => 'Order Confirmed!',
            'body' => 'Your order #' . $orderData['orderId'] . ' has been confirmed. Total: ' . $orderData['total']
        ],
        'status_update' => [
            'title' => 'Order Status Update',
            'body' => 'Your order #' . $orderData['orderId'] . ' status: ' . ucfirst($orderData['status'])
        ],
        'payment_update' => [
            'title' => 'Payment Update',
            'body' => 'Your order #' . $orderData['orderId'] . ' payment: ' . ucfirst($orderData['paymentStatus'])
        ]
    ];
    
    if (!isset($messages[$type])) {
        return false;
    }
    
    $message = $messages[$type];
    
    // For now, we'll simulate push notification
    // In production, you would use Firebase Cloud Messaging (FCM)
    $pushData = [
        'to' => $user['push_token'],
        'title' => $message['title'],
        'body' => $message['body'],
        'data' => [
            'orderId' => $orderData['orderId'],
            'type' => $type
        ],
        'sent_at' => date('Y-m-d H:i:s')
    ];
    
    // Log push notification (in production, you'd actually send it)
    error_log("PUSH NOTIFICATION: " . json_encode($pushData));
    
    return true;
}

/**
 * Generate order created email template
 */
function generateOrderCreatedEmail($orderData, $customerName) {
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
            .item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
            .total { font-weight: bold; font-size: 18px; color: #dc3545; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Total Wine & More</h1>
                <h2>Order Confirmation</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>Thank you for your order! We've received your order and are processing it.</p>
                
                <div class='order-details'>
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> " . $orderData['orderId'] . "</p>
                    <p><strong>Order Date:</strong> " . date('F j, Y g:i A', strtotime($orderData['date'])) . "</p>
                    <p><strong>Status:</strong> " . ucfirst($orderData['status']) . "</p>
                    <p><strong>Payment Status:</strong> " . ucfirst($orderData['paymentStatus']) . "</p>
                    
                    <h4>Items Ordered:</h4>";
    
    foreach ($orderData['items'] as $item) {
        $html .= "
                    <div class='item'>
                        <span>" . htmlspecialchars($item['name']) . " x " . $item['quantity'] . "</span>
                        <span>" . $item['price'] . "</span>
                    </div>";
    }
    
    $html .= "
                    <div class='item total'>
                        <span>Total:</span>
                        <span>" . $orderData['total'] . "</span>
                    </div>
                </div>
                
                <p>You can track your order status at any time using your order ID.</p>
                <p>If you have any questions, please don't hesitate to contact us.</p>
            </div>
            <div class='footer'>
                <p>Total Wine & More<br>
                Thank you for choosing us!</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

/**
 * Generate status update email template
 */
function generateStatusUpdateEmail($orderData, $customerName) {
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .status-box { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; text-align: center; }
            .status { font-size: 24px; font-weight: bold; color: #dc3545; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Total Wine & More</h1>
                <h2>Order Status Update</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>Your order status has been updated.</p>
                
                <div class='status-box'>
                    <p><strong>Order ID:</strong> " . $orderData['orderId'] . "</p>
                    <p class='status'>" . ucfirst($orderData['status']) . "</p>
                    <p>Updated: " . date('F j, Y g:i A', strtotime($orderData['updatedAt'])) . "</p>
                </div>
                
                <p>You can track your order status at any time using your order ID.</p>
            </div>
            <div class='footer'>
                <p>Total Wine & More<br>
                Thank you for choosing us!</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

/**
 * Generate payment update email template
 */
function generatePaymentUpdateEmail($orderData, $customerName) {
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .payment-box { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; text-align: center; }
            .payment-status { font-size: 24px; font-weight: bold; color: #dc3545; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Total Wine & More</h1>
                <h2>Payment Status Update</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($customerName) . ",</p>
                <p>Your payment status has been updated.</p>
                
                <div class='payment-box'>
                    <p><strong>Order ID:</strong> " . $orderData['orderId'] . "</p>
                    <p><strong>Amount:</strong> " . $orderData['total'] . "</p>
                    <p class='payment-status'>" . ucfirst($orderData['paymentStatus']) . "</p>
                    <p>Updated: " . date('F j, Y g:i A', strtotime($orderData['updatedAt'])) . "</p>
                </div>
                
                <p>You can track your order status at any time using your order ID.</p>
            </div>
            <div class='footer'>
                <p>Total Wine & More<br>
                Thank you for choosing us!</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>
