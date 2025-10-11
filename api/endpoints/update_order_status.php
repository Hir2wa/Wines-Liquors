<?php
/**
 * Update Order Status Endpoint
 * PUT /api/orders/update-status
 */

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['orderId']) || !isset($input['status'])) {
    sendError('Order ID and status are required', 400);
}

$orderId = $input['orderId'];
$newStatus = $input['status'];
$changedBy = $input['changedBy'] ?? 'system';
$reason = $input['reason'] ?? '';

// Validate order ID format
if (!preg_match('/^ORD-\d{9}$/', $orderId)) {
    sendError('Invalid order ID format', 400);
}

// Validate status
$validStatuses = ['pending', 'on_route', 'shipped', 'delivered', 'cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    sendError('Invalid status value', 400);
}

try {
    $order = new Order();
    
    // Check if order exists
    $existingOrder = $order->getById($orderId);
    if (!$existingOrder) {
        sendError('Order not found', 404);
    }
    
    // Update status
    $result = $order->updateStatus($orderId, $newStatus, $changedBy, $reason);
    
    if ($result) {
        // Get updated order
        $updatedOrder = $order->getById($orderId);
        sendSuccess($updatedOrder, 'Order status updated successfully');
    } else {
        sendError('Failed to update order status', 500);
    }
    
} catch (Exception $e) {
    sendError('Failed to update order status: ' . $e->getMessage(), 500);
}
?>
