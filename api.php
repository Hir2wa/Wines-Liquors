<?php
/**
 * API Router for PHP Development Server
 * Handles all API requests
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

// Get the request URI and method
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Handle both /api/ and direct requests
if (strpos($uri, '/api/') === 0) {
    $path = str_replace('/api/', '', $uri);
} else {
    $path = $uri;
}
$path = strtok($path, '?'); // Remove query string

// Include required files
require_once 'config/database.php';
require_once 'models/Order.php';
require_once 'models/User.php';

// Helper functions are already defined in database.php

try {
    // Route requests
    if ($path === 'health') {
        sendSuccess(['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')], 'API is running');
    } elseif ($path === 'orders' && $method === 'POST') {
        require_once 'api/endpoints/create_order.php';
    } elseif ($path === 'orders' && $method === 'GET') {
        require_once 'api/endpoints/get_orders.php';
    } elseif ($path === 'orders/track' && $method === 'GET') {
        require_once 'api/endpoints/track_order.php';
    } elseif ($path === 'orders/update-status' && $method === 'PUT') {
        require_once 'api/endpoints/update_order_status.php';
    } elseif ($path === 'orders/update-payment' && $method === 'PUT') {
        require_once 'api/endpoints/update_payment_status.php';
    } elseif ($path === 'orders/customer' && $method === 'GET') {
        require_once 'api/endpoints/get_customer_orders.php';
    } elseif ($path === 'orders/notify' && $method === 'POST') {
        require_once 'api/endpoints/send_order_notification.php';
    } elseif ($path === 'orders/payment-code' && $method === 'GET') {
        require_once 'api/endpoints/get_payment_code.php';
    } elseif ($path === 'orders/verify-payment' && $method === 'POST') {
        require_once 'api/endpoints/verify_payment_code.php';
    } elseif ($path === 'orders/pending-payments' && $method === 'GET') {
        require_once 'api/endpoints/get_pending_payments.php';
    } elseif ($path === 'admin/pending-payments' && $method === 'GET') {
        require_once 'api/endpoints/get_pending_payments.php';
    } elseif ($path === 'admin/dashboard' && $method === 'GET') {
        require_once 'api/endpoints/admin_dashboard.php';
    } elseif ($path === 'admin/login' && $method === 'POST') {
        require_once 'api/endpoints/admin_login.php';
    } elseif ($path === 'admin/verify-payment-code' && $method === 'POST') {
        require_once 'api/endpoints/verify_payment_code.php';
    } elseif ($path === 'auth/register' && $method === 'POST') {
        require_once 'api/endpoints/register.php';
    } elseif ($path === 'auth/login' && $method === 'POST') {
        require_once 'api/endpoints/user_login.php';
    } elseif ($path === 'auth/verify' && $method === 'POST') {
        require_once 'api/endpoints/verify_code.php';
    } elseif ($path === 'auth/resend-verification' && $method === 'POST') {
        require_once 'api/endpoints/resend_verification.php';
    } elseif ($path === 'auth/forgot-password' && $method === 'POST') {
        require_once 'api/endpoints/forgot_password.php';
    } elseif ($path === 'auth/verify-reset-code' && $method === 'POST') {
        require_once 'api/endpoints/verify_reset_code.php';
    } elseif ($path === 'auth/reset-password' && $method === 'POST') {
        require_once 'api/endpoints/reset_password.php';
    } elseif ($path === 'profile' && $method === 'GET') {
        require_once 'api/endpoints/get_profile.php';
    } elseif ($path === 'profile' && $method === 'PUT') {
        require_once 'api/endpoints/update_profile.php';
    } else {
        sendError('Endpoint not found: ' . $path, 404);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
?>
