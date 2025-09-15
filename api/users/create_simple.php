<?php
/**
 * Simple User Creation API
 * Uses simplified session management
 */

// Load simple session management
require_once __DIR__ . '/../../includes/simple_session.php';

// Require admin role
requireRole('admin');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

try {
    require_once __DIR__ . '/../../config/database.php';
    $db = getDB();
    
    // Get and validate input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
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
    
    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($role)) {
        $errors[] = 'Role is required';
    } elseif (!in_array($role, ['admin', 'manager', 'trader', 'analyst', 'viewer'])) {
        $errors[] = 'Invalid role';
    }
    
    // Check if username already exists
    $stmt = $db->query("SELECT id FROM users WHERE username = ?", [$username]);
    if ($stmt->fetch()) {
        $errors[] = 'Username already exists';
    }
    
    // Check if email already exists
    $stmt = $db->query("SELECT id FROM users WHERE email = ?", [$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already exists';
    }
    
    if (!empty($errors)) {
        sendErrorResponse(implode(', ', $errors), 400);
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user using the database insert method
    $userId = $db->insert('users', [
        'username' => $username,
        'password_hash' => $passwordHash,
        'email' => $email,
        'full_name' => $fullName,
        'role' => $role,
        'status' => 'active',
        'department' => $department,
        'permissions' => json_encode([]), // Empty permissions for now
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    if (!$userId) {
        throw new Exception('Failed to create user');
    }
    
    // Get the created user
    $user = $db->query(
        "SELECT id, username, email, full_name, role, status, department, created_at 
         FROM users WHERE id = ?", 
        [$userId]
    )->fetch();
    
    // Log activity
    logUserActivity('create_user', "Created user: {$username}");
    
    // Send success response
    sendSuccessResponse($user, 'User created successfully');
    
} catch (Exception $e) {
    error_log("Error in users/create_simple.php: " . $e->getMessage());
    sendErrorResponse('Failed to create user: ' . $e->getMessage());
}
?> 