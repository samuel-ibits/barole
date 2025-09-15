<?php
/**
 * Test APIs to identify JSON response issues
 */

// Load simple session management
require_once 'includes/simple_session.php';

if (!isUserLoggedIn()) {
    echo "<p><strong>❌ Not logged in!</strong> <a href='login.php'>Please login first</a></p>";
    exit;
}

echo "<h1>API Response Test</h1>";

$apis = [
    'Physical Sales' => 'api/trading/physical-sales.php',
    'Financial Trades' => 'api/trading/financial-trades.php',
    'FX Trades' => 'api/trading/fx-trades.php',
    'Invoices' => 'api/operations/invoices.php',
    'Logistics' => 'api/operations/logistics.php',
    'Settlements' => 'api/operations/settlements.php',
    'Counterparties' => 'api/master-data/counterparties.php',
    'Products' => 'api/master-data/products.php',
    'Business Units' => 'api/master-data/business-units.php',
    'Brokers' => 'api/master-data/brokers.php',
    'Ports' => 'api/master-data/ports.php',
    'Carriers' => 'api/master-data/carriers.php',
    'Portfolio' => 'api/risk-analytics/portfolio.php',
    'Alerts' => 'api/risk-analytics/alerts.php',
    'Users' => 'api/users/list.php'
];

echo "<h2>GET Request Tests</h2>";

foreach ($apis as $name => $endpoint) {
    echo "<h3>Testing {$name}</h3>";
    
    // Test GET request
    ob_start();
    
    try {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        include $endpoint;
        $response = ob_get_clean();
        
        // Check if it's valid JSON
        $json = json_decode($response, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ <strong>Valid JSON Response</strong></p>";
            echo "<details><summary>Response Preview</summary>";
            echo "<pre style='max-height: 200px; overflow-y: auto;'>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
            echo "</details>";
        } else {
            echo "<p>❌ <strong>Invalid JSON Response</strong></p>";
            echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
            echo "<details><summary>Raw Response</summary>";
            echo "<pre style='max-height: 200px; overflow-y: auto; background: #ffeeee;'>" . htmlspecialchars($response) . "</pre>";
            echo "</details>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p>❌ <strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (ParseError $e) {
        ob_end_clean();
        echo "<p>❌ <strong>Parse Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Error $e) {
        ob_end_clean();
        echo "<p>❌ <strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
}

echo "<h2>POST Request Test (Physical Sales)</h2>";

// Test a POST request to see if create is working
echo "<h3>Testing Physical Sales CREATE</h3>";

try {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'trade_id' => 'TEST_' . date('ymdHis'),
        'counterparty_id' => '1',
        'product_id' => '1',
        'quantity' => '100',
        'price' => '75.50',
        'currency' => 'USD',
        'delivery_date' => date('Y-m-d', strtotime('+30 days')),
        'status' => 'pending'
    ];
    
    ob_start();
    include 'api/trading/physical-sales.php';
    $response = ob_get_clean();
    
    $json = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p>✅ <strong>Valid JSON Response</strong></p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        if (isset($json['success']) && $json['success']) {
            echo "<p>✅ <strong>CREATE SUCCESSFUL!</strong></p>";
        } else {
            echo "<p>❌ <strong>CREATE FAILED:</strong> " . ($json['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p>❌ <strong>Invalid JSON Response in CREATE</strong></p>";
        echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
        echo "<pre style='background: #ffeeee;'>" . htmlspecialchars($response) . "</pre>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p>❌ <strong>Exception in CREATE:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Clear POST data
$_POST = [];
$_SERVER['REQUEST_METHOD'] = 'GET';

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3 { color: #333; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; }
details { margin: 10px 0; }
summary { cursor: pointer; font-weight: bold; color: #007bff; }
summary:hover { text-decoration: underline; }
hr { margin: 20px 0; border: 1px solid #ddd; }
</style> 