<?php
/**
 * Test User Creation - Minimal version for debugging
 */

// NO OUTPUT BEFORE THIS POINT
session_start();

// Set content type first  
header('Content-Type: application/json');

// Simple test data
$response = [
    'endpoint' => 'test_create_user.php',
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
    'session_active' => (session_status() === PHP_SESSION_ACTIVE),
    'logged_in' => isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true,
    'user_role' => $_SESSION['role'] ?? 'none',
    'post_data' => $_POST,
    'headers_sent_before' => headers_sent()
];

// If it's a POST request, try to simulate user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check authentication
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        $response['error'] = 'Not logged in';
        $response['success'] = false;
    } elseif ($_SESSION['role'] !== 'admin') {
        $response['error'] = 'Not admin';
        $response['success'] = false;
    } else {
        // Try database connection
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            
            // Test a simple query
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            
            $response['success'] = true;
            $response['message'] = 'All checks passed - user creation should work';
            $response['user_count'] = $result['count'];
            $response['received_data'] = [
                'username' => $_POST['username'] ?? 'missing',
                'email' => $_POST['email'] ?? 'missing',
                'full_name' => $_POST['full_name'] ?? 'missing',
                'role' => $_POST['role'] ?? 'missing'
            ];
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['error'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['success'] = true;
    $response['message'] = 'Test endpoint is working - send POST request to test user creation';
}

// Send response
echo json_encode($response, JSON_PRETTY_PRINT); 