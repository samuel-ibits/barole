<?php
/**
 * Risk Alerts API
 * Get risk alerts data for risk analytics module
 */

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $severity = isset($_GET['severity']) ? trim($_GET['severity']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    
    // Validate parameters
    $page = max(1, $page);
    $limit = max(1, min(100, $limit));
    $offset = ($page - 1) * $limit;
    
    // Build query
    $whereConditions = [];
    $params = [];
    
    if (!empty($severity)) {
        $whereConditions[] = "severity = ?";
        $params[] = $severity;
    }
    
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM risk_alerts {$whereClause}";
    $countStmt = $db->query($countQuery, $params);
    $totalResult = $countStmt->fetch();
    $total = $totalResult['total'];
    
    // Get risk alerts
    $query = "
        SELECT 
            ra.*
        FROM risk_alerts ra
        {$whereClause}
        ORDER BY ra.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->query($query, $params);
    $alerts = $stmt->fetchAll();
    
    // Format data for frontend
    $formattedAlerts = [];
    foreach ($alerts as $alert) {
        $formattedAlerts[] = [
            'id' => $alert['id'],
            'alert_type' => $alert['alert_type'],
            'severity' => $alert['severity'],
            'message' => $alert['message'],
            'status' => $alert['status'],
            'created_at' => date('Y-m-d H:i', strtotime($alert['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $formattedAlerts,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    error_log("Risk alerts API error: " . $e->getMessage());
    sendErrorResponse('Failed to load risk alerts data');
}
?> 