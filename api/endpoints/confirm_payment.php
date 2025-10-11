<?php
/**
 * Confirm Payment Endpoint
 * POST /api/orders/confirm-payment
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

try {
    // Get input data
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($input['orderId'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Order ID is required']);
        exit;
    }
    
    $orderId = $input['orderId'];
    $paymentMethod = $input['paymentMethod'] ?? 'mobile_money';
    $paymentProof = $input['paymentProof'] ?? '';
    $notes = $input['notes'] ?? 'Payment confirmed by admin';
    
    $order = new Order();
    
    // Check if order exists
    $existingOrder = $order->getById($orderId);
    if (!$existingOrder) {
        sendError('Order not found', 404);
    }
    
    // Update payment status to approved
    $result = $order->updatePaymentStatus($orderId, 'approved', 'admin', $notes);
    
    if ($result) {
        // Get the order to check payment method
        $orderData = $order->getById($orderId);
        
        // Only move to on_route if it's not cash on delivery
        if ($orderData && $orderData['paymentMethod'] !== 'cash_on_delivery') {
            $order->updateStatus($orderId, 'on_route', 'admin', 'Payment confirmed, order is now on route');
            $message = 'Payment confirmed and order is now on route';
        } else {
            $message = 'Payment confirmed (cash on delivery - order ready for pickup)';
        }
        
        // Get updated order
        $updatedOrder = $order->getById($orderId);
        
        sendSuccess($updatedOrder, $message);
    } else {
        sendError('Failed to confirm payment', 500);
    }
    
} catch (Exception $e) {
    sendError('Failed to confirm payment: ' . $e->getMessage(), 500);
}
?>
