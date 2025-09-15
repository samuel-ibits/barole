<?php
/**
 * Simple Session Management
 * ETRM System - Basic session handling that works without header issues
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user has specific role
 */
function hasUserRole($role) {
    if (!isUserLoggedIn()) {
        return false;
    }
    
    $userRole = getCurrentUserRole();
    
    // Admin has all permissions
    if ($userRole === 'admin') {
        return true;
    }
    
    return $userRole === $role;
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isUserLoggedIn()) {
        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Return JSON error for AJAX requests
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            exit;
        } else {
            // Redirect to login page for regular requests
            header('Location: login.php');
            exit;
        }
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasUserRole($role)) {
        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Return JSON error for AJAX requests
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        } else {
            // Redirect to error page for regular requests
            header('Location: login.php?error=insufficient_permissions');
            exit;
        }
    }
}

/**
 * Check session timeout (1 hour default)
 */
function checkSessionTimeout($timeout = 3600) {
    if (isUserLoggedIn() && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout) {
            // Session expired
            session_destroy();
            session_start();
            
            // Redirect to login with timeout message
            header('Location: login.php?error=session_expired');
            exit;
        }
    }
}

/**
 * Log user activity (simplified)
 */
function logUserActivity($action, $details = '') {
    $userId = getCurrentUserId();
    if (!$userId) return;
    
    try {
        require_once __DIR__ . '/../config/database.php';
        $db = getDB();
        
        $logQuery = "INSERT INTO user_activity_logs (user_id, action, details, ip_address, created_at) 
                     VALUES (?, ?, ?, ?, ?)";
        $db->query($logQuery, [
            $userId, 
            $action, 
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        error_log("Failed to log user activity: " . $e->getMessage());
    }
}

/**
 * Send JSON response helper
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
?> 