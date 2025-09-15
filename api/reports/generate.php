<?php
/**
 * Reports Generation API
 * Handle report generation requests
 */

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'POST') {
        sendErrorResponse('Method not allowed', 405);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $errors = [];
    $requiredFields = ['category', 'reportType', 'reportFormat', 'startDate', 'endDate'];
    
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            $errors[] = "Field '$field' is required";
        }
    }
    
    if (!empty($errors)) {
        sendErrorResponse('Validation failed: ' . implode(', ', $errors), 400);
        return;
    }
    
    // Extract parameters
    $category = trim($input['category']);
    $reportType = trim($input['reportType']);
    $format = trim($input['reportFormat']);
    $startDate = trim($input['startDate']);
    $endDate = trim($input['endDate']);
    $userId = $_SESSION['user_id'];
    
    // Validate date range
    if (!validateDate($startDate) || !validateDate($endDate)) {
        sendErrorResponse('Invalid date format', 400);
        return;
    }
    
    if (strtotime($startDate) > strtotime($endDate)) {
        sendErrorResponse('Start date must be before end date', 400);
        return;
    }
    
    // Generate unique report ID
    $reportId = uniqid('RPT_');
    $fileName = generateReportFileName($reportType, $format, $startDate, $endDate);
    
    // Insert report record into database
    $reportData = [
        'report_id' => $reportId,
        'user_id' => $userId,
        'category' => $category,
        'report_type' => $reportType,
        'format' => $format,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'status' => 'generating',
        'file_name' => $fileName,
        'parameters' => json_encode($input),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = $db->insert('reports', $reportData);
    
    if (!$result) {
        sendErrorResponse('Failed to create report record');
        return;
    }
    
    // Generate the actual report content
    $reportContent = generateReportContent($category, $reportType, $input, $db);
    
    // Save report file
    $uploadDir = __DIR__ . '/../../uploads/reports/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filePath = $uploadDir . $fileName;
    $success = saveReportFile($filePath, $reportContent, $format);
    
    if ($success) {
        // Update report status to completed
        $updateData = [
            'status' => 'completed',
            'file_path' => $filePath,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $db->update('reports', $updateData, 'report_id = ?', [$reportId]);
        
        sendJSONResponse([
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => [
                'reportId' => $reportId,
                'fileName' => $fileName,
                'downloadUrl' => "/api/reports/download.php?id={$reportId}"
            ]
        ], 201);
    } else {
        // Update report status to failed
        $updateData = [
            'status' => 'failed',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $db->update('reports', $updateData, 'report_id = ?', [$reportId]);
        
        sendErrorResponse('Failed to generate report file');
    }
    
} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    sendErrorResponse('Failed to generate report: ' . $e->getMessage());
}

/**
 * Generate report file name
 */
function generateReportFileName($reportType, $format, $startDate, $endDate) {
    $timestamp = date('Y-m-d_H-i-s');
    $dateRange = date('Y-m-d', strtotime($startDate)) . '_to_' . date('Y-m-d', strtotime($endDate));
    return "{$reportType}_{$dateRange}_{$timestamp}.{$format}";
}

/**
 * Generate report content based on type and parameters
 */
function generateReportContent($category, $reportType, $params, $db) {
    switch ($category) {
        case 'trading':
            return generateTradingReport($reportType, $params, $db);
        case 'operations':
            return generateOperationsReport($reportType, $params, $db);
        case 'risk':
            return generateRiskReport($reportType, $params, $db);
        case 'financial':
            return generateFinancialReport($reportType, $params, $db);
        case 'regulatory':
            return generateRegulatoryReport($reportType, $params, $db);
        default:
            throw new Exception("Unsupported report category: {$category}");
    }
}

/**
 * Generate trading reports
 */
function generateTradingReport($reportType, $params, $db) {
    $startDate = $params['startDate'];
    $endDate = $params['endDate'];
    
    switch ($reportType) {
        case 'trade_summary':
            return generateTradeSummaryReport($startDate, $endDate, $params, $db);
        case 'pnl_report':
        case 'volume_analysis':
        case 'performance_metrics':
        case 'position_report':
            return generateGenericTradingReport($reportType, $startDate, $endDate, $params, $db);
        default:
            throw new Exception("Unsupported trading report type: {$reportType}");
    }
}

/**
 * Generate trade summary report
 */
function generateTradeSummaryReport($startDate, $endDate, $params, $db) {
    $whereConditions = ["DATE(ft.created_at) BETWEEN ? AND ?"];
    $queryParams = [$startDate, $endDate];
    
    // Add filters
    if (!empty($params['commodity'])) {
        $whereConditions[] = "p.category = ?";
        $queryParams[] = $params['commodity'];
    }
    
    if (!empty($params['trader'])) {
        $whereConditions[] = "ft.trader_id = ?";
        $queryParams[] = $params['trader'];
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $query = "
        SELECT 
            ft.trade_id,
            ft.trade_type,
            ft.contract_type,
            p.product_name,
            c.name as counterparty_name,
            ft.quantity,
            ft.price,
            ft.currency,
            (ft.quantity * ft.price) as total_value,
            ft.status,
            ft.settlement_date,
            ft.created_at,
            u.full_name as trader_name
        FROM financial_trades ft
        LEFT JOIN products p ON ft.commodity_id = p.id
        LEFT JOIN counterparties c ON ft.counterparty_id = c.id
        LEFT JOIN users u ON ft.trader_id = u.id
        WHERE {$whereClause}
        ORDER BY ft.created_at DESC
    ";
    
    $trades = $db->fetchAll($query, $queryParams);
    
    // Calculate summary statistics
    $totalTrades = count($trades);
    $totalVolume = array_sum(array_column($trades, 'quantity'));
    $totalValue = array_sum(array_column($trades, 'total_value'));
    
    return [
        'title' => 'Trade Summary Report',
        'period' => "{$startDate} to {$endDate}",
        'summary' => [
            'total_trades' => $totalTrades,
            'total_volume' => $totalVolume,
            'total_value' => $totalValue
        ],
        'trades' => $trades,
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Generate operations reports
 */
function generateOperationsReport($reportType, $params, $db) {
    $startDate = $params['startDate'];
    $endDate = $params['endDate'];
    
    switch ($reportType) {
        case 'invoice_summary':
        case 'settlement_report':
        case 'logistics_tracking':
        case 'operational_metrics':
            return generateGenericOperationsReport($reportType, $startDate, $endDate, $params, $db);
        default:
            throw new Exception("Unsupported operations report type: {$reportType}");
    }
}

/**
 * Generate risk reports
 */
function generateRiskReport($reportType, $params, $db) {
    // Simplified risk report generation
    return [
        'title' => 'Risk Report',
        'type' => $reportType,
        'period' => "{$params['startDate']} to {$params['endDate']}",
        'message' => 'Risk report generation is being implemented',
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Generate financial reports
 */
function generateFinancialReport($reportType, $params, $db) {
    // Simplified financial report generation
    return [
        'title' => 'Financial Report',
        'type' => $reportType,
        'period' => "{$params['startDate']} to {$params['endDate']}",
        'message' => 'Financial report generation is being implemented',
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Generate regulatory reports
 */
function generateRegulatoryReport($reportType, $params, $db) {
    // Simplified regulatory report generation
    return [
        'title' => 'Regulatory Report',
        'type' => $reportType,
        'period' => "{$params['startDate']} to {$params['endDate']}",
        'message' => 'Regulatory report generation is being implemented',
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Save report content to file
 */
function saveReportFile($filePath, $content, $format) {
    try {
        switch ($format) {
            case 'csv':
                return saveCSVReport($filePath, $content);
            case 'pdf':
                return savePDFReport($filePath, $content);
            case 'excel':
                return saveExcelReport($filePath, $content);
            default:
                return false;
        }
    } catch (Exception $e) {
        error_log("Save report file error: " . $e->getMessage());
        return false;
    }
}

/**
 * Save CSV report
 */
function saveCSVReport($filePath, $content) {
    $file = fopen($filePath, 'w');
    if (!$file) return false;
    
    // Write header
    fputcsv($file, ['Report', $content['title']]);
    fputcsv($file, ['Period', $content['period']]);
    fputcsv($file, ['Generated', $content['generated_at']]);
    fputcsv($file, []); // Empty line
    
    // Write data
    if (isset($content['trades']) && !empty($content['trades'])) {
        // Write headers
        $headers = array_keys($content['trades'][0]);
        fputcsv($file, $headers);
        
        // Write data rows
        foreach ($content['trades'] as $row) {
            fputcsv($file, array_values($row));
        }
    }
    
    fclose($file);
    return true;
}

/**
 * Save PDF report (simplified)
 */
function savePDFReport($filePath, $content) {
    // For now, save as text - in production you'd use a PDF library
    $text = "Report: {$content['title']}\n";
    $text .= "Period: {$content['period']}\n";
    $text .= "Generated: {$content['generated_at']}\n\n";
    
    if (isset($content['summary'])) {
        $text .= "Summary:\n";
        foreach ($content['summary'] as $key => $value) {
            $text .= "  {$key}: {$value}\n";
        }
    }
    
    return file_put_contents($filePath, $text) !== false;
}

/**
 * Save Excel report (simplified)
 */
function saveExcelReport($filePath, $content) {
    // For now, save as CSV with .xlsx extension
    // In production, you'd use PhpSpreadsheet or similar
    return saveCSVReport($filePath, $content);
}

/**
 * Generate generic trading report (placeholder)
 */
function generateGenericTradingReport($reportType, $startDate, $endDate, $params, $db) {
    return [
        'title' => ucwords(str_replace('_', ' ', $reportType)),
        'period' => "{$startDate} to {$endDate}",
        'message' => 'This report type is being implemented',
        'generated_at' => date('Y-m-d H:i:s')
    ];
}

/**
 * Generate generic operations report (placeholder)
 */
function generateGenericOperationsReport($reportType, $startDate, $endDate, $params, $db) {
    return [
        'title' => ucwords(str_replace('_', ' ', $reportType)),
        'period' => "{$startDate} to {$endDate}",
        'message' => 'This report type is being implemented',
        'generated_at' => date('Y-m-d H:i:s')
    ];
}
?> 