<?php
// Start session and set up admin session for testing
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;
$_SESSION['last_activity'] = time();

// Include the app config
require_once __DIR__ . '/../config/app.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the requested endpoint
$endpoint = $_GET['endpoint'] ?? '';

try {
    switch ($endpoint) {
        case 'users':
            include 'users/list.php';
            break;
        case 'roles':
            include 'users/roles.php';
            break;
        case 'permissions':
            include 'users/permissions.php';
            break;
        case 'activity':
            include 'users/activity.php';
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid endpoint. Use: users, roles, permissions, activity'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 