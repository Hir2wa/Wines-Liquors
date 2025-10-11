<?php
/**
 * Simple PDF Generation Endpoint (No Image Processing Required)
 * POST /api/admin/generate-simple-pdf
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Order.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['type'])) {
        sendError('PDF type is required', 400);
    }
    
    $pdfType = $data['type'];
    
    switch ($pdfType) {
        case 'comprehensive_report':
            generateSimpleComprehensiveReport();
            break;
        case 'orders_report':
            generateSimpleOrdersReport();
            break;
        default:
            sendError('Invalid PDF type', 400);
    }
    
} catch (Exception $e) {
    sendError('Failed to generate PDF: ' . $e->getMessage(), 500);
}

function generateSimpleComprehensiveReport() {
    $order = new Order();
    
    // Get dashboard stats
    $stats = $order->getDashboardStats();
    
    // Get recent orders
    $recentOrders = $order->getAll(1, 10);
    
    // Get all orders for the report
    $allOrders = $order->getAll(1, 1000);
    
    // Create HTML report
    $html = createReportHTML($stats, $recentOrders, $allOrders);
    
    // Set headers for HTML download
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="comprehensive_report_' . date('Y-m-d') . '.html"');
    
    echo $html;
}

function generateSimpleOrdersReport() {
    $order = new Order();
    $allOrders = $order->getAll(1, 1000);
    
    $html = createOrdersReportHTML($allOrders);
    
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="orders_report_' . date('Y-m-d') . '.html"');
    
    echo $html;
}

function createReportHTML($stats, $recentOrders, $allOrders) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NELVINTO Liquors Store - Comprehensive Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #dc3545; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #dc3545; margin: 0; }
        .header p { color: #666; margin: 5px 0; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545; }
        .stat-card h3 { margin: 0 0 10px 0; color: #dc3545; }
        .stat-card .number { font-size: 24px; font-weight: bold; color: #333; }
        .section { margin: 30px 0; }
        .section h2 { color: #dc3545; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .status-pending { color: #ffc107; }
        .status-delivered { color: #28a745; }
        .status-on_route { color: #17a2b8; }
        .status-cancelled { color: #dc3545; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NELVINTO Liquors Store</h1>
        <p>Comprehensive Business Report</p>
        <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
    </div>
    
    <div class="section">
        <h2>Business Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number">' . number_format($stats['total_orders']) . '</div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="number">' . number_format($stats['total_revenue']) . 'frw</div>
            </div>
            <div class="stat-card">
                <h3>Unique Customers</h3>
                <div class="number">' . number_format($stats['unique_customers']) . '</div>
            </div>
            <div class="stat-card">
                <h3>Pending Payments</h3>
                <div class="number">' . number_format($stats['pending_payments']) . '</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2>Recent Orders (' . count($recentOrders['orders']) . ' orders)</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($recentOrders['orders'] as $order) {
        $html .= '<tr>
            <td>' . $order['id'] . '</td>
            <td>' . $order['customer_first_name'] . ' ' . $order['customer_last_name'] . '</td>
            <td>' . number_format($order['total_amount']) . 'frw</td>
            <td class="status-' . $order['status'] . '">' . ucfirst($order['status']) . '</td>
            <td>' . ucfirst($order['payment_status']) . '</td>
            <td>' . date('M j, Y', strtotime($order['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>All Orders Summary (' . count($allOrders['orders']) . ' total orders)</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Location</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($allOrders['orders'] as $order) {
        $html .= '<tr>
            <td>' . $order['id'] . '</td>
            <td>' . $order['customer_first_name'] . ' ' . $order['customer_last_name'] . '</td>
            <td>' . $order['customer_email'] . '</td>
            <td>' . $order['customer_phone'] . '</td>
            <td>' . $order['customer_location'] . '</td>
            <td>' . number_format($order['total_amount']) . 'frw</td>
            <td class="status-' . $order['status'] . '">' . ucfirst($order['status']) . '</td>
            <td>' . ucfirst(str_replace('_', ' ', $order['payment_method'])) . '</td>
            <td>' . date('M j, Y', strtotime($order['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>This report was generated automatically by the NELVINTO Liquors Store Management System</p>
        <p>For questions or support, please contact the system administrator</p>
    </div>
</body>
</html>';
    
    return $html;
}

function createOrdersReportHTML($allOrders) {
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NELVINTO Liquors Store - Orders Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #dc3545; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #dc3545; margin: 0; }
        .header p { color: #666; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .status-pending { color: #ffc107; }
        .status-delivered { color: #28a745; }
        .status-on_route { color: #17a2b8; }
        .status-cancelled { color: #dc3545; }
        .footer { margin-top: 50px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NELVINTO Liquors Store</h1>
        <p>Orders Report</p>
        <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Payment Method</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($allOrders['orders'] as $order) {
        $html .= '<tr>
            <td>' . $order['id'] . '</td>
            <td>' . $order['customer_first_name'] . ' ' . $order['customer_last_name'] . '</td>
            <td>' . $order['customer_email'] . '</td>
            <td>' . $order['customer_phone'] . '</td>
            <td>' . $order['customer_location'] . '</td>
            <td>' . number_format($order['total_amount']) . 'frw</td>
            <td class="status-' . $order['status'] . '">' . ucfirst($order['status']) . '</td>
            <td>' . ucfirst($order['payment_status']) . '</td>
            <td>' . ucfirst(str_replace('_', ' ', $order['payment_method'])) . '</td>
            <td>' . date('M j, Y', strtotime($order['created_at'])) . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Total Orders: ' . count($allOrders['orders']) . '</p>
        <p>This report was generated automatically by the NELVINTO Liquors Store Management System</p>
    </div>
</body>
</html>';
    
    return $html;
}
?>

