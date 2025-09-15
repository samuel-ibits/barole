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
    $user = $db->fetchOne("SELECT id, username FROM users WHERE id = ?", [$userId]);
    if (!$user) {
        sendErrorResponse('User not found', 404);
    }
    
    // Generate secure password
    $newPassword = generateSecurePassword();
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $success = $db->update('users', 
        ['password_hash' => $passwordHash, 'updated_at' => date('Y-m-d H:i:s')], 
        'id = ?', 
        [$userId]
    );
    
    if (!$success) {
        throw new Exception('Failed to reset password');
    }
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'reset_password', "Reset password for user: {$user['username']}");
    
    // Send success response
    $response = [
        'success' => true,
        'message' => 'Password reset successfully',
        'data' => [
            'new_password' => $newPassword
        ]
    ];
    
} catch (Exception $e) {
    error_log("Error in users/reset-password.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to reset password: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 