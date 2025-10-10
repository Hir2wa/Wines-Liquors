<?php
/**
 * Simple Router for PHP Development Server
 * Handles all API requests
 */

// Get the request URI
$uri = $_SERVER['REQUEST_URI'];

// Handle API requests
if (strpos($uri, '/api/') === 0) {
    // Remove /api/ prefix
    $path = substr($uri, 5);
    
    // Remove query string
    $path = strtok($path, '?');
    
    // Route to appropriate endpoint
    if ($path === 'health') {
        require_once 'api/endpoints/health.php';
    } elseif ($path === 'orders' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/create_order.php';
    } elseif ($path === 'orders' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once 'api/endpoints/get_orders.php';
    } elseif (preg_match('#^orders/([^/]+)/payment-code$#', $path, $matches) && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // Set the order ID and include the endpoint
        $orderId = $matches[1];
        require_once 'api/endpoints/get_payment_code.php';
    } elseif ($path === 'admin/pending-payments' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once 'api/endpoints/get_pending_payments.php';
    } elseif ($path === 'admin/verify-payment-code' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/verify_payment_code.php';
    } elseif ($path === 'admin/dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once 'api/endpoints/admin_dashboard.php';
} elseif ($path === 'admin/generate-pdf' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'api/endpoints/generate_pdf.php';
} elseif ($path === 'admin/sales-report' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once 'api/endpoints/get_sales_report.php';
    } elseif ($path === 'auth/register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/register.php';
    } elseif ($path === 'auth/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/user_login.php';
    } elseif ($path === 'auth/verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/verify_code.php';
    } elseif ($path === 'auth/resend-verification' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'api/endpoints/resend_verification.php';
    } elseif ($path === 'orders/customer' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        require_once 'api/endpoints/get_customer_orders.php';
    } elseif ($path === 'debug') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 200,
            'message' => 'Debug info',
            'data' => [
                'path' => $path,
                'method' => $_SERVER['REQUEST_METHOD'],
                'full_uri' => $uri,
                'parsed_path' => $path
            ]
        ]);
    } else {
        // Default API response
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['status' => 404, 'message' => 'Endpoint not found: ' . $path]);
    }
} else {
    // Let the server handle non-API requests
    return false;
}
?>

