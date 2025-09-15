<?php
/**
 * Schedule Report API
 * Schedule recurring report generation
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
    $userId = $_SESSION['user_id'];
    
    // Validate required fields
    $errors = [];
    $requiredFields = ['name', 'frequency', 'time', 'email'];
    
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
    $name = trim($input['name']);
    $frequency = trim($input['frequency']);
    $time = trim($input['time']);
    $email = trim($input['email']);
    
    // Validate frequency
    if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'quarterly'])) {
        sendErrorResponse('Invalid frequency. Must be: daily, weekly, monthly, or quarterly', 400);
        return;
    }
    
    // Validate time format
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        sendErrorResponse('Invalid time format. Use HH:MM format', 400);
        return;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendErrorResponse('Invalid email address', 400);
        return;
    }
    
    // Generate unique schedule ID
    $scheduleId = uniqid('SCH_');
    
    // Insert schedule record into database
    $scheduleData = [
        'schedule_id' => $scheduleId,
        'user_id' => $userId,
        'name' => $name,
        'frequency' => $frequency,
        'execution_time' => $time,
        'email_recipients' => $email,
        'status' => 'active',
        'next_execution' => calculateNextExecution($frequency, $time),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = $db->insert('report_schedules', $scheduleData);
    
    if ($result) {
        sendJSONResponse([
            'success' => true,
            'message' => 'Report schedule created successfully',
            'data' => [
                'scheduleId' => $scheduleId,
                'nextExecution' => $scheduleData['next_execution']
            ]
        ], 201);
    } else {
        sendErrorResponse('Failed to create report schedule');
    }
    
} catch (Exception $e) {
    error_log("Schedule report API error: " . $e->getMessage());
    sendErrorResponse('Failed to schedule report: ' . $e->getMessage());
}

/**
 * Calculate next execution time based on frequency
 */
function calculateNextExecution($frequency, $time) {
    $now = new DateTime();
    $executionTime = DateTime::createFromFormat('H:i', $time);
    
    switch ($frequency) {
        case 'daily':
            $next = new DateTime('tomorrow ' . $time);
            break;
        case 'weekly':
            $next = new DateTime('next monday ' . $time);
            break;
        case 'monthly':
            $next = new DateTime('first day of next month ' . $time);
            break;
        case 'quarterly':
            $currentMonth = (int)$now->format('n');
            $nextQuarterMonth = (ceil($currentMonth / 3) * 3) + 1;
            if ($nextQuarterMonth > 12) {
                $nextQuarterMonth = 1;
                $year = $now->format('Y') + 1;
            } else {
                $year = $now->format('Y');
            }
            $next = new DateTime("{$year}-{$nextQuarterMonth}-01 {$time}");
            break;
        default:
            $next = new DateTime('tomorrow ' . $time);
    }
    
    return $next->format('Y-m-d H:i:s');
}
?> 