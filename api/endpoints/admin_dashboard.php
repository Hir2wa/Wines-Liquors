<?php
/**
 * Admin Dashboard Endpoint
 * GET /api/admin/dashboard
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

// Helper functions are already defined in database.php

try {
    $order = new Order();
    
    // Get dashboard statistics
    $stats = $order->getDashboardStats();
    
    // Get recent orders (last 10)
    $recentOrdersResult = $order->getAll(1, 10);
    $recentOrders = [];
    foreach ($recentOrdersResult['orders'] ?? [] as $orderData) {
        $recentOrders[] = [
            'id' => $orderData['id'],
            'customer_first_name' => $orderData['customer_first_name'],
            'customer_last_name' => $orderData['customer_last_name'],
            'customer_email' => $orderData['customer_email'],
            'total_amount' => $orderData['total_amount'],
            'status' => $orderData['status'],
            'created_at' => $orderData['created_at'],
            'item_count' => $orderData['item_count'] ?? 0
        ];
    }
    
    // Get pending payment orders
    $pendingPaymentsResult = $order->getAll(1, 20, null, 'pending');
    $pendingPayments = [];
    foreach ($pendingPaymentsResult['orders'] ?? [] as $orderData) {
        $pendingPayments[] = [
            'id' => $orderData['id'],
            'customer_first_name' => $orderData['customer_first_name'],
            'customer_last_name' => $orderData['customer_last_name'],
            'customer_email' => $orderData['customer_email'],
            'total_amount' => $orderData['total_amount'],
            'status' => $orderData['status'],
            'payment_status' => $orderData['payment_status'],
            'created_at' => $orderData['created_at'],
            'item_count' => $orderData['item_count'] ?? 0
        ];
    }
    
    $dashboardData = [
        'stats' => $stats,
        'recentOrders' => $recentOrders,
        'pendingPayments' => $pendingPayments
    ];
    
    sendSuccess($dashboardData, 'Dashboard data retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve dashboard data: ' . $e->getMessage(), 500);
}
?>
