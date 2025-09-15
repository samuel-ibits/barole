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
    $userIds = $_POST['ids'] ?? [];
    
    if (empty($userIds) || !is_array($userIds)) {
        sendErrorResponse('No users selected', 400);
    }
    
    // Convert to integers and validate
    $userIds = array_map('intval', $userIds);
    $userIds = array_filter($userIds, function($id) { return $id > 0; });
    
    if (empty($userIds)) {
        sendErrorResponse('Invalid user IDs', 400);
    }
    
    // Get users to be deleted
    $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
    $users = $db->fetchAll("SELECT id, username, role FROM users WHERE id IN ($placeholders)", $userIds);
    
    if (empty($users)) {
        sendErrorResponse('No valid users found', 404);
    }
    
    // Check for safety constraints
    $currentUserId = getCurrentUserId();
    $deletedUsers = [];
    $errors = [];
    
    foreach ($users as $user) {
        // Prevent deleting self
        if ($user['id'] === $currentUserId) {
            $errors[] = "Cannot delete your own account ({$user['username']})";
            continue;
        }
        
        // Check if this is the last admin
        if ($user['role'] === ROLE_ADMIN) {
            $adminCount = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = ? AND id NOT IN ($placeholders)", array_merge([ROLE_ADMIN], $userIds));
            if ($adminCount['count'] <= 0) {
                $errors[] = "Cannot delete the last admin user ({$user['username']})";
                continue;
            }
        }
        
        $deletedUsers[] = $user;
    }
    
    if (!empty($errors)) {
        sendErrorResponse(implode(', ', $errors), 400);
    }
    
    if (empty($deletedUsers)) {
        sendErrorResponse('No users can be deleted', 400);
    }
    
    // Delete users
    $deletedIds = array_column($deletedUsers, 'id');
    $placeholders = str_repeat('?,', count($deletedIds) - 1) . '?';
    $success = $db->delete('users', "id IN ($placeholders)", $deletedIds);
    
    if (!$success) {
        throw new Exception('Failed to delete users');
    }
    
    // Log activity
    $usernames = array_column($deletedUsers, 'username');
    logUserActivity(getCurrentUserId(), 'bulk_delete_users', "Deleted users: " . implode(', ', $usernames));
    
    // Send success response
    $response = [
        'success' => true,
        'message' => count($deletedUsers) . ' users deleted successfully',
        'data' => [
            'deleted_count' => count($deletedUsers),
            'deleted_users' => $usernames
        ]
    ];
    
} catch (Exception $e) {
    error_log("Error in users/bulk-delete.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to delete users: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 