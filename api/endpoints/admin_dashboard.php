<?php
/**
 * Admin Dashboard Endpoint
 * GET /api/admin/dashboard
 */

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
        'recentOrders' => $recentOrders['orders'],
        'pendingPayments' => $pendingPayments['orders']
    ];
    
    sendSuccess($dashboardData, 'Dashboard data retrieved successfully');
    
} catch (Exception $e) {
    sendError('Failed to retrieve dashboard data: ' . $e->getMessage(), 500);
}
?>
