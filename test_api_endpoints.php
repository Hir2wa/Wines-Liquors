<?php
/**
 * Test API Endpoints
 * Simple test to verify the payment system API endpoints are working
 */

// Test data
$testOrderData = [
    'customerInfo' => [
        'email' => 'test@example.com',
        'phone' => '+250788123456',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'address' => 'Test Address',
        'city' => 'Kigali',
        'country' => 'Rwanda'
    ],
    'items' => [
        [
            'name' => 'Test Wine Bottle',
            'price' => '50000frw',
            'quantity' => 1,
            'image' => 'images/WINE1.webp'
        ]
    ],
    'paymentMethod' => 'mobile_money',
    'total' => 50000
];

echo "<h1>🧪 API Endpoints Test</h1>\n";
echo "<p>Testing the payment system API endpoints...</p>\n\n";

// Test 1: Create Order
echo "<h2>Test 1: Create Order with Mobile Money Payment</h2>\n";
echo "<p>Creating a test order...</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/orders');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testOrderData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $httpCode</p>\n";
echo "<p><strong>Response:</strong></p>\n";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars($response);
echo "</pre>\n\n";

// Parse response to get order ID and payment code
$responseData = json_decode($response, true);
$orderId = null;
$paymentCode = null;

if ($responseData && isset($responseData['data'])) {
    $orderId = $responseData['data']['orderId'] ?? null;
    $paymentCode = $responseData['data']['paymentCode']['payment_code'] ?? null;
    
    if ($orderId && $paymentCode) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ Order Created Successfully!</h3>";
        echo "<p><strong>Order ID:</strong> $orderId</p>";
        echo "<p><strong>Payment Code:</strong> <code style='background: #e9ecef; padding: 4px 8px; border-radius: 3px; font-weight: bold; color: #dc3545;'>$paymentCode</code></p>";
        echo "</div>\n";
    }
}

// Test 2: Get Payment Code (if order was created)
if ($orderId) {
    echo "<h2>Test 2: Get Payment Code for Order</h2>\n";
    echo "<p>Retrieving payment code for order: $orderId</p>\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/orders/$orderId/payment-code");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> $httpCode</p>\n";
    echo "<p><strong>Response:</strong></p>\n";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>\n\n";
}

// Test 3: Get Pending Payments
echo "<h2>Test 3: Get Pending Payments</h2>\n";
echo "<p>Retrieving all pending mobile money payments...</p>\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/admin/pending-payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> $httpCode</p>\n";
echo "<p><strong>Response:</strong></p>\n";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars($response);
echo "</pre>\n\n";

// Test 4: Verify Payment (if we have order ID and payment code)
if ($orderId && $paymentCode) {
    echo "<h2>Test 4: Admin Payment Verification</h2>\n";
    echo "<p>Simulating admin verification of payment...</p>\n";
    
    $verifyData = [
        'orderId' => $orderId,
        'paymentCode' => $paymentCode,
        'verifiedBy' => 'Test Admin'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/admin/verify-payment-code');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verifyData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> $httpCode</p>\n";
    echo "<p><strong>Response:</strong></p>\n";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>\n\n";
    
    if ($httpCode === 200) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ Payment Verified Successfully!</h3>";
        echo "<p>The payment has been verified and the order status updated.</p>";
        echo "</div>\n";
    }
}

echo "<h2>🎉 Test Complete!</h2>\n";
echo "<p>All API endpoints have been tested. Check the responses above to verify functionality.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Open <code>test_payment_system.html</code> in your browser for interactive testing</li>\n";
echo "<li>Test the registration system with email and phone verification</li>\n";
echo "<li>Test the admin dashboard payment verification</li>\n";
echo "</ul>\n";
?>

