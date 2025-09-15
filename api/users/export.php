<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

try {
    $db = getDB();
    
    // Get parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $role = isset($_GET['role']) ? trim($_GET['role']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
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
    
    // Get users
    $query = "
        SELECT 
            username, email, full_name, role, status, 
            department, created_at, last_login
        FROM users 
        {$whereClause}
        ORDER BY role, username
    ";
    
    $users = $db->fetchAll($query, $params);
    
    // Set headers for CSV download
    $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create CSV output
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // CSV headers
    fputcsv($output, [
        'Username',
        'Email',
        'Full Name',
        'Role',
        'Status',
        // 'Phone', // Phone column not in database
        'Department',
        'Created At',
        'Last Login'
    ]);
    
    // CSV data
    foreach ($users as $user) {
        fputcsv($output, [
            $user['username'],
            $user['email'],
            $user['full_name'],
            $user['role'],
            $user['status'],
            // $user['phone'], // Phone column not in database
            $user['department'],
            $user['created_at'],
            $user['last_login'] ?: 'Never'
        ]);
    }
    
    fclose($output);
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'export_users', "Exported " . count($users) . " users to CSV");
    
} catch (Exception $e) {
    error_log("Error in users/export.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to export users'
    ]);
}
?> 