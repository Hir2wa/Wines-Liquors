<?php
/**
 * Verify Payment Code Endpoint (Admin)
 * POST /api/admin/verify-payment-code
 */

// Check if user is admin (you should implement proper admin authentication)
// For now, we'll assume this endpoint is protected by admin middleware

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['orderId']) || !isset($input['paymentCode']) || !isset($input['verifiedBy'])) {
    sendError('Order ID, payment code, and verified by are required', 400);
}

$orderId = $input['orderId'];
$paymentCode = $input['paymentCode'];
$verifiedBy = $input['verifiedBy'];

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
    
    // Verify payment code
    $result = $order->verifyPaymentCode($orderId, $paymentCode, $verifiedBy);
    
    if ($result['success']) {
        sendSuccess($result, 'Payment verified successfully');
    } else {
        sendError($result['message'], 400);
    }
    
} catch (Exception $e) {
    sendError('Failed to verify payment: ' . $e->getMessage(), 500);
}
?>

