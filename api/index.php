<?php
/**
 * API Router
 * Total Wine & More - Order Management API
 */

// Suppress PHP warnings/notices to prevent breaking JSON response
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../models/Order.php';
require_once '../models/User.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/', '', $path);

// Remove query string from path
$path = strtok($path, '?');

// Function to match dynamic routes
function matchRoute($pattern, $path) {
    $pattern = str_replace('{orderId}', '([^/]+)', $pattern);
    return preg_match('#^' . $pattern . '$#', $path);
}

// Function to extract order ID from path
function extractOrderId($path) {
    if (preg_match('#^orders/([^/]+)/payment-code$#', $path, $matches)) {
        return $matches[1];
    }
    return null;
}

try {
    // Debug: Log the path for troubleshooting
    error_log("API Debug - Path: '$path', Method: '$method'");
    
    // Handle dynamic routes first - check for orders/{orderId}/payment-code pattern
    if (preg_match('#^orders/([^/]+)/payment-code$#', $path, $matches) && $method === 'GET') {
        error_log("API Debug - Matched dynamic route for payment code");
        $orderId = $matches[1];
        require_once 'endpoints/get_payment_code.php';
    } elseif ($path === 'orders' && $method === 'POST') {
        require_once 'endpoints/create_order.php';
    } elseif ($path === 'orders' && $method === 'GET') {
        require_once 'endpoints/get_orders.php';
    } elseif ($path === 'orders/track' && $method === 'GET') {
        require_once 'endpoints/track_order.php';
    } elseif ($path === 'orders/update-status' && $method === 'PUT') {
        require_once 'endpoints/update_order_status.php';
    } elseif ($path === 'orders/update-payment' && $method === 'PUT') {
        require_once 'endpoints/update_payment_status.php';
    } elseif ($path === 'orders/customer' && $method === 'GET') {
        require_once 'endpoints/get_customer_orders.php';
    } elseif ($path === 'orders/notify' && $method === 'POST') {
        require_once 'endpoints/send_order_notification.php';
    } elseif ($path === 'orders/payment-code' && $method === 'GET') {
        require_once 'endpoints/get_payment_code.php';
    } elseif ($path === 'orders/verify-payment' && $method === 'POST') {
        require_once 'endpoints/verify_payment_code.php';
    } elseif ($path === 'orders/pending-payments' && $method === 'GET') {
        require_once 'endpoints/get_pending_payments.php';
    } elseif ($path === 'admin/pending-payments' && $method === 'GET') {
        require_once 'endpoints/get_pending_payments.php';
    } elseif ($path === 'admin/dashboard' && $method === 'GET') {
        require_once 'endpoints/admin_dashboard.php';
    } elseif ($path === 'admin/login' && $method === 'POST') {
        require_once 'endpoints/admin_login.php';
    } elseif ($path === 'admin/verify-payment-code' && $method === 'POST') {
        require_once 'endpoints/verify_payment_code.php';
    } elseif ($path === 'auth/register' && $method === 'POST') {
        require_once 'endpoints/register.php';
    } elseif ($path === 'auth/login' && $method === 'POST') {
        require_once 'endpoints/user_login.php';
    } elseif ($path === 'auth/verify' && $method === 'POST') {
        require_once 'endpoints/verify_code.php';
    } elseif ($path === 'auth/resend-verification' && $method === 'POST') {
        require_once 'endpoints/resend_verification.php';
    } elseif ($path === 'profile' && $method === 'GET') {
        require_once 'endpoints/get_profile.php';
    } elseif ($path === 'profile' && $method === 'PUT') {
        require_once 'endpoints/update_profile.php';
    } elseif ($path === 'health') {
        sendSuccess(['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')], 'API is running');
    } elseif ($path === 'debug') {
        sendSuccess([
            'path' => $path,
            'method' => $method,
            'full_uri' => $_SERVER['REQUEST_URI'],
            'parsed_path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        ], 'Debug info');
    } else {
        sendError('Endpoint not found: ' . $path, 404);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
?>
