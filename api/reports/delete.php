<?php
/**
 * Delete Report API
 * Delete a generated report
 */

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'DELETE') {
        sendErrorResponse('Method not allowed', 405);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($input['id'])) {
        sendErrorResponse('Report ID is required', 400);
        return;
    }
    
    $reportId = trim($input['id']);
    
    // Get report details to check ownership and get file path
    $query = "
        SELECT 
            report_id,
            user_id,
            file_path,
            status
        FROM reports
        WHERE report_id = ? AND user_id = ?
    ";
    
    $report = $db->fetchOne($query, [$reportId, $userId]);
    
    if (!$report) {
        sendErrorResponse('Report not found', 404);
        return;
    }
    
    // Delete the file if it exists
    if (!empty($report['file_path']) && file_exists($report['file_path'])) {
        if (!unlink($report['file_path'])) {
            error_log("Failed to delete report file: " . $report['file_path']);
        }
    }
    
    // Delete the report record from database
    $success = $db->delete('reports', 'report_id = ? AND user_id = ?', [$reportId, $userId]);
    
    if ($success) {
        sendJSONResponse([
            'success' => true,
            'message' => 'Report deleted successfully'
        ]);
    } else {
        sendErrorResponse('Failed to delete report');
    }
    
} catch (Exception $e) {
    error_log("Delete report API error: " . $e->getMessage());
    sendErrorResponse('Failed to delete report: ' . $e->getMessage());
}
?> 