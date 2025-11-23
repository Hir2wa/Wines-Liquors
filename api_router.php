<?php
/**
 * API Router for PHP Development Server
 * This file handles all API requests and routes them to the appropriate endpoints
 */

// Only handle requests that start with /api/
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== 0) {
    return false; // Let the server handle other requests
}

// Include the main API router
require_once 'api/index.php';
?>






