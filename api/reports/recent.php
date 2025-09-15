<?php
/**
 * Recent Reports API
 * Get recent reports for current user
 */

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    // Get parameters
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
    
    // Query recent reports for the user
    $query = "
        SELECT 
            report_id as id,
            report_type,
            category,
            format,
            status,
            file_name as name,
            created_at as generated_at,
            updated_at
        FROM reports
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ";
    
    $reports = $db->fetchAll($query, [$userId, $limit]);
    
    // Format the response
    $formattedReports = [];
    foreach ($reports as $report) {
        $formattedReports[] = [
            'id' => $report['id'],
            'name' => $report['name'] ?: ucwords(str_replace('_', ' ', $report['report_type'])),
            'category' => ucwords($report['category']),
            'format' => $report['format'],
            'status' => $report['status'],
            'generated_at' => $report['generated_at'],
            'updated_at' => $report['updated_at']
        ];
    }
    
    sendJSONResponse([
        'success' => true,
        'data' => $formattedReports
    ]);
    
} catch (Exception $e) {
    error_log("Recent reports API error: " . $e->getMessage());
    sendErrorResponse('Failed to load recent reports: ' . $e->getMessage());
}
?> 