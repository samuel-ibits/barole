<?php
/**
 * ETRM System - Logout Page
 * User logout and session cleanup - Simplified Version
 */

// Start session to access session variables
session_start();

// Log the logout activity if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        require_once 'config/database.php';
        $db = getDB();
        
        // Try to log the logout activity
        $userId = $_SESSION['user_id'];
        $logQuery = "INSERT INTO user_activity_logs (user_id, action, details, ip_address, created_at) 
                     VALUES (?, ?, ?, ?, ?)";
        $db->query($logQuery, [
            $userId, 
            'logout', 
            'User logged out successfully',
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Continue with logout even if logging fails
        error_log("Failed to log logout activity: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php?logged_out=1');
exit;
?> 