<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    sendErrorResponse('Invalid CSRF token', 403);
}

try {
    $db = getDB();
    
    // Get and validate input
    $userId = (int)($_POST['id'] ?? 0);
    
    if ($userId <= 0) {
        sendErrorResponse('Invalid user ID', 400);
    }
    
    // Check if user exists
    $user = $db->fetchOne("SELECT id, username, status, role FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        sendErrorResponse('User not found', 404);
    }
    
    // Prevent deactivating the last admin
    if ($user['role'] === ROLE_ADMIN && $user['status'] === 'active') {
        $adminCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = ? AND status = 'active'", [ROLE_ADMIN]);
        if ($adminCount['count'] <= 1) {
            sendErrorResponse('Cannot deactivate the last admin user', 400);
        }
    }
    
    // Prevent deactivating self
    if ($userId === getCurrentUserId()) {
        sendErrorResponse('Cannot deactivate your own account', 400);
    }
    
    // Toggle status
    $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
    
    $success = $db->update('users', 
        ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 
        'id = ?', 
        [$userId]
    );
    
    if (!$success) {
        throw new Exception('Failed to update user status');
    }
    
    // Log activity
    $action = $newStatus === 'active' ? 'activate_user' : 'deactivate_user';
    logUserActivity(getCurrentUserId(), $action, "{$newStatus} user: {$user['username']}");
    
    // Send success response
    $response = [
        'success' => true,
        'message' => "User {$newStatus} successfully",
        'data' => [
            'user_id' => $userId,
            'new_status' => $newStatus
        ]
    ];
    
} catch (Exception $e) {
    error_log("Error in users/toggle-status.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to update user status: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 