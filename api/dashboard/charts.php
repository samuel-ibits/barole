<?php
/**
 * Dashboard Charts API
 * Get chart data for dashboard
 */

// Load simple session management  
require_once __DIR__ . '/../../includes/simple_session.php';

// Load database configuration
require_once __DIR__ . '/../../config/database.php';

// Require authentication
requireLogin();

try {
    $db = getDB();
    
    // Get chart data
    $charts = [
        'portfolio' => [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'datasets' => [
                [
                    'label' => 'Portfolio Value',
                    'data' => [45.2, 46.1, 47.8, 46.9, 48.2],
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)'
                ]
            ]
        ],
        'risk' => [
            'labels' => ['Low', 'Medium', 'High'],
            'datasets' => [
                [
                    'data' => [60, 30, 10],
                    'backgroundColor' => ['#28a745', '#ffc107', '#dc3545']
                ]
            ]
        ],
        'trading' => [
            'labels' => ['Physical', 'Financial', 'FX'],
            'datasets' => [
                [
                    'label' => 'Trade Volume',
                    'data' => [45, 30, 25],
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56']
                ]
            ]
        ]
    ];
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $charts
    ];
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    error_log("Dashboard charts API error: " . $e->getMessage());
    sendErrorResponse('Failed to load chart data');
}
?> 