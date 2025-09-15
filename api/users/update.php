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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    // $phone = trim($_POST['phone'] ?? ''); // Phone column not in database
    $department = trim($_POST['department'] ?? '');
    
    // Validation
    $errors = [];
    
    if ($userId <= 0) {
        $errors[] = 'Invalid user ID';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($role)) {
        $errors[] = 'Role is required';
    } elseif (!in_array($role, [ROLE_ADMIN, ROLE_MANAGER, ROLE_TRADER, ROLE_ANALYST, ROLE_VIEWER])) {
        $errors[] = 'Invalid role';
    }
    
    if (!in_array($status, ['active', 'inactive', 'suspended'])) {
        $errors[] = 'Invalid status';
    }
    
    // Check if user exists
    $existingUser = $db->fetchOne("SELECT id, username, email FROM users WHERE id = ?", [$userId]);
    if (!$existingUser) {
        $errors[] = 'User not found';
    }
    
    // Check if username already exists (excluding current user)
    $existingUsername = $db->fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $userId]);
    if ($existingUsername) {
        $errors[] = 'Username already exists';
    }
    
    // Check if email already exists (excluding current user)
    $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
    if ($existingEmail) {
        $errors[] = 'Email already exists';
    }
    
    if (!empty($errors)) {
        sendErrorResponse(implode(', ', $errors), 400);
    }
    
    // Get default permissions for role
    $permissions = getRolePermissions($role);
    
    // Update user
    $updateData = [
        'username' => $username,
        'email' => $email,
        'full_name' => $fullName,
        'role' => $role,
        'status' => $status,
        // 'phone' => $phone, // Phone column not in database
        'department' => $department,
        'permissions' => json_encode($permissions),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $success = $db->update('users', $updateData, 'id = ?', [$userId]);
    
    if (!$success) {
        throw new Exception('Failed to update user');
    }
    
    // Get the updated user
    $user = $db->fetchOne(
        "SELECT id, username, email, full_name, role, status, department, created_at, updated_at 
         FROM users WHERE id = ?", 
        [$userId]
    );
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'update_user', "Updated user: {$username}");
    
    // Send success response
    $response = [
        'success' => true,
        'message' => 'User updated successfully',
        'data' => $user
    ];
    
} catch (Exception $e) {
    error_log("Error in users/update.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to update user: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 