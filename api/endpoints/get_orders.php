<?php
/**
 * Get Orders Endpoint for Admin Dashboard
 * GET /api/orders
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
require_once __DIR__ . '/../../models/Order.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

try {
    $order = new Order();
    
    // Get all orders with proper formatting
    $result = $order->getAll(1, 100); // Get first 100 orders
    
    // Format orders for admin dashboard
    $formattedOrders = [];
    foreach ($result['orders'] as $orderData) {
        $formattedOrder = [
            'order_id' => $orderData['id'],
            'customer_name' => $orderData['customer_first_name'] . ' ' . $orderData['customer_last_name'],
            'customer_email' => $orderData['customer_email'],
            'customer_phone' => $orderData['customer_phone'],
            'customer_location' => $orderData['customer_location'],
            'total' => number_format($orderData['total_amount']) . 'frw',
            'total_amount' => $orderData['total_amount'],
            'status' => $orderData['status'],
            'payment_status' => $orderData['payment_status'],
            'payment_method' => $orderData['payment_method'],
            'created_at' => $orderData['created_at'],
            'updated_at' => $orderData['updated_at'],
            'item_count' => $orderData['item_count'] ?? 0,
            'coordinates' => [
                'latitude' => $orderData['customer_latitude'] ?? null,
                'longitude' => $orderData['customer_longitude'] ?? null
            ]
        ];
        
        $formattedOrders[] = $formattedOrder;
    }
    
    $response = [
        'orders' => $formattedOrders,
        'total' => $result['total'],
        'page' => $result['page'],
        'limit' => $result['limit'],
        'total_pages' => $result['total_pages']
    ];
    
    sendSuccess($response, 'Orders retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve orders: ' . $e->getMessage(), 500);
}
?>