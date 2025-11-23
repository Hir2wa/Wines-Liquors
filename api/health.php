<?php
/**
 * Health check endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

sendSuccess(['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')], 'API is running');
?>


