<?php
// Test Registration API Endpoint
echo "Testing Registration API...\n";

$userData = [
    'email' => 'test' . time() . '@example.com',
    'password' => 'TestPass123',
    'firstName' => 'Test',
    'lastName' => 'User',
    'phone' => '0780146863',
    'address' => 'Test Address',
    'city' => 'Kigali',
    'country' => 'Rwanda'
];

echo "Testing with data:\n";
print_r($userData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/auth/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "\nHTTP Status Code: $httpCode\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response: $response\n";
    
    $result = json_decode($response, true);
    if ($result) {
        echo "\nParsed Response:\n";
        print_r($result);
        
        if (isset($result['data']['verification']['phone_code'])) {
            echo "\n✅ Phone verification code: " . $result['data']['verification']['phone_code'] . "\n";
        }
    }
}
?>



