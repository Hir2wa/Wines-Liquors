<?php
/**
 * Get Order by ID Endpoint
 * GET /api/orders/{orderId}
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
    
    // Get order ID from URL path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    $orderId = end($pathParts);
    
    if (!$orderId) {
        sendError('Order ID is required', 400);
    }
    
    // Get order details
    $orderData = $order->getById($orderId);
    
    if (!$orderData) {
        sendError('Order not found', 404);
    }
    
    // Format the order data for the frontend using the correct field names from formatOrderData
    $formattedOrder = [
        'order_id' => $orderData['orderId'],
        'customer_name' => $orderData['customerInfo']['firstName'] . ' ' . $orderData['customerInfo']['lastName'],
        'customer_email' => $orderData['customerInfo']['email'],
        'customer_phone' => $orderData['customerInfo']['phone'],
        'customer_location' => $orderData['customerInfo']['location'],
        'total' => $orderData['total'],
        'total_amount' => $orderData['totalAmount'],
        'status' => $orderData['status'],
        'payment_status' => $orderData['paymentStatus'],
        'payment_method' => $orderData['paymentMethod'],
        'created_at' => $orderData['date'],
        'updated_at' => $orderData['updatedAt'],
        'item_count' => count($orderData['items'] ?? []),
        'items' => $orderData['items'] ?? [],
        'coordinates' => $orderData['coordinates'] ?? null
    ];
    
    sendSuccess($formattedOrder, 'Order retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve order: ' . $e->getMessage(), 500);
}
?>
