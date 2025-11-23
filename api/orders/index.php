<?php
/**
 * Orders API endpoint
 * Handles both GET and POST requests to /api/orders/
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

// Route based on HTTP method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create order
    require_once __DIR__ . '/../endpoints/create_order.php';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get orders
    require_once __DIR__ . '/../endpoints/get_orders.php';
} else {
    sendError('Method not allowed', 405);
}
?>



