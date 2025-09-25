<?php
/**
 * Get Orders Endpoint
 * GET /api/orders?page=1&limit=10&status=pending&paymentStatus=approved
 */

// Get query parameters
$page = intval($_GET['page'] ?? 1);
$limit = intval($_GET['limit'] ?? 10);
$status = $_GET['status'] ?? null;
$paymentStatus = $_GET['paymentStatus'] ?? null;

// Validate parameters
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 100) $limit = 10;

// Validate status values
$validStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
$validPaymentStatuses = ['pending', 'approved', 'rejected'];

if ($status && !in_array($status, $validStatuses)) {
    sendError('Invalid status value', 400);
}

if ($paymentStatus && !in_array($paymentStatus, $validPaymentStatuses)) {
    sendError('Invalid payment status value', 400);
}

try {
    $order = new Order();
    $result = $order->getAll($page, $limit, $status, $paymentStatus);
    
    sendSuccess($result, 'Orders retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve orders: ' . $e->getMessage(), 500);
}
?>
