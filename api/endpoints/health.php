<?php
/**
 * Health Check Endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    'status' => 200,
    'message' => 'API is running',
    'data' => [
        'status' => 'OK',
        'timestamp' => date('Y-m-d H:i:s')
    ],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>





