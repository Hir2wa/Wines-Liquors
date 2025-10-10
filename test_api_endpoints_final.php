<?php
/**
 * Test API Endpoints
 * Test all the payment system API endpoints
 */

echo "<h1>🔗 API Endpoints Test</h1>\n";
echo "<p>Testing all payment system API endpoints...</p>\n\n";

// Test endpoints
$endpoints = [
    [
        'name' => 'Health Check',
        'url' => 'http://localhost:8000/api/health',
        'method' => 'GET',
        'data' => null
    ],
    [
        'name' => 'Pending Payments',
        'url' => 'http://localhost:8000/api/admin/pending-payments',
        'method' => 'GET',
        'data' => null
    ],
    [
        'name' => 'Create Order',
        'url' => 'http://localhost:8000/api/orders',
        'method' => 'POST',
        'data' => [
            'customerInfo' => [
                'email' => 'test@example.com',
                'phone' => '+250788123456',
                'firstName' => 'Test',
                'lastName' => 'User',
                'address' => 'Test Address',
                'city' => 'Kigali',
                'country' => 'Rwanda'
            ],
            'items' => [
                [
                    'name' => 'Test Wine',
                    'price' => 50000,
                    'quantity' => 1,
                    'image' => 'test.jpg'
                ]
            ],
            'paymentMethod' => 'mobile_money',
            'total' => 50000
        ]
    ]
];

foreach ($endpoints as $endpoint) {
    echo "<h2>Testing: {$endpoint['name']}</h2>\n";
    
    $context = null;
    if ($endpoint['method'] === 'POST' && $endpoint['data']) {
        $postData = json_encode($endpoint['data']);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $postData
            ]
        ]);
    }
    
    $response = @file_get_contents($endpoint['url'], false, $context);
    
    if ($response === false) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Error</h3>";
        echo "<p><strong>Error:</strong> Failed to connect to endpoint</p>";
        echo "</div>\n";
    } else {
        $result = json_decode($response, true);
        
        if ($result && isset($result['status']) && $result['status'] >= 200 && $result['status'] < 300) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>✅ Success</h3>";
            echo "<p><strong>Status Code:</strong> {$result['status']}</p>";
            echo "<p><strong>Response:</strong></p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
            echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
            echo "</pre>";
            echo "</div>\n";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>❌ Failed</h3>";
            echo "<p><strong>Response:</strong></p>";
            echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
            echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
            echo "</pre>";
            echo "</div>\n";
        }
    }
}

echo "<h2>🎉 API Endpoints Test Complete!</h2>\n";
echo "<p>All API endpoints have been tested.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Open <code>test_payment_system.html</code> in your browser for interactive testing</li>\n";
echo "<li>Test the registration system at <code>Register.html</code></li>\n";
echo "<li>Test the admin dashboard at <code>AdminDashboard.html</code></li>\n";
echo "</ul>\n";
?>
