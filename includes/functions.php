<?php
/**
 * Core Utility Functions
 * ETRM System - Common helper functions
 */

// Load configuration
require_once CONFIG_PATH . '/app.php';

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * Generate secure password
 */
function generateSecurePassword($length = 12) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    $password = '';
    
    // Ensure at least one character from each category
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $symbols[rand(0, strlen($symbols) - 1)];
    
    // Fill the rest with random characters
    $allChars = $uppercase . $lowercase . $numbers . $symbols;
    for ($i = 4; $i < $length; $i++) {
        $password .= $allChars[rand(0, strlen($allChars) - 1)];
    }
    
    // Shuffle the password to make it more random
    return str_shuffle($password);
}

/**
 * Generate unique trade ID
 */
function generateTradeID($prefix) {
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(md5(uniqid()), 0, 4));
    return $prefix . $timestamp . $random;
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    return number_format($amount, 2) . ' ' . $currency;
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Get user-friendly status label
 */
function getStatusLabel($status, $type = 'trade') {
    $labels = [
        'trade' => [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'confirmed' => '<span class="badge bg-info">Confirmed</span>',
            'executed' => '<span class="badge bg-primary">Executed</span>',
            'settled' => '<span class="badge bg-success">Settled</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
        ],
        'invoice' => [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'sent' => '<span class="badge bg-info">Sent</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'cancelled' => '<span class="badge bg-dark">Cancelled</span>'
        ],
        'logistics' => [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'in_transit' => '<span class="badge bg-info">In Transit</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
        ],
        'settlement' => [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'processed' => '<span class="badge bg-info">Processed</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>'
        ],
        'user' => [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'suspended' => '<span class="badge bg-danger">Suspended</span>'
        ],
        'alert' => [
            'active' => '<span class="badge bg-warning">Active</span>',
            'acknowledged' => '<span class="badge bg-info">Acknowledged</span>',
            'resolved' => '<span class="badge bg-success">Resolved</span>'
        ]
    ];
    
    return $labels[$type][$status] ?? '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Get severity label
 */
function getSeverityLabel($severity) {
    $labels = [
        'low' => '<span class="badge bg-success">Low</span>',
        'medium' => '<span class="badge bg-warning">Medium</span>',
        'high' => '<span class="badge bg-danger">High</span>',
        'critical' => '<span class="badge bg-dark">Critical</span>'
    ];
    
    return $labels[$severity] ?? '<span class="badge bg-secondary">' . ucfirst($severity) . '</span>';
}

/**
 * Log user activity
 */
function logUserActivity($userId, $action, $details = '', $ipAddress = null) {
    try {
        $db = getDB();
        $ipAddress = $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? '';
        
        $db->insert('user_activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ipAddress
        ]);
    } catch (Exception $e) {
        error_log("Failed to log user activity: " . $e->getMessage());
    }
}

/**
 * Log audit trail
 */
function logAuditTrail($userId, $action, $tableName, $recordId, $oldValues = null, $newValues = null) {
    try {
        $db = getDB();
        
        $db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Failed to log audit trail: " . $e->getMessage());
    }
}

/**
 * Get system setting
 */
function getSystemSetting($key, $default = null) {
    try {
        $db = getDB();
        $result = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        error_log("Failed to get system setting: " . $e->getMessage());
        return $default;
    }
}

/**
 * Set system setting
 */
function setSystemSetting($key, $value) {
    try {
        $db = getDB();
        $existing = $db->fetchOne("SELECT id FROM system_settings WHERE setting_key = ?", [$key]);
        
        if ($existing) {
            $db->update('system_settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            $db->insert('system_settings', [
                'setting_key' => $key,
                'setting_value' => $value
            ]);
        }
        return true;
    } catch (Exception $e) {
        error_log("Failed to set system setting: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file parameter'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error'];
    }
    
    $maxSize = $maxSize ?: MAX_FILE_SIZE;
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds maximum allowed size'];
    }
    
    $allowedTypes = $allowedTypes ?: ALLOWED_FILE_TYPES;
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['valid' => false, 'error' => 'File type not allowed'];
    }
    
    return ['valid' => true];
}

/**
 * Upload file
 */
function uploadFile($file, $destination = null) {
    $validation = validateFileUpload($file);
    if (!$validation['valid']) {
        return $validation;
    }
    
    $destination = $destination ?: UPLOADS_PATH . '/' . date('Y/m/d');
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'valid' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => $file['size']
        ];
    }
    
    return ['valid' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Generate pagination
 */
function generatePagination($totalRecords, $currentPage, $pageSize, $baseUrl) {
    $totalPages = ceil($totalRecords / $pageSize);
    $currentPage = max(1, min($currentPage, $totalPages));
    
    $pagination = [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_records' => $totalRecords,
        'page_size' => $pageSize,
        'offset' => ($currentPage - 1) * $pageSize,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
    
    // Generate page links
    $pagination['pages'] = [];
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $pagination['pages'][] = [
            'number' => $i,
            'url' => $baseUrl . '?page=' . $i,
            'active' => $i === $currentPage
        ];
    }
    
    return $pagination;
}

/**
 * Send JSON response
 */
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJSONResponse(['error' => $message], $statusCode);
}

/**
 * Send success response
 */
function sendSuccessResponse($data = null, $message = 'Success') {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    sendJSONResponse($response);
}

/**
 * Check if request is AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Calculate date difference
 */
function dateDifference($date1, $date2, $format = '%a') {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->format($format);
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Clean old sessions
 */
function cleanOldSessions() {
    try {
        $db = getDB();
        $db->query("DELETE FROM user_sessions WHERE expires_at < NOW()");
    } catch (Exception $e) {
        error_log("Failed to clean old sessions: " . $e->getMessage());
    }
}

/**
 * Clean old logs
 */
function cleanOldLogs($days = 30) {
    try {
        $db = getDB();
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $db->query("DELETE FROM user_activity_logs WHERE created_at < ?", [$cutoffDate]);
        $db->query("DELETE FROM audit_logs WHERE created_at < ?", [$cutoffDate]);
    } catch (Exception $e) {
        error_log("Failed to clean old logs: " . $e->getMessage());
    }
}