<?php
/**
 * Payment Code Endpoint
 * Handles /api/orders/payment-code requests
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

// Helper functions are already defined in database.php

// Get order ID from query parameter
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