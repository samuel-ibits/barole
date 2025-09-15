<?php
/**
 * Session Check API
 * Check if user session is valid
 */

// Load configuration
require_once __DIR__ . '/../../config/app.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (isLoggedIn()) {
    sendSuccessResponse(['valid' => true, 'user' => [
        'id' => getCurrentUserId(),
        'username' => getCurrentUsername(),
        'role' => getCurrentUserRole()
    ]]);
} else {
    sendSuccessResponse(['valid' => false]);
}
?> 