<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

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
    
    // Prepare response
    $response = [
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
    ];
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'view_users', "Viewed user list (page {$page}, limit {$limit})");
    
} catch (Exception $e) {
    error_log("Error in users/list.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to load users'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 