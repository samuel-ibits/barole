<?php
/**
 * Portfolio API
 * Get portfolio data for risk analytics module
 */

require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');

requireAuth();

try {
    $db = getDB();
    
    // Get portfolio positions
    $query = "
        SELECT 
            pp.*,
            p.product_name
        FROM portfolio_positions pp
        LEFT JOIN products p ON pp.product_id = p.id
        ORDER BY pp.created_at DESC
    ";
    
    $stmt = $db->query($query);
    $positions = $stmt->fetchAll();
    
    // Calculate portfolio metrics
    $totalValue = 0;
    $totalPnL = 0;
    $positionCount = count($positions);
    
    foreach ($positions as $position) {
        $marketValue = $position['quantity'] * $position['current_price'];
        $totalValue += $marketValue;
        $totalPnL += $position['pnl'] ?? 0;
    }
    
    // Format data for frontend
    $formattedPositions = [];
    foreach ($positions as $position) {
        $marketValue = $position['quantity'] * $position['current_price'];
        $formattedPositions[] = [
            'id' => $position['id'],
            'product_name' => $position['product_name'],
            'quantity' => number_format($position['quantity'], 2),
            'average_price' => number_format($position['average_price'], 2),
            'current_price' => number_format($position['current_price'], 2),
            'market_value' => number_format($marketValue, 2),
            'pnl' => number_format($position['pnl'], 2),
            'created_at' => date('Y-m-d H:i', strtotime($position['created_at']))
        ];
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'positions' => $formattedPositions,
            'metrics' => [
                'total_value' => number_format($totalValue, 2),
                'total_pnl' => number_format($totalPnL, 2),
                'position_count' => $positionCount
            ]
        ]
    ];
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    error_log("Portfolio API error: " . $e->getMessage());
    sendErrorResponse('Failed to load portfolio data');
}
?> 