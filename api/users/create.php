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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? 'active');
    // $phone = trim($_POST['phone'] ?? ''); // Phone column not in database
    $department = trim($_POST['department'] ?? '');
    
    // Validation
    $errors = [];
    
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
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (empty($role)) {
        $errors[] = 'Role is required';
    } elseif (!in_array($role, [ROLE_ADMIN, ROLE_MANAGER, ROLE_TRADER, ROLE_ANALYST, ROLE_VIEWER])) {
        $errors[] = 'Invalid role';
    }
    
    if (!in_array($status, ['active', 'inactive', 'suspended'])) {
        $errors[] = 'Invalid status';
    }
    
    // Check if username already exists
    $existingUser = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existingUser) {
        $errors[] = 'Username already exists';
    }
    
    // Check if email already exists
    $existingEmail = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingEmail) {
        $errors[] = 'Email already exists';
    }
    
    if (!empty($errors)) {
        sendErrorResponse(implode(', ', $errors), 400);
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Get default permissions for role
    $permissions = getRolePermissions($role);
    
    // Insert user
    $userId = $db->insert('users', [
        'username' => $username,
        'email' => $email,
        'password_hash' => $passwordHash,
        'full_name' => $fullName,
        'role' => $role,
        'status' => $status,
        // 'phone' => $phone, // Phone column not in database
        'department' => $department,
        'permissions' => json_encode($permissions),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if (!$userId) {
        throw new Exception('Failed to create user');
    }
    
    // Get the created user
    $user = $db->fetchOne(
        "SELECT id, username, email, full_name, role, status, department, created_at 
         FROM users WHERE id = ?", 
        [$userId]
    );
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'create_user', "Created user: {$username}");
    
    // Send success response
    $response = [
        'success' => true,
        'message' => 'User created successfully',
        'data' => $user
    ];
    
} catch (Exception $e) {
    error_log("Error in users/create.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to create user: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 