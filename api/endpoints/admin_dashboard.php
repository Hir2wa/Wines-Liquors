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
    $recentOrders = $order->getAll(1, 10);
    
    // Get pending payment orders
    $pendingPayments = $order->getAll(1, 20, null, 'pending');
    
    $dashboardData = [
        'stats' => $stats,
        'recentOrders' => $recentOrders['orders'] ?? [],
        'pendingPayments' => $pendingPayments['orders'] ?? []
    ];
    
    sendSuccess($dashboardData, 'Dashboard data retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve dashboard data: ' . $e->getMessage(), 500);
}
?>
