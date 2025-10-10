<?php
/**
 * Get User Orders
 * GET /api/user/orders
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Order.php';

try {
    // Get user email from query parameter or session
    $userEmail = $_GET['email'] ?? null;
    
    if (!$userEmail) {
        sendError('User email is required', 400);
        exit;
    }
    
    $order = new Order();
    $orders = $order->getByCustomerEmail($userEmail);
    
    // Format orders for frontend
    $formattedOrders = [];
    foreach ($orders as $orderData) {
        $formattedOrders[] = [
            'id' => $orderData['id'],
            'customerName' => $orderData['customer_first_name'] . ' ' . $orderData['customer_last_name'],
            'customerEmail' => $orderData['customer_email'],
            'customerPhone' => $orderData['customer_phone'],
            'customerLocation' => $orderData['customer_address'],
            'totalAmount' => $orderData['total_amount'],
            'paymentMethod' => $orderData['payment_method'],
            'status' => $orderData['status'],
            'paymentStatus' => $orderData['payment_status'],
            'items' => $orderData['items_data'],
            'createdAt' => $orderData['created_at'],
            'updatedAt' => $orderData['updated_at']
        ];
    }
    
    sendSuccess($formattedOrders, 'User orders retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve user orders: ' . $e->getMessage(), 500);
}
?>
