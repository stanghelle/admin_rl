<?php
/**
 * API Authentication Middleware
 * Include this file at the top of API endpoints that require authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the main init file to get access to Auth class
require_once __DIR__ . '/../core/init.php';

// Include config for database connection
require_once __DIR__ . '/config.php';

// Set JSON headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Only allow specific origins in production
$allowedOrigins = [
    'http://localhost',
    'https://yourdomain.com' // Add your production domain
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // For development, allow all origins (remove in production)
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Require API authentication
 * Call this function in API endpoints that need auth
 */
function requireApiAuth() {
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Authentication required',
            'authenticated' => false
        ]);
        exit;
    }
}

/**
 * Require CSRF token for state-changing operations
 * Call this function for POST, PUT, DELETE requests
 */
function requireCsrfToken() {
    // Get token from header or POST body
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ??
             $_POST['csrf_token'] ??
             $_POST['token'] ?? '';

    if (empty($token) || !Token::check($token, false)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Invalid or missing CSRF token',
            'code' => 'CSRF_INVALID'
        ]);
        exit;
    }
}

/**
 * Helper function to get JSON input
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

/**
 * Helper function to send JSON response
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

/**
 * Helper function to send error response
 */
function jsonError($message, $code = 400) {
    jsonResponse(['error' => $message], $code);
}
