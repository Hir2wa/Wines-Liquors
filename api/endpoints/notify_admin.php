<?php
/**
 * Notify Admin of New Order
 * POST /api/admin/notify
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed', 405);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['orderId'])) {
        sendError('Order ID is required', 400);
        exit;
    }
    
    $orderId = $input['orderId'];
    
    // Get order details
    $order = new Order();
    $orderDetails = $order->getById($orderId);
    
    if (!$orderDetails) {
        sendError('Order not found', 404);
        exit;
    }
    
    // Get admin users
    $database = new Database();
    $conn = $database->getConnection();
    
    $stmt = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE is_admin = true AND is_active = true");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        sendError('No admin users found', 404);
        exit;
    }
    
    // Prepare notification data
    $notificationData = [
        'orderId' => $orderDetails['id'],
        'customerName' => $orderDetails['customer_first_name'] . ' ' . $orderDetails['customer_last_name'],
        'customerEmail' => $orderDetails['customer_email'],
        'customerPhone' => $orderDetails['customer_phone'],
        'customerLocation' => $orderDetails['customer_address'],
        'totalAmount' => $orderDetails['total_amount'],
        'paymentMethod' => $orderDetails['payment_method'],
        'items' => $orderDetails['items_data'],
        'createdAt' => $orderDetails['created_at'],
        'adminEmails' => array_column($admins, 'email')
    ];
    
    // Store notification in database for admin dashboard
    $stmt = $conn->prepare("
        INSERT INTO admin_notifications 
        (order_id, notification_type, title, message, data, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, false, NOW())
    ");
    
    $title = "New Order #" . $orderId;
    $message = "New order from " . $notificationData['customerName'] . " for " . number_format($notificationData['totalAmount']) . " RWF";
    $data = json_encode($notificationData);
    
    $stmt->execute([
        $orderId,
        'new_order',
        $title,
        $message,
        $data
    ]);
    
    sendSuccess($notificationData, 'Admin notification sent successfully');
    
} catch (Exception $e) {
    sendError('Failed to notify admin: ' . $e->getMessage(), 500);
}
?>
