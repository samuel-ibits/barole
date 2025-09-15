<?php
/**
 * Debug SQL Error
 * Find the exact SQL error when creating physical sales
 */

// Load simple session management
require_once 'includes/simple_session.php';

// Only allow logged in users
if (!isUserLoggedIn()) {
    echo "<p><strong>❌ Not logged in!</strong> <a href='login.php'>Please login first</a></p>";
    exit;
}

echo "<h1>Debug SQL Error</h1>";

try {
    require_once 'config/database.php';
    $db = getDB();
    
    echo "<h2>1. Physical Sales Table Structure</h2>";
    
    // Check the actual table structure
    try {
        $structure = $db->query("DESCRIBE physical_sales")->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($structure as $column) {
            $keyValue = !empty($column['Key']) ? $column['Key'] : '-';
            $defaultValue = ($column['Default'] !== null) ? $column['Default'] : 'NULL';
            $extraValue = !empty($column['Extra']) ? $column['Extra'] : '-';
            
            echo "<tr>";
            echo "<td><strong>{$column['Field']}</strong></td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$keyValue}</td>";
            echo "<td>{$defaultValue}</td>";
            echo "<td>{$extraValue}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>❌ Error getting table structure: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<h2>2. Reference Data Check</h2>";
    
    // Get sample reference data
    $bu = $db->query("SELECT id, business_unit_name FROM business_units LIMIT 1")->fetch();
    $cp = $db->query("SELECT id, name FROM counterparties LIMIT 1")->fetch();
    $prod = $db->query("SELECT id, product_name FROM products LIMIT 1")->fetch();
    
    if (!$bu || !$cp || !$prod) {
        echo "<p>❌ Missing reference data!</p>";
        exit;
    }
    
    echo "<p>✅ Reference data available:</p>";
    echo "<ul>";
    echo "<li>Business Unit: ID {$bu['id']} - {$bu['business_unit_name']}</li>";
    echo "<li>Counterparty: ID {$cp['id']} - {$cp['name']}</li>";
    echo "<li>Product: ID {$prod['id']} - {$prod['product_name']}</li>";
    echo "<li>User ID: " . getCurrentUserId() . "</li>";
    echo "</ul>";
    
    echo "<h2>3. Test Insert with Detailed Error Reporting</h2>";
    
    // Test data (sale_id max 20 chars)
    $testSaleId = 'DBG' . date('ymdHis'); // DBG2501261602 = 12 chars
    $testData = [
        'sale_id' => $testSaleId,
        'product_id' => $prod['id'],
        'quantity' => 1000.0000,
        'price' => 75.50,
        'currency' => 'USD',
        'delivery_date' => date('Y-m-d', strtotime('+30 days')),
        'counterparty_id' => $cp['id'],
        'business_unit_id' => $bu['id'],
        'trader_id' => getCurrentUserId(),
        'status' => 'draft'
    ];
    
    echo "<h4>Test Data:</h4>";
    echo "<pre>" . print_r($testData, true) . "</pre>";
    
    // Method 1: Test our database insert method
    echo "<h4>Method 1: Using Database::insert()</h4>";
    try {
        $newId = $db->insert('physical_sales', $testData);
        
        if ($newId) {
            echo "<p>✅ <strong>SUCCESS!</strong> Insert worked, new ID: {$newId}</p>";
            
            // Clean up
            $db->query("DELETE FROM physical_sales WHERE id = ?", [$newId]);
            echo "<p>🧹 Test record cleaned up</p>";
        } else {
            echo "<p>❌ Insert returned false (no ID)</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ <strong>Database::insert() Error:</strong></p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border-radius: 4px;'>";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Code: " . $e->getCode() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "</pre>";
    }
    
    // Method 2: Test direct PDO
    echo "<h4>Method 2: Direct PDO Insert</h4>";
    try {
        $columns = array_keys($testData);
        $placeholders = ':' . implode(', :', $columns);
        $columnList = implode(', ', $columns);
        
        $sql = "INSERT INTO physical_sales ({$columnList}) VALUES ({$placeholders})";
        echo "<p><strong>SQL:</strong> <code>{$sql}</code></p>";
        
        // Prepare named parameters
        $namedParams = [];
        foreach ($testData as $key => $value) {
            $namedParams[':' . $key] = $value;
        }
        
        echo "<p><strong>Parameters:</strong></p>";
        echo "<pre>" . print_r($namedParams, true) . "</pre>";
        
        $stmt = $db->getConnection()->prepare($sql);
        $result = $stmt->execute($namedParams);
        
        if ($result) {
            $newId = $db->getConnection()->lastInsertId();
            echo "<p>✅ <strong>SUCCESS!</strong> Direct PDO insert worked, new ID: {$newId}</p>";
            
            // Clean up
            $db->query("DELETE FROM physical_sales WHERE id = ?", [$newId]);
            echo "<p>🧹 Test record cleaned up</p>";
        } else {
            echo "<p>❌ PDO execute returned false</p>";
            
            // Get error info
            $errorInfo = $stmt->errorInfo();
            echo "<p><strong>PDO Error Info:</strong></p>";
            echo "<pre>" . print_r($errorInfo, true) . "</pre>";
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ <strong>Direct PDO Error:</strong></p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border-radius: 4px;'>";
        echo "SQLSTATE: " . $e->getCode() . "\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p>❌ <strong>General Error:</strong></p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border-radius: 4px;'>";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Code: " . $e->getCode() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "</pre>";
    }
    
    // Method 3: Test simple manual insert
    echo "<h4>Method 3: Simple Manual Insert</h4>";
    try {
        $simpleSQL = "INSERT INTO physical_sales (sale_id, product_id, quantity, price, currency, delivery_date, counterparty_id, business_unit_id, trader_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $simpleParams = [
            'SMP' . date('ymdHis'), // SMP2501261602 = 12 chars
            $prod['id'],
            1000.0000,
            75.50,
            'USD',
            date('Y-m-d', strtotime('+30 days')),
            $cp['id'],
            $bu['id'],
            getCurrentUserId(),
            'draft'
        ];
        
        echo "<p><strong>Simple SQL:</strong> <code>{$simpleSQL}</code></p>";
        echo "<p><strong>Simple Parameters:</strong></p>";
        echo "<pre>" . print_r($simpleParams, true) . "</pre>";
        
        $stmt = $db->query($simpleSQL, $simpleParams);
        $newId = $db->getConnection()->lastInsertId();
        
        if ($newId) {
            echo "<p>✅ <strong>SUCCESS!</strong> Simple insert worked, new ID: {$newId}</p>";
            
            // Clean up
            $db->query("DELETE FROM physical_sales WHERE id = ?", [$newId]);
            echo "<p>🧹 Test record cleaned up</p>";
        } else {
            echo "<p>❌ Simple insert failed</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Simple Insert Error:</strong></p>";
        echo "<pre style='background: #ffe6e6; padding: 10px; border-radius: 4px;'>";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Code: " . $e->getCode() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "</pre>";
    }
    
    echo "<h2>4. Check Recent Error Logs</h2>";
    $errorLog = 'logs/php_errors.log';
    if (file_exists($errorLog)) {
        $errors = file_get_contents($errorLog);
        $recentErrors = array_slice(explode("\n", $errors), -10); // Last 10 lines
        
        echo "<h4>Recent PHP Errors (last 10 lines):</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: scroll; font-size: 11px;'>";
        echo htmlspecialchars(implode("\n", $recentErrors));
        echo "</pre>";
    } else {
        echo "<p>No error log file found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3, h4 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #f5f5f5; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; overflow-x: auto; }
code { background: #e9ecef; padding: 2px 4px; border-radius: 2px; font-family: monospace; }
</style> 