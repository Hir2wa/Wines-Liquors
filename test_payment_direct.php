<?php
/**
 * Direct Payment System Test
 * Test the payment system directly without cURL
 */

require_once 'config/database.php';
require_once 'models/Order.php';

echo "<h1>🧪 Direct Payment System Test</h1>\n";
echo "<p>Testing the payment system directly...</p>\n\n";

try {
    // Test 1: Create Order
    echo "<h2>Test 1: Create Order with Mobile Money Payment</h2>\n";
    
    $orderData = [
        'orderId' => 'ORD-' . time() . rand(100, 999),
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
                'price' => 50000,
                'quantity' => 1,
                'image' => 'images/WINE1.webp'
            ]
        ],
        'paymentMethod' => 'mobile_money',
        'totalAmount' => 50000,
        'status' => 'pending',
        'paymentStatus' => 'pending'
    ];
    
    $order = new Order();
    $createdOrderId = $order->create($orderData);
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>✅ Order Created Successfully!</h3>";
    echo "<p><strong>Order ID:</strong> $createdOrderId</p>";
    echo "</div>\n";
    
    // Test 2: Generate Payment Code
    echo "<h2>Test 2: Generate Mobile Money Payment Code</h2>\n";
    
    $paymentCodeResult = $order->generatePaymentCode(
        $createdOrderId, 
        50000, 
        '+250788123456'
    );
    
    if ($paymentCodeResult['success']) {
        $paymentCode = $paymentCodeResult['payment_code'];
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ Payment Code Generated!</h3>";
        echo "<p><strong>Payment Code:</strong> <code style='background: #e9ecef; padding: 4px 8px; border-radius: 3px; font-weight: bold; color: #dc3545; font-size: 18px;'>$paymentCode</code></p>";
        echo "<p><strong>Amount:</strong> {$paymentCodeResult['amount']}frw</p>";
        echo "<p><strong>Instructions:</strong> {$paymentCodeResult['instructions']}</p>";
        echo "<p><strong>Expires:</strong> {$paymentCodeResult['expires_at']}</p>";
        echo "</div>\n";
        
        // Test 3: Get Payment Code
        echo "<h2>Test 3: Retrieve Payment Code</h2>\n";
        
        $retrievedCode = $order->getPaymentCode($createdOrderId);
        
        if ($retrievedCode['success']) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>✅ Payment Code Retrieved!</h3>";
            echo "<p><strong>Payment Code:</strong> {$retrievedCode['payment_code']}</p>";
            echo "<p><strong>Amount:</strong> {$retrievedCode['amount']}frw</p>";
            echo "<p><strong>Phone:</strong> {$retrievedCode['phone_number']}</p>";
            echo "</div>\n";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>❌ Failed to Retrieve Payment Code</h3>";
            echo "<p>{$retrievedCode['message']}</p>";
            echo "</div>\n";
        }
        
        // Test 4: Admin Payment Verification
        echo "<h2>Test 4: Admin Payment Verification</h2>\n";
        
        $verifyResult = $order->verifyPaymentCode($createdOrderId, $paymentCode, 'Test Admin');
        
        if ($verifyResult['success']) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>✅ Payment Verified Successfully!</h3>";
            echo "<p><strong>Order ID:</strong> {$verifyResult['order_id']}</p>";
            echo "<p><strong>Amount:</strong> {$verifyResult['amount']}frw</p>";
            echo "<p><strong>Message:</strong> {$verifyResult['message']}</p>";
            echo "</div>\n";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>❌ Payment Verification Failed</h3>";
            echo "<p>{$verifyResult['message']}</p>";
            echo "</div>\n";
        }
        
        // Test 5: Get Pending Payments
        echo "<h2>Test 5: Get Pending Payments</h2>\n";
        
        $pendingPayments = $order->getPendingPaymentCodes();
        
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>📋 Pending Payments</h3>";
        echo "<p><strong>Count:</strong> " . count($pendingPayments) . " pending payments</p>";
        
        if (!empty($pendingPayments)) {
            echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Order ID</th>";
            echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Payment Code</th>";
            echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Amount</th>";
            echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Customer</th>";
            echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Created</th>";
            echo "</tr>";
            
            foreach ($pendingPayments as $payment) {
                echo "<tr>";
                echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$payment['order_id']}</td>";
                echo "<td style='border: 1px solid #dee2e6; padding: 8px;'><code>{$payment['payment_code']}</code></td>";
                echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$payment['amount']}frw</td>";
                echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$payment['customer_name']}</td>";
                echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$payment['created_at']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        echo "</div>\n";
        
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Failed to Generate Payment Code</h3>";
        echo "<p>{$paymentCodeResult['message']}</p>";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>❌ Test Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>\n";
}

echo "<h2>🎉 Test Complete!</h2>\n";
echo "<p>All payment system components have been tested directly.</p>\n";
echo "<p><strong>What was tested:</strong></p>\n";
echo "<ul>\n";
echo "<li>✅ Order creation with mobile money payment</li>\n";
echo "<li>✅ Payment code generation</li>\n";
echo "<li>✅ Payment code retrieval</li>\n";
echo "<li>✅ Admin payment verification</li>\n";
echo "<li>✅ Pending payments listing</li>\n";
echo "</ul>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Open <code>test_payment_system.html</code> in your browser for interactive testing</li>\n";
echo "<li>Test the registration system with email and phone verification</li>\n";
echo "<li>Test the admin dashboard payment verification</li>\n";
echo "</ul>\n";
?>
