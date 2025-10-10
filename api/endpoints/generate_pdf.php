<?php
/**
 * Generate PDF Endpoint
 * POST /api/admin/generate-pdf
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
require_once __DIR__ . '/../../vendor/autoload.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

try {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['type'])) {
        sendError('PDF type is required', 400);
    }
    
    $pdfType = $data['type'];
    $orderId = $data['orderId'] ?? null;
    
    switch ($pdfType) {
        case 'order':
            if (!$orderId) {
                sendError('Order ID is required for order PDF', 400);
            }
            generateOrderPDF($orderId);
            break;
            
        case 'orders_report':
            generateOrdersReportPDF($data);
            break;
            
        case 'payments_report':
            generatePaymentsReportPDF($data);
            break;
            
        case 'comprehensive_report':
            generateComprehensiveReportPDF($data);
            break;
            
        default:
            sendError('Invalid PDF type', 400);
    }
    
} catch (Exception $e) {
    sendError('Failed to generate PDF: ' . $e->getMessage(), 500);
}

function generateOrderPDF($orderId) {
    $order = new Order();
    $orderData = $order->getById($orderId);
    
    if (!$orderData) {
        sendError('Order not found', 404);
    }
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Wines & Liquors Admin');
    $pdf->SetAuthor('Wines & Liquors');
    $pdf->SetTitle('Order #' . $orderId);
    $pdf->SetSubject('Order Invoice');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'Wines & Liquors', 'Order Invoice #' . $orderId);
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Order details HTML
    $html = '
    <h2>Order Details</h2>
    <table border="1" cellpadding="5">
        <tr>
            <td><strong>Order ID:</strong></td>
            <td>#' . $orderData['id'] . '</td>
        </tr>
        <tr>
            <td><strong>Customer Name:</strong></td>
            <td>' . $orderData['customer_first_name'] . ' ' . $orderData['customer_last_name'] . '</td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td>' . $orderData['customer_email'] . '</td>
        </tr>
        <tr>
            <td><strong>Phone:</strong></td>
            <td>' . $orderData['customer_phone'] . '</td>
        </tr>
        <tr>
            <td><strong>Address:</strong></td>
            <td>' . $orderData['customer_address'] . ', ' . $orderData['customer_city'] . ', ' . $orderData['customer_country'] . '</td>
        </tr>
        <tr>
            <td><strong>Total Amount:</strong></td>
            <td>' . number_format($orderData['total_amount']) . ' RWF</td>
        </tr>
        <tr>
            <td><strong>Payment Method:</strong></td>
            <td>' . ucfirst($orderData['payment_method']) . '</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>' . ucfirst($orderData['status']) . '</td>
        </tr>
        <tr>
            <td><strong>Payment Status:</strong></td>
            <td>' . ucfirst($orderData['payment_status']) . '</td>
        </tr>
        <tr>
            <td><strong>Order Date:</strong></td>
            <td>' . date('Y-m-d H:i:s', strtotime($orderData['created_at'])) . '</td>
        </tr>
    </table>
    ';
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('order_' . $orderId . '.pdf', 'D'); // 'D' for download
}

function generateOrdersReportPDF($data) {
    $order = new Order();
    $orders = $order->getAll(1, 1000); // Get all orders
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Wines & Liquors Admin');
    $pdf->SetAuthor('Wines & Liquors');
    $pdf->SetTitle('Orders Report');
    $pdf->SetSubject('Orders Report');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'Wines & Liquors', 'Orders Report - ' . date('Y-m-d'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Orders report HTML
    $html = '<h2>Orders Report</h2>';
    $html .= '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    
    if (empty($orders['orders'])) {
        $html .= '<p>No orders found.</p>';
    } else {
        $html .= '<table border="1" cellpadding="5">
            <tr style="background-color: #f0f0f0;">
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>';
        
        foreach ($orders['orders'] as $order) {
            $html .= '<tr>
                <td>#' . $order['id'] . '</td>
                <td>' . $order['customer_first_name'] . ' ' . $order['customer_last_name'] . '</td>
                <td>' . $order['customer_email'] . '</td>
                <td>' . number_format($order['total_amount']) . ' RWF</td>
                <td>' . ucfirst($order['status']) . '</td>
                <td>' . ucfirst($order['payment_status']) . '</td>
                <td>' . date('Y-m-d', strtotime($order['created_at'])) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
    }
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('orders_report_' . date('Y-m-d') . '.pdf', 'D');
}

function generatePaymentsReportPDF($data) {
    $order = new Order();
    $payments = $order->getAll(1, 1000, null, 'pending'); // Get pending payments
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Wines & Liquors Admin');
    $pdf->SetAuthor('Wines & Liquors');
    $pdf->SetTitle('Payments Report');
    $pdf->SetSubject('Payments Report');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'Wines & Liquors', 'Payments Report - ' . date('Y-m-d'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Payments report HTML
    $html = '<h2>Payments Report</h2>';
    $html .= '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    
    if (empty($payments['orders'])) {
        $html .= '<p>No payments found.</p>';
    } else {
        $html .= '<table border="1" cellpadding="5">
            <tr style="background-color: #f0f0f0;">
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>';
        
        foreach ($payments['orders'] as $payment) {
            $html .= '<tr>
                <td>#' . $payment['id'] . '</td>
                <td>' . $payment['customer_first_name'] . ' ' . $payment['customer_last_name'] . '</td>
                <td>' . number_format($payment['total_amount']) . ' RWF</td>
                <td>' . ucfirst($payment['payment_method']) . '</td>
                <td>' . ucfirst($payment['payment_status']) . '</td>
                <td>' . date('Y-m-d', strtotime($payment['created_at'])) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
    }
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdf->Output('payments_report_' . date('Y-m-d') . '.pdf', 'D');
}

function generateComprehensiveReportPDF($data) {
    $order = new Order();
    
    // Get all data for comprehensive report
    $stats = $order->getDashboardStats();
    $orders = $order->getAll(1, 1000); // Get all orders
    $pendingPayments = $order->getAll(1, 1000, null, 'pending'); // Get pending payments
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Wines & Liquors Admin');
    $pdf->SetAuthor('Wines & Liquors');
    $pdf->SetTitle('Comprehensive Business Report');
    $pdf->SetSubject('Business Analytics Report');
    
    // Set default header data with logo
    $logoPath = __DIR__ . '/../../images/logo.png';
    if (file_exists($logoPath)) {
        $pdf->SetHeaderData($logoPath, 20, 'Wines & Liquors', 'Comprehensive Business Report - ' . date('Y-m-d'));
    } else {
        $pdf->SetHeaderData('', 0, 'Wines & Liquors', 'Comprehensive Business Report - ' . date('Y-m-d'));
    }
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Add company header with logo
    $pdf->SetFillColor(102, 126, 234); // Company blue color
    $pdf->Rect(0, 0, 210, 40, 'F');
    
    // Add logo if exists
    $logoPath = __DIR__ . '/../../images/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 15, 8, 25, 25, 'PNG');
    }
    
    // Company name and title
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetXY(50, 10);
    $pdf->Cell(0, 8, 'WINES & LIQUORS', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY(50, 20);
    $pdf->Cell(0, 6, 'COMPREHENSIVE BUSINESS REPORT', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(50, 28);
    $pdf->Cell(0, 5, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'L');
    
    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln(20);
    
    // Executive Summary
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'EXECUTIVE SUMMARY', 0, 1, 'L', true);
    $pdf->Ln(8);
    
    $pdf->SetFont('helvetica', '', 12);
    $html = '
    <style>
        .summary-table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }
        .summary-table th {
            background-color: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        .summary-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .summary-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .metric-value {
            font-weight: bold;
            color: #667eea;
        }
    </style>
    <table class="summary-table">
        <tr>
            <th>Business Metric</th>
            <th>Current Value</th>
        </tr>
        <tr>
            <td><strong>📊 Total Orders Processed</strong></td>
            <td class="metric-value">' . $stats['total_orders'] . ' orders</td>
        </tr>
        <tr>
            <td><strong>💰 Total Revenue Generated</strong></td>
            <td class="metric-value">' . number_format($stats['total_revenue']) . ' RWF</td>
        </tr>
        <tr>
            <td><strong>👥 Unique Customers Served</strong></td>
            <td class="metric-value">' . $stats['unique_customers'] . ' customers</td>
        </tr>
        <tr>
            <td><strong>⏳ Pending Payments</strong></td>
            <td class="metric-value">' . $stats['pending_payments'] . ' payments</td>
        </tr>
    </table>
    ';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln(15);
    
    // Orders Analysis
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'ORDERS ANALYSIS', 0, 1, 'L', true);
    $pdf->Ln(8);
    
    if (empty($orders['orders'])) {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 10, '📋 No orders found in the system.', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        
        // Add some test data for demonstration
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Sample Orders (Test Data):', 0, 1, 'L');
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', '', 10);
        $html = '
        <style>
            .orders-table {
                border-collapse: collapse;
                width: 100%;
                margin: 10px 0;
            }
            .orders-table th {
                background-color: #667eea;
                color: white;
                padding: 8px;
                text-align: left;
                font-weight: bold;
            }
            .orders-table td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .orders-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .status-pending { color: #f39c12; font-weight: bold; }
            .status-completed { color: #27ae60; font-weight: bold; }
            .status-processing { color: #3498db; font-weight: bold; }
        </style>
        <table class="orders-table">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>
            <tr>
                <td>#1001</td>
                <td>John Doe</td>
                <td>45,000 RWF</td>
                <td class="status-completed">Completed</td>
                <td class="status-completed">Paid</td>
                <td>2025-10-01</td>
            </tr>
            <tr>
                <td>#1002</td>
                <td>Jane Smith</td>
                <td>32,500 RWF</td>
                <td class="status-processing">Processing</td>
                <td class="status-pending">Pending</td>
                <td>2025-10-02</td>
            </tr>
            <tr>
                <td>#1003</td>
                <td>Mike Johnson</td>
                <td>28,000 RWF</td>
                <td class="status-completed">Completed</td>
                <td class="status-completed">Paid</td>
                <td>2025-10-03</td>
            </tr>
        </table>
        ';
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        $pdf->SetFont('helvetica', '', 10);
        $html = '<table border="1" cellpadding="5">
            <tr style="background-color: #667eea; color: white;">
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>';
        
        foreach ($orders['orders'] as $order) {
            $html .= '<tr>
                <td>#' . $order['id'] . '</td>
                <td>' . $order['customer_first_name'] . ' ' . $order['customer_last_name'] . '</td>
                <td>' . number_format($order['total_amount']) . ' RWF</td>
                <td>' . ucfirst($order['status']) . '</td>
                <td>' . ucfirst($order['payment_status']) . '</td>
                <td>' . date('Y-m-d', strtotime($order['created_at'])) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }
    
    $pdf->AddPage();
    
    // Payment Analysis
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'PAYMENT ANALYSIS', 0, 1, 'L', true);
    $pdf->Ln(8);
    
    if (empty($pendingPayments['orders'])) {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 10, '💳 No pending payments found.', 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
        
        // Add test payment data
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Sample Payments (Test Data):', 0, 1, 'L');
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', '', 10);
        $html = '
        <style>
            .payments-table {
                border-collapse: collapse;
                width: 100%;
                margin: 10px 0;
            }
            .payments-table th {
                background-color: #667eea;
                color: white;
                padding: 8px;
                text-align: left;
                font-weight: bold;
            }
            .payments-table td {
                padding: 8px;
                border: 1px solid #ddd;
            }
            .payments-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .method-mobile { color: #e74c3c; font-weight: bold; }
            .method-cash { color: #27ae60; font-weight: bold; }
        </style>
        <table class="payments-table">
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
            </tr>
            <tr>
                <td>#1001</td>
                <td>John Doe</td>
                <td>45,000 RWF</td>
                <td class="method-mobile">Mobile Money</td>
                <td>2025-10-01</td>
            </tr>
            <tr>
                <td>#1002</td>
                <td>Jane Smith</td>
                <td>32,500 RWF</td>
                <td class="method-cash">Cash on Delivery</td>
                <td>2025-10-02</td>
            </tr>
            <tr>
                <td>#1003</td>
                <td>Mike Johnson</td>
                <td>28,000 RWF</td>
                <td class="method-mobile">Mobile Money</td>
                <td>2025-10-03</td>
            </tr>
        </table>
        ';
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        $pdf->SetFont('helvetica', '', 10);
        $html = '<table border="1" cellpadding="5">
            <tr style="background-color: #667eea; color: white;">
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Date</th>
            </tr>';
        
        foreach ($pendingPayments['orders'] as $payment) {
            $html .= '<tr>
                <td>#' . $payment['id'] . '</td>
                <td>' . $payment['customer_first_name'] . ' ' . $payment['customer_last_name'] . '</td>
                <td>' . number_format($payment['total_amount']) . ' RWF</td>
                <td>' . ucfirst($payment['payment_method']) . '</td>
                <td>' . date('Y-m-d', strtotime($payment['created_at'])) . '</td>
            </tr>';
        }
        
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }
    
    $pdf->Ln(15);
    
    // Business Insights
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 12, 'BUSINESS INSIGHTS & RECOMMENDATIONS', 0, 1, 'L', true);
    $pdf->Ln(8);
    
    $pdf->SetFont('helvetica', '', 12);
    $insights = '
    <style>
        .insights-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 10px 0;
        }
        .insights-list li {
            margin: 8px 0;
            line-height: 1.6;
        }
        .highlight {
            background-color: #667eea;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
    <div class="insights-list">
        <h3 style="color: #667eea; margin-top: 0;">📊 Key Performance Indicators</h3>
        <ul>
            <li><strong>💰 Total Business Volume:</strong> <span class="highlight">' . number_format($stats['total_revenue']) . ' RWF</span></li>
            <li><strong>👥 Customer Base:</strong> <span class="highlight">' . $stats['unique_customers'] . ' unique customers</span></li>
            <li><strong>📦 Order Processing:</strong> <span class="highlight">' . $stats['total_orders'] . ' total orders processed</span></li>
            <li><strong>⏳ Payment Status:</strong> <span class="highlight">' . $stats['pending_payments'] . ' payments pending approval</span></li>
        </ul>
        
        <h3 style="color: #667eea;">🎯 Business Recommendations</h3>
        <ul>
            <li><strong>📈 Growth Strategy:</strong> Focus on customer retention and repeat orders</li>
            <li><strong>💳 Payment Optimization:</strong> Streamline payment processing for faster transactions</li>
            <li><strong>📊 Analytics:</strong> Implement regular reporting for better business insights</li>
            <li><strong>🎯 Marketing:</strong> Target high-value customers for premium products</li>
        </ul>
    </div>
    ';
    
    $pdf->writeHTML($insights, true, false, true, false, '');
    
    // Add professional footer
    $pdf->SetY(-30);
    $pdf->SetFillColor(102, 126, 234);
    $pdf->Rect(0, $pdf->GetY(), 210, 30, 'F');
    
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(15, $pdf->GetY() + 5);
    $pdf->Cell(0, 5, 'Wines & Liquors - Premium Spirits & Wine Collection', 0, 1, 'L');
    $pdf->SetXY(15, $pdf->GetY() + 2);
    $pdf->Cell(0, 5, 'Email: info@winesliquors.com | Phone: +250 788 123 456', 0, 1, 'L');
    $pdf->SetXY(15, $pdf->GetY() + 2);
    $pdf->Cell(0, 5, 'Report generated on ' . date('Y-m-d H:i:s') . ' | Confidential Business Document', 0, 1, 'L');
    
    // Close and output PDF document
    $pdf->Output('comprehensive_report_' . date('Y-m-d') . '.pdf', 'D');
}
?>
