<?php
/**
 * Debug API - Check what's happening with session and authentication
 */

// NO OUTPUT BEFORE THIS POINT
session_start();

// Load database
require_once __DIR__ . '/../config/database.php';

// Set content type first
header('Content-Type: application/json');

// Debug information
$debug = [
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'server_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'headers_sent' => headers_sent(),
    'php_version' => PHP_VERSION,
    'database_connection' => false,
    'current_user' => null,
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
];

// Test database connection
try {
    $db = getDB();
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    $debug['database_connection'] = ($result['test'] == 1);
    
    // If user is logged in, get user info
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->query("SELECT id, username, role, status FROM users WHERE id = ?", [$_SESSION['user_id']]);
        $debug['current_user'] = $stmt->fetch();
    }
} catch (Exception $e) {
    $debug['database_error'] = $e->getMessage();
}

// Authentication status
$debug['authentication'] = [
    'logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
    'is_admin' => isset($_SESSION['role']) && $_SESSION['role'] === 'admin',
    'user_id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
    'role' => $_SESSION['role'] ?? null
];

// Send response
echo json_encode([
    'success' => true,
    'message' => 'Debug information',
    'debug' => $debug
], JSON_PRETTY_PRINT); 