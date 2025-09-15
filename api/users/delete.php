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
    $user = $db->fetchOne("SELECT id, username, role FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        sendErrorResponse('User not found', 404);
    }
    
    // Prevent deleting the last admin
    if ($user['role'] === ROLE_ADMIN) {
        $adminCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = ?", [ROLE_ADMIN]);
        if ($adminCount['count'] <= 1) {
            sendErrorResponse('Cannot delete the last admin user', 400);
        }
    }
    
    // Prevent deleting self
    if ($userId === getCurrentUserId()) {
        sendErrorResponse('Cannot delete your own account', 400);
    }
    
    // Delete user
    $success = $db->delete('users', 'id = ?', [$userId]);
    
    if (!$success) {
        throw new Exception('Failed to delete user');
    }
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'delete_user', "Deleted user: {$user['username']}");
    
    // Send success response
    $response = [
        'success' => true,
        'message' => 'User deleted successfully'
    ];
    
} catch (Exception $e) {
    error_log("Error in users/delete.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to delete user: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 