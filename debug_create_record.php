<?php
/**
 * Debug Record Creation
 * Find out why record creation is failing
 */

// Load simple session management
require_once 'includes/simple_session.php';

echo "<h1>Debug Record Creation</h1>";

// Check if user is logged in
if (!isUserLoggedIn()) {
    echo "<p><strong>❌ Not logged in!</strong> <a href='login.php'>Please login first</a></p>";
    exit;
}

echo "<p>✅ User logged in: " . getCurrentUsername() . " (Role: " . getCurrentUserRole() . ")</p>";

// Test database connection
try {
    require_once 'config/database.php';
    $db = getDB();
    
    echo "<h2>Database Connection Test</h2>";
    $result = $db->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "<p>✅ Database connected successfully</p>";
    echo "<p>Total users: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// Test specific table structures
echo "<h2>Database Schema Verification</h2>";

$tables = ['physical_sales', 'counterparties', 'products', 'business_units'];
foreach ($tables as $table) {
    try {
        echo "<h3>{$table} Table Structure:</h3>";
        
        // Get table structure
        $structure = $db->query("DESCRIBE {$table}")->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        foreach ($structure as $column) {
            $nullText = $column['Null'] === 'YES' ? 'NULL OK' : 'NOT NULL';
            $keyText = $column['Key'] ?: '-';
            $defaultText = $column['Default'] !== null ? $column['Default'] : 'NULL';
            
            echo "<tr>";
            echo "<td><strong>{$column['Field']}</strong></td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$nullText}</td>";
            echo "<td>{$keyText}</td>";
            echo "<td>{$defaultText}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test simple insert
        if ($table === 'physical_sales') {
            echo "<h4>Testing Physical Sales Insert:</h4>";
            
            // First, ensure we have required data
            $counterparties = $db->query("SELECT id, name FROM counterparties LIMIT 1")->fetch();
            $products = $db->query("SELECT id, product_name FROM products LIMIT 1")->fetch();
            $business_units = $db->query("SELECT id, business_unit_name FROM business_units LIMIT 1")->fetch();
            
            if (!$counterparties) {
                echo "<p>❌ No counterparties found - need sample data</p>";
                continue;
            }
            if (!$products) {
                echo "<p>❌ No products found - need sample data</p>";
                continue;
            }
            if (!$business_units) {
                echo "<p>❌ No business units found - need sample data</p>";
                continue;
            }
            
            echo "<p>✅ Required reference data exists:</p>";
            echo "<ul>";
            echo "<li>Counterparty: {$counterparties['name']} (ID: {$counterparties['id']})</li>";
            echo "<li>Product: {$products['product_name']} (ID: {$products['id']})</li>";
            echo "<li>Business Unit: {$business_units['business_unit_name']} (ID: {$business_units['id']})</li>";
            echo "</ul>";
            
            // Test insert with minimal data
            $testSaleId = 'DEBUG_' . date('YmdHis');
            $testData = [
                'sale_id' => $testSaleId,
                'product_id' => $products['id'],
                'quantity' => 1000.0000,
                'price' => 75.50,
                'currency' => 'USD',
                'delivery_date' => date('Y-m-d', strtotime('+30 days')),
                'counterparty_id' => $counterparties['id'],
                'business_unit_id' => $business_units['id'],
                'trader_id' => getCurrentUserId(),
                'status' => 'draft'
            ];
            
            echo "<h4>Test Data to Insert:</h4>";
            echo "<pre>" . print_r($testData, true) . "</pre>";
            
            try {
                // Use the database insert method
                $newId = $db->insert('physical_sales', $testData);
                
                if ($newId) {
                    echo "<p>✅ <strong>INSERT SUCCESSFUL!</strong> New ID: {$newId}</p>";
                    
                    // Verify the record was created
                    $verify = $db->query("SELECT * FROM physical_sales WHERE id = ?", [$newId])->fetch();
                    if ($verify) {
                        echo "<p>✅ Record verification successful</p>";
                        echo "<details><summary>Inserted Record</summary><pre>" . print_r($verify, true) . "</pre></details>";
                        
                        // Clean up test record
                        $db->query("DELETE FROM physical_sales WHERE id = ?", [$newId]);
                        echo "<p>🧹 Test record cleaned up</p>";
                    } else {
                        echo "<p>❌ Record not found after insert</p>";
                    }
                } else {
                    echo "<p>❌ <strong>INSERT FAILED!</strong> No new ID returned</p>";
                }
                
            } catch (Exception $e) {
                echo "<p>❌ <strong>INSERT ERROR:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>Error Details:</strong></p>";
                echo "<pre>";
                echo "Error Code: " . $e->getCode() . "\n";
                echo "Error Message: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . "\n";
                echo "Line: " . $e->getLine() . "\n";
                echo "</pre>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error checking {$table}: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Test the database insert method directly
echo "<h2>Database Insert Method Test</h2>";

try {
    // Check if our database class has the insert method
    echo "<p>Database class: " . get_class($db) . "</p>";
    echo "<p>Available methods: " . implode(', ', get_class_methods($db)) . "</p>";
    
    // Test a simple insert into a test table or existing table
    echo "<h4>Testing Database Insert Method:</h4>";
    
    // Simple test with user_activity_logs which should always work
    $testLogData = [
        'user_id' => getCurrentUserId(),
        'action' => 'debug_test',
        'details' => 'Testing database insert method',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    echo "<p>Testing with user_activity_logs table:</p>";
    echo "<pre>" . print_r($testLogData, true) . "</pre>";
    
    $logId = $db->insert('user_activity_logs', $testLogData);
    
    if ($logId) {
        echo "<p>✅ <strong>Database insert method works!</strong> Log ID: {$logId}</p>";
    } else {
        echo "<p>❌ Database insert method failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database insert method error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test API call simulation
echo "<h2>API Call Simulation</h2>";

if (isset($_POST['test_api'])) {
    echo "<h4>Testing Physical Sales API Call:</h4>";
    
    // Simulate the API call
    $_POST['sale_id'] = 'TEST_API_' . date('YmdHis');
    $_POST['counterparty_id'] = '1';
    $_POST['product_id'] = '1';
    $_POST['quantity'] = '1000';
    $_POST['price'] = '75.50';
    $_POST['currency'] = 'USD';
    $_POST['delivery_date'] = date('Y-m-d', strtotime('+30 days'));
    $_POST['status'] = 'draft';
    $_POST['notes'] = 'API test record';
    
    echo "<p>Simulating POST data:</p>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Capture output
    ob_start();
    
    try {
        // Include the API file
        include 'api/trading/physical-sales.php';
    } catch (Exception $e) {
        echo "<p>❌ API Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    $apiOutput = ob_get_clean();
    
    echo "<h5>API Response:</h5>";
    echo "<pre>" . htmlspecialchars($apiOutput) . "</pre>";
    
    // Try to decode JSON
    $apiData = json_decode($apiOutput, true);
    if ($apiData) {
        echo "<h5>Parsed API Response:</h5>";
        echo "<pre>" . print_r($apiData, true) . "</pre>";
        
        if (isset($apiData['success']) && $apiData['success']) {
            echo "<p>✅ <strong>API CALL SUCCESSFUL!</strong></p>";
        } else {
            echo "<p>❌ <strong>API CALL FAILED:</strong> " . ($apiData['error'] ?? $apiData['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p>❌ API returned invalid JSON</p>";
    }
}

?>

<h2>Manual API Test</h2>
<form method="POST">
    <button type="submit" name="test_api" value="1">Test Physical Sales API</button>
</form>

<h2>Check Error Logs</h2>
<?php
$errorLog = 'logs/php_errors.log';
if (file_exists($errorLog)) {
    $errors = file_get_contents($errorLog);
    $recentErrors = array_slice(explode("\n", $errors), -20); // Last 20 lines
    
    echo "<h4>Recent PHP Errors (last 20 lines):</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: scroll;'>";
    echo htmlspecialchars(implode("\n", $recentErrors));
    echo "</pre>";
} else {
    echo "<p>No error log file found</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3, h4 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #f5f5f5; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; }
details { margin: 10px 0; }
button { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #0056b3; }
</style> 