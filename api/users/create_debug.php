<?php
/**
 * Debug User Creation API
 * Shows exact database errors
 */

// NO OUTPUT BEFORE THIS POINT
session_start();

// Load database
require_once __DIR__ . '/../../config/database.php';

// Set content type first
header('Content-Type: application/json');

// Simple authentication check
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Simple admin check
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin role required']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = getDB();
    
    // First, let's check the table structure
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    $debug = [
        'table_structure' => $columns,
        'post_data' => $_POST,
        'session_data' => $_SESSION
    ];
    
    // Get and validate input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $department = trim($_POST['department'] ?? '');
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($fullName) || empty($role)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Missing required fields',
            'debug' => $debug
        ]);
        exit;
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Default permissions for role
    $permissions = [
        'admin' => ['user_manage', 'system_config', 'audit_view'],
        'manager' => ['trade_approve', 'report_generate'],
        'trader' => ['trade_create', 'trade_edit'],
        'analyst' => ['trade_view', 'risk_view'],
        'viewer' => ['trade_view', 'dashboard_view']
    ];
    
    // Prepare data for insertion
    $insertData = [
        'username' => $username,
        'email' => $email,
        'password_hash' => $passwordHash,
        'full_name' => $fullName,
        'role' => $role,
        'status' => 'active'
    ];
    
    // Add permissions if column exists
    $columnNames = array_column($columns, 'Field');
    if (in_array('permissions', $columnNames)) {
        $insertData['permissions'] = json_encode($permissions[$role] ?? []);
    }
    
    $debug['insert_data'] = $insertData;
    $debug['column_names'] = $columnNames;
    
    // Try to insert
    try {
        $userId = $db->insert('users', $insertData);
        
        if ($userId) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId,
                'debug' => $debug
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to get user ID after insert',
                'debug' => $debug
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database PDO Error: ' . $e->getMessage(),
            'error_code' => $e->getCode(),
            'debug' => $debug
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'General error: ' . $e->getMessage(),
        'debug' => $debug ?? []
    ]);
} 