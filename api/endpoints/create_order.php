<?php
/**
 * Create Order Endpoint
 * POST /api/orders
 */

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
$customer_fields = ['email', 'phone', 'firstName', 'lastName', 'address', 'city', 'country'];
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

// Prepare order data
$orderData = [
    'orderId' => $orderId,
    'customerInfo' => $input['customerInfo'],
    'items' => $input['items'],
    'paymentMethod' => $input['paymentMethod'],
    'totalAmount' => $totalAmount,
    'status' => 'pending',
    'paymentStatus' => 'pending'
];

try {
    $order = new Order();
    $createdOrderId = $order->create($orderData);
    
    // Get the created order with full details
    $createdOrder = $order->getById($createdOrderId);
    
    // Generate payment code if payment method is mobile money
    if ($input['paymentMethod'] === 'mobile_money') {
        $paymentCodeResult = $order->generatePaymentCode(
            $createdOrderId, 
            $totalAmount, 
            $input['customerInfo']['phone']
        );
        
        if ($paymentCodeResult['success']) {
            $createdOrder['paymentCode'] = $paymentCodeResult;
        }
    }
    
    sendSuccess($createdOrder, 'Order created successfully');
    
} catch (Exception $e) {
    sendError('Failed to create order: ' . $e->getMessage(), 500);
}
?>
