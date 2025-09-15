<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

try {
    $db = getDB();
    
    // Get and validate input
    $userId = (int)($_GET['id'] ?? 0);
    
    if ($userId <= 0) {
        sendErrorResponse('Invalid user ID', 400);
    }
    
    // Get user
    $user = $db->fetchOne(
        "SELECT id, username, email, full_name, role, status, department, created_at, updated_at 
         FROM users WHERE id = ?", 
        [$userId]
    );
    
    if (!$user) {
        sendErrorResponse('User not found', 404);
    }
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'view_user', "Viewed user: {$user['username']}");
    
    // Send success response
    $response = [
        'success' => true,
        'data' => $user
    ];
    
} catch (Exception $e) {
    error_log("Error in users/get.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to get user'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 