<?php
/**
 * Get Orders Endpoint
 * GET /api/orders?page=1&limit=10&status=pending&paymentStatus=approved
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
