<?php
/**
 * Simplified User List API
 * Works with fixed session management
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

// Simple admin check (only admins can view user list)
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin role required']);
    exit;
}

try {
    $db = getDB();
    
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $role = isset($_GET['role']) ? trim($_GET['role']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($search)) {
        $whereConditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($role)) {
        $whereConditions[] = "role = ?";
        $params[] = $role;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM users {$whereClause}";
    $countStmt = $db->query($countQuery, $params);
    $totalResult = $countStmt->fetch();
    $total = $totalResult['total'];
    
    // Get users
    $query = "
        SELECT 
            id, username, email, full_name, role, status, 
            department, created_at, last_login
        FROM users 
        {$whereClause}
        ORDER BY 
            CASE role
                WHEN 'admin' THEN 1
                WHEN 'manager' THEN 2
                WHEN 'trader' THEN 3
                WHEN 'analyst' THEN 4
                WHEN 'viewer' THEN 5
                ELSE 6
            END,
            username
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->query($query, $params);
    $users = $stmt->fetchAll();
    
    // Calculate pagination info
    $totalPages = ceil($total / $limit);
    $hasNext = $page < $totalPages;
    $hasPrev = $page > 1;
    
    // Send success response
    echo json_encode([
        'success' => true,
        'data' => [
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $hasNext,
                'has_prev' => $hasPrev
            ],
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in users/list_simple.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load users: ' . $e->getMessage()
    ]);
} 