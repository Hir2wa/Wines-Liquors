<?php
/**
 * Test Payment Code Endpoint
 * Simple endpoint to test payment code retrieval
 */

// Include required files
require_once 'config/database.php';
require_once 'models/Order.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get order ID from query parameter
$orderId = $_GET['orderId'] ?? null;

if (!$orderId) {
    echo json_encode(['status' => 400, 'message' => 'Order ID is required']);
    exit;
}

// Validate order ID format
if (!preg_match('/^ORD-\d{9}$/', $orderId)) {
    echo json_encode(['status' => 400, 'message' => 'Invalid order ID format']);
    exit;
}

try {
    $order = new Order();
    
    // Check if order exists
    $existingOrder = $order->getById($orderId);
    if (!$existingOrder) {
        echo json_encode(['status' => 404, 'message' => 'Order not found']);
        exit;
    }
    
    // Get payment code
    $paymentCodeResult = $order->getPaymentCode($orderId);
    
    if ($paymentCodeResult['success']) {
        echo json_encode([
            'status' => 200,
            'message' => 'Payment code retrieved successfully',
            'data' => $paymentCodeResult
        ]);
    } else {
        echo json_encode([
            'status' => 404,
            'message' => $paymentCodeResult['message']
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 500,
        'message' => 'Failed to get payment code: ' . $e->getMessage()
    ]);
}
?>




