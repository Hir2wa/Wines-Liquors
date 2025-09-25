<?php
/**
 * Track Order Endpoint
 * GET /api/orders/track?orderId=ORD-123456
 */

// Get order ID from query parameters
$orderId = $_GET['orderId'] ?? null;

if (!$orderId) {
    sendError('Order ID is required', 400);
}

// Validate order ID format
if (!preg_match('/^ORD-\d{9}$/', $orderId)) {
    sendError('Invalid order ID format', 400);
}

try {
    $order = new Order();
    $orderData = $order->getById($orderId);
    
    if (!$orderData) {
        sendError('Order not found', 404);
    }
    
    sendSuccess($orderData, 'Order found');
    
} catch (Exception $e) {
    sendError('Failed to retrieve order: ' . $e->getMessage(), 500);
}
?>

