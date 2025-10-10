<?php
/**
 * Get Pending Payments Endpoint (Admin)
 * GET /api/admin/pending-payments
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

// Check if user is admin (you should implement proper admin authentication)
// For now, we'll assume this endpoint is protected by admin middleware

try {
    $order = new Order();
    
    // Get all pending payment codes
    $pendingPayments = $order->getPendingPaymentCodes();
    
    sendSuccess($pendingPayments, 'Pending payments retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to get pending payments: ' . $e->getMessage(), 500);
}
?>

