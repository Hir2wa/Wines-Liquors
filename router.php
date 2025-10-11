<?php
/**
 * PHP Development Server Router
 * This file handles all requests and routes them appropriately
 */

// Get the request URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string from URI
$uri = strtok($uri, '?');

// Handle API requests
if (strpos($uri, '/api/') === 0) {
    // Debug: Log API requests
    error_log("Router: API request to $uri");
    // Include the API router
    require_once 'api/index.php';
    return;
}

// Handle static files (CSS, JS, images, etc.)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    // Let the server handle static files
    return false;
}

// Handle HTML files
if (preg_match('/\.html$/', $uri)) {
    // Let the server handle HTML files
    return false;
}

// Default: serve index.html for root requests
if ($uri === '/' || $uri === '') {
    include 'index.html';
    return;
}

// For any other requests, return 404
http_response_code(404);
echo "404 Not Found";
?>