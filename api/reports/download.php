<?php
/**
 * Report Download API
 * Download generated report files
 */

require_once __DIR__ . '/../../config/app.php';

requireAuth();

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    
    // Get report ID from query parameter
    $reportId = isset($_GET['id']) ? trim($_GET['id']) : '';
    
    if (empty($reportId)) {
        http_response_code(400);
        echo "Report ID is required";
        exit;
    }
    
    // Get report details
    $query = "
        SELECT 
            report_id,
            user_id,
            file_name,
            file_path,
            format,
            status
        FROM reports
        WHERE report_id = ? AND user_id = ?
    ";
    
    $report = $db->fetchOne($query, [$reportId, $userId]);
    
    if (!$report) {
        http_response_code(404);
        echo "Report not found";
        exit;
    }
    
    if ($report['status'] !== 'completed') {
        http_response_code(400);
        echo "Report is not ready for download";
        exit;
    }
    
    // Check if file exists
    $filePath = $report['file_path'];
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo "Report file not found";
        exit;
    }
    
    // Set appropriate headers for file download
    $fileName = $report['file_name'];
    $format = $report['format'];
    
    // Set content type based on format
    switch ($format) {
        case 'pdf':
            $contentType = 'application/pdf';
            break;
        case 'excel':
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            break;
        case 'csv':
            $contentType = 'text/csv';
            break;
        default:
            $contentType = 'application/octet-stream';
    }
    
    // Send headers
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Output file content
    readfile($filePath);
    
} catch (Exception $e) {
    error_log("Report download error: " . $e->getMessage());
    http_response_code(500);
    echo "Error downloading report";
}
?> 