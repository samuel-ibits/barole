<?php
/**
 * Dashboard Widgets API
 * Get dashboard widget data
 */

// Prevent any HTML output from errors
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header immediately
header('Content-Type: application/json');

try {
    // Load simple session management  
    require_once __DIR__ . '/../../includes/simple_session.php';

    // Load database configuration
    require_once __DIR__ . '/../../config/database.php';

    // Require authentication
    requireLogin();

    // Sample dashboard widget data
    $widgets = [
        [
            'title' => 'Total Trades',
            'value' => '1,234',
            'change' => 5.2,
            'icon' => 'bi-graph-up'
        ],
        [
            'title' => 'Portfolio Value',
            'value' => '$45.2M',
            'change' => -2.1,
            'icon' => 'bi-currency-dollar'
        ],
        [
            'title' => 'Risk Level',
            'value' => 'Medium',
            'change' => 0,
            'icon' => 'bi-shield-check'
        ],
        [
            'title' => 'Active Alerts',
            'value' => '3',
            'change' => 50,
            'icon' => 'bi-exclamation-triangle'
        ]
    ];
    
    sendSuccessResponse($widgets);
    
} catch (Exception $e) {
    error_log("Dashboard widgets error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dashboard widgets: ' . $e->getMessage()]);
}
?> 