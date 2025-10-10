<?php
// get_sales_report.php
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
    sendError('Method not allowed', 405);
    exit;
}

try {
    $order = new Order();
    
    // Get sales report data
    $salesData = $order->getSalesReport();
    
    sendSuccess('Sales report data retrieved successfully', $salesData);
    
} catch (Exception $e) {
    sendError('Failed to retrieve sales report: ' . $e->getMessage(), 500);
}
?>

