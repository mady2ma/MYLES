<?php
require_once 'env.php';

// Security settings
define('SESSION_COOKIE_LIFETIME', 86400); // 24 hours

// Disable display errors, enable logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Start secure session
session_set_cookie_params([
    'lifetime' => SESSION_COOKIE_LIFETIME,
    'path' => '/',
    'domain' => '',
    'secure' => true, // Enforce HTTPS in production
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return null;
    }
    return $conn;
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}
?>