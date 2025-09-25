<?php
/**
 * Update Payment Status Endpoint
 * PUT /api/orders/update-payment
 */

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['orderId']) || !isset($input['paymentStatus'])) {
    sendError('Order ID and payment status are required', 400);
}

$orderId = $input['orderId'];
$newPaymentStatus = $input['paymentStatus'];
$changedBy = $input['changedBy'] ?? 'system';
$reason = $input['reason'] ?? '';

// Validate order ID format
if (!preg_match('/^ORD-\d{9}$/', $orderId)) {
    sendError('Invalid order ID format', 400);
}

// Validate payment status
$validPaymentStatuses = ['pending', 'approved', 'rejected'];
if (!in_array($newPaymentStatus, $validPaymentStatuses)) {
    sendError('Invalid payment status value', 400);
}

try {
    $order = new Order();
    
    // Check if order exists
    $existingOrder = $order->getById($orderId);
    if (!$existingOrder) {
        sendError('Order not found', 404);
    }
    
    // Update payment status
    $result = $order->updatePaymentStatus($orderId, $newPaymentStatus, $changedBy, $reason);
    
    if ($result) {
        // Get updated order
        $updatedOrder = $order->getById($orderId);
        sendSuccess($updatedOrder, 'Payment status updated successfully');
    } else {
        sendError('Failed to update payment status', 500);
    }
    
} catch (Exception $e) {
    sendError('Failed to update payment status: ' . $e->getMessage(), 500);
}
?>
