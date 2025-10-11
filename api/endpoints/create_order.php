<?php
/**
 * Create Order Endpoint
 * POST /api/orders
 */

// Suppress PHP warnings and errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['customerInfo', 'items', 'paymentMethod', 'total'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        sendError("Missing required field: $field", 400);
    }
}

// Validate customer info
$customer_fields = ['email', 'phone', 'firstName', 'lastName', 'location'];
foreach ($customer_fields as $field) {
    if (!isset($input['customerInfo'][$field]) || empty($input['customerInfo'][$field])) {
        sendError("Missing required customer field: $field", 400);
    }
}

// Validate email format
if (!filter_var($input['customerInfo']['email'], FILTER_VALIDATE_EMAIL)) {
    sendError('Invalid email format', 400);
}

// Validate phone format (basic validation)
if (!preg_match('/^\+?[1-9]\d{1,14}$/', $input['customerInfo']['phone'])) {
    sendError('Invalid phone format', 400);
}

// Validate items
if (!is_array($input['items']) || empty($input['items'])) {
    sendError('Items array is required and cannot be empty', 400);
}

foreach ($input['items'] as $item) {
    if (!isset($item['name']) || !isset($item['price']) || !isset($item['quantity'])) {
        sendError('Each item must have name, price, and quantity', 400);
    }
}

// Generate order ID
function generateOrderId() {
    $timestamp = substr(time(), -6);
    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    return 'ORD-' . $timestamp . $random;
}

$orderId = generateOrderId();

// Calculate total amount (remove currency symbols and convert to number)
$totalAmount = 0;
foreach ($input['items'] as $item) {
    $price = preg_replace('/[^\d.]/', '', $item['price']);
    $totalAmount += floatval($price) * intval($item['quantity']);
}

// Debug: Log what coordinates are received
error_log("DEBUG - Raw input coordinates:");
error_log("Input latitude: " . ($input['latitude'] ?? 'NOT SET') . " (type: " . gettype($input['latitude'] ?? null) . ")");
error_log("Input longitude: " . ($input['longitude'] ?? 'NOT SET') . " (type: " . gettype($input['longitude'] ?? null) . ")");
error_log("Full input data: " . json_encode($input));

// Prepare order data
$orderData = [
    'orderId' => $orderId,
    'customerInfo' => $input['customerInfo'],
    'items' => $input['items'],
    'paymentMethod' => $input['paymentMethod'],
    'totalAmount' => $totalAmount,
    'status' => 'pending',
    'paymentStatus' => 'pending',
    'latitude' => isset($input['latitude']) ? floatval($input['latitude']) : null,
    'longitude' => isset($input['longitude']) ? floatval($input['longitude']) : null
];

// Debug: Log processed coordinates
error_log("DEBUG - Processed coordinates:");
error_log("Processed latitude: " . ($orderData['latitude'] ?? 'NULL') . " (type: " . gettype($orderData['latitude']) . ")");
error_log("Processed longitude: " . ($orderData['longitude'] ?? 'NULL') . " (type: " . gettype($orderData['longitude']) . ")");


try {
    $order = new Order();
    $createdOrderId = $order->create($orderData);
    
    // Get the created order with full details
    $createdOrder = $order->getById($createdOrderId);
    
    // For mobile money, just store the phone number for contact
    if ($input['paymentMethod'] === 'mobile_money') {
        $createdOrder['paymentInfo'] = [
            'method' => 'mobile_money',
            'phone' => $input['customerInfo']['phone'],
            'message' => 'We will contact you shortly to confirm your order and provide payment instructions.'
        ];
    }
    
    // Send email notification to admin
    try {
        // Include the email service
        require_once __DIR__ . '/../../email_service.php';
        
        // Send notification using our email service
        $emailSent = sendOrderNotificationEmail($createdOrder);
        
        if ($emailSent) {
            $createdOrder['emailNotified'] = true;
            $createdOrder['notificationMessage'] = 'Admin has been notified via email (logged to file)';
        } else {
            $createdOrder['emailNotified'] = false;
            $createdOrder['notificationMessage'] = 'Failed to send email notification';
        }
        
    } catch (Exception $e) {
        // Don't fail the order creation if notification fails
        error_log('Failed to send email notification: ' . $e->getMessage());
        $createdOrder['emailNotified'] = false;
        $createdOrder['notificationMessage'] = 'Email notification failed: ' . $e->getMessage();
    }
    
    sendSuccess($createdOrder, 'Order created successfully');
    
} catch (Exception $e) {
    sendError('Failed to create order: ' . $e->getMessage(), 500);
}
?>
