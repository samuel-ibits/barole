<?php
require_once __DIR__ . '/../../config/app.php';

// Check authentication
requireAuth();

// Check permissions
requireRole(ROLE_ADMIN);

try {
    $db = getDB();
    
    // Get parameters
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    $activityType = isset($_GET['activity_type']) ? trim($_GET['activity_type']) : '';
    $dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    
    // Validate parameters
    $limit = max(1, min(1000, $limit));
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if ($userId > 0) {
        $whereConditions[] = "ual.user_id = ?";
        $params[] = $userId;
    }
    
    if (!empty($activityType)) {
        $whereConditions[] = "ual.action = ?";
        $params[] = $activityType;
    }
    
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(ual.created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(ual.created_at) <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get user activity with user details
    $query = "
        SELECT 
            ual.id,
            ual.user_id,
            u.username,
            u.full_name,
            ual.action,
            ual.details,
            ual.ip_address,
            ual.created_at
        FROM user_activity_logs ual
        LEFT JOIN users u ON ual.user_id = u.id
        {$whereClause}
        ORDER BY ual.created_at DESC
        LIMIT ?
    ";
    
    $params[] = $limit;
    
    $stmt = $db->query($query, $params);
    $activity = $stmt->fetchAll();
    
    // Log activity
    logUserActivity(getCurrentUserId(), 'view_activity', 'Viewed user activity logs');
    
    // Send success response
    $response = [
        'success' => true,
        'data' => $activity
    ];
    
} catch (Exception $e) {
    error_log("Error in users/activity.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'Failed to load activity'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 