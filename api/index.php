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

try {
    switch ($path) {
        case 'orders':
            if ($method === 'POST') {
                require_once 'endpoints/create_order.php';
            } elseif ($method === 'GET') {
                require_once 'endpoints/get_orders.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'orders/track':
            if ($method === 'GET') {
                require_once 'endpoints/track_order.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'orders/update-status':
            if ($method === 'PUT') {
                require_once 'endpoints/update_order_status.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'orders/update-payment':
            if ($method === 'PUT') {
                require_once 'endpoints/update_payment_status.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'orders/customer':
            if ($method === 'GET') {
                require_once 'endpoints/get_customer_orders.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'orders/notify':
            if ($method === 'POST') {
                require_once 'endpoints/send_order_notification.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'admin/dashboard':
            if ($method === 'GET') {
                require_once 'endpoints/admin_dashboard.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'admin/login':
            if ($method === 'POST') {
                require_once 'endpoints/admin_login.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'auth/register':
            if ($method === 'POST') {
                require_once 'endpoints/register.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'auth/login':
            if ($method === 'POST') {
                require_once 'endpoints/user_login.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'auth/verify':
            if ($method === 'POST') {
                require_once 'endpoints/verify_code.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'auth/resend-verification':
            if ($method === 'POST') {
                require_once 'endpoints/resend_verification.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'profile':
            if ($method === 'GET') {
                require_once 'endpoints/get_profile.php';
            } elseif ($method === 'PUT') {
                require_once 'endpoints/update_profile.php';
            } else {
                sendError('Method not allowed', 405);
            }
            break;

        case 'health':
            sendSuccess(['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')], 'API is running');
            break;

        default:
            sendError('Endpoint not found', 404);
            break;
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
?>
