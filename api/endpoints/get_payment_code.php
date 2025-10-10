<?php
/**
 * Get Payment Code Endpoint
 * GET /api/orders/{orderId}/payment-code
 */

// Include required files
require_once '../../config/database.php';
require_once '../../models/Order.php';

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

// Helper functions
function sendSuccess($data, $message = 'Success') {
    echo json_encode([
        'status' => 200,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'status' => $code,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get order ID from query parameter or router variable
$orderId = $orderId ?? $_GET['orderId'] ?? null;

if (!$orderId) {
    sendError('Order ID is required', 400);
}

// Validate order ID format
if (!preg_match('/^ORD-\d{9}$/', $orderId)) {
    sendError('Invalid order ID format', 400);
}

try {
    $order = new Order();
    
    // Check if order exists
    $existingOrder = $order->getById($orderId);
    if (!$existingOrder) {
        sendError('Order not found', 404);
    }
    
    // Get payment code
    $paymentCodeResult = $order->getPaymentCode($orderId);
    
    if ($paymentCodeResult['success']) {
        sendSuccess($paymentCodeResult, 'Payment code retrieved successfully');
    } else {
        sendError($paymentCodeResult['message'], 404);
    }
    
} catch (Exception $e) {
    sendError('Failed to get payment code: ' . $e->getMessage(), 500);
}
?>
