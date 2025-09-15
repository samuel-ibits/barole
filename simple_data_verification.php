<?php
/**
 * Simple Data Verification
 * Check if required reference data exists without assuming column names
 */

// Load simple session management
require_once 'includes/simple_session.php';

// Only allow logged in users
if (!isUserLoggedIn()) {
    echo "<p><strong>❌ Not logged in!</strong> <a href='login.php'>Please login first</a></p>";
    exit;
}

echo "<h1>Simple Data Verification</h1>";

try {
    require_once 'config/database.php';
    $db = getDB();
    
    echo "<h2>Checking master data...</h2>";
    
    // Check basic table existence and record counts
    $tables = [
        'business_units' => 'Business Units',
        'counterparties' => 'Counterparties', 
        'products' => 'Products',
        'ports' => 'Ports'
    ];
    
    $allGood = true;
    
    foreach ($tables as $table => $label) {
        try {
            $result = $db->query("SELECT COUNT(*) as count FROM {$table}")->fetch();
            $count = $result['count'];
            
            if ($count > 0) {
                echo "<p>✅ <strong>{$label}:</strong> {$count} records</p>";
                
                // Show sample data for first few records
                $samples = $db->query("SELECT * FROM {$table} LIMIT 3")->fetchAll();
                if (!empty($samples)) {
                    echo "<details><summary>Sample {$label} Data</summary>";
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>";
                    
                    // Header row
                    echo "<tr>";
                    foreach (array_keys($samples[0]) as $column) {
                        echo "<th style='padding: 5px; background: #f5f5f5;'>{$column}</th>";
                    }
                    echo "</tr>";
                    
                    // Data rows
                    foreach ($samples as $row) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            $displayValue = $value !== null ? htmlspecialchars(substr($value, 0, 50)) : 'NULL';
                            echo "<td style='padding: 5px;'>{$displayValue}</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table></details>";
                }
            } else {
                echo "<p>❌ <strong>{$label}:</strong> No records found</p>";
                $allGood = false;
            }
        } catch (Exception $e) {
            echo "<p>❌ <strong>{$label}:</strong> Error - " . htmlspecialchars($e->getMessage()) . "</p>";
            $allGood = false;
        }
    }
    
    // Test API-specific requirements
    echo "<h2>API Requirements Check</h2>";
    
    // Check if we have required IDs for testing
    try {
        $bu = $db->query("SELECT id, business_unit_name FROM business_units LIMIT 1")->fetch();
        $cp = $db->query("SELECT id, name FROM counterparties LIMIT 1")->fetch();
        $prod = $db->query("SELECT id, product_name FROM products LIMIT 1")->fetch();
        
        if ($bu && $cp && $prod) {
            echo "<p>✅ <strong>API Test Data Available:</strong></p>";
            echo "<ul>";
            echo "<li>Business Unit: ID {$bu['id']} - {$bu['business_unit_name']}</li>";
            echo "<li>Counterparty: ID {$cp['id']} - {$cp['name']}</li>";
            echo "<li>Product: ID {$prod['id']} - {$prod['product_name']}</li>";
            echo "</ul>";
            
            // Test physical sales API with these IDs
            echo "<h3>API Test with Real Data</h3>";
            echo "<form method='POST' action='api/trading/physical-sales.php' target='_blank'>";
            echo "<input type='hidden' name='sale_id' value='VERIFY_" . date('YmdHis') . "'>";
            echo "<input type='hidden' name='counterparty_id' value='{$cp['id']}'>";
            echo "<input type='hidden' name='product_id' value='{$prod['id']}'>";
            echo "<input type='hidden' name='business_unit_id' value='{$bu['id']}'>";
            echo "<input type='hidden' name='quantity' value='1000'>";
            echo "<input type='hidden' name='price' value='75.50'>";
            echo "<input type='hidden' name='currency' value='USD'>";
            echo "<input type='hidden' name='delivery_date' value='" . date('Y-m-d', strtotime('+30 days')) . "'>";
            echo "<input type='hidden' name='status' value='draft'>";
            echo "<input type='hidden' name='notes' value='Verification test'>";
            echo "<button type='submit' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px;'>🧪 Test Physical Sales API</button>";
            echo "</form>";
            echo "<p><small>This will open in a new tab and should return JSON success message</small></p>";
            
        } else {
            echo "<p>❌ <strong>Missing required reference data for API testing</strong></p>";
            $allGood = false;
        }
    } catch (Exception $e) {
        echo "<p>❌ <strong>API Requirements Check Failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        $allGood = false;
    }
    
    // Overall status
    echo "<h2>Overall Status</h2>";
    if ($allGood) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
        echo "<h3>✅ All Systems Ready!</h3>";
        echo "<p>Your database has all the required reference data. APIs should work correctly now.</p>";
        echo "<p><strong>Next Steps:</strong></p>";
        echo "<ul>";
        echo "<li><a href='debug_create_record.php'>Test Debug Script</a> - Should now pass all tests</li>";
        echo "<li><a href='test_data_flow.php'>Test Data Flow</a> - Try creating records</li>";
        echo "<li><a href='index.php'>Visit Dashboard</a> - See your data in action</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0;'>";
        echo "<h3>❌ Issues Found</h3>";
        echo "<p>Some reference data is missing or there are configuration issues.</p>";
        echo "<p>Check the errors above and fix them before testing APIs.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Database Connection Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3 { color: #333; }
table { margin: 10px 0; font-size: 12px; }
th, td { padding: 5px; border: 1px solid #ddd; text-align: left; }
details { margin: 10px 0; }
summary { cursor: pointer; font-weight: bold; color: #007bff; }
summary:hover { text-decoration: underline; }
button { cursor: pointer; }
button:hover { opacity: 0.9; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style> 