<?php
/**
 * Test Data Flow - Frontend to Database and Back
 * Verify that the fixed APIs work correctly with the MySQL schema
 */

// Load simple session management
require_once 'includes/simple_session.php';

echo "<h1>ETRM Data Flow Test</h1>";

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

// Test master data APIs
echo "<h2>Master Data API Tests</h2>";

$apiTests = [
    'Counterparties' => 'api/master-data/counterparties.php',
    'Products' => 'api/master-data/products.php',
    'Physical Sales' => 'api/trading/physical-sales.php'
];

foreach ($apiTests as $name => $endpoint) {
    echo "<h3>Testing {$name} API</h3>";
    
    // Test GET request
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/" . $endpoint;
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Cookie: " . $_SERVER['HTTP_COOKIE'] . "\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            echo "<p>✅ {$name} API works - Found " . count($data['data']) . " records</p>";
        } else {
            echo "<p>❌ {$name} API error: " . ($data['error'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p>❌ {$name} API not accessible</p>";
    }
}

// Test database data
echo "<h2>Database Data Check</h2>";

$tables = [
    'counterparties' => 'Counterparties',
    'products' => 'Products', 
    'business_units' => 'Business Units',
    'ports' => 'Ports',
    'physical_sales' => 'Physical Sales'
];

foreach ($tables as $table => $label) {
    try {
        $result = $db->query("SELECT COUNT(*) as count FROM {$table}")->fetch();
        $count = $result['count'];
        
        if ($count > 0) {
            echo "<p>✅ {$label}: {$count} records</p>";
            
            // Show sample data for first few records
            if ($count <= 5) {
                $sample = $db->query("SELECT * FROM {$table} LIMIT 3")->fetchAll();
                echo "<details><summary>Sample data</summary><pre>";
                foreach ($sample as $row) {
                    echo "ID: " . $row['id'] . " | ";
                    if (isset($row['name'])) echo "Name: " . $row['name'] . " | ";
                    if (isset($row['code'])) echo "Code: " . $row['code'] . " | ";
                    if (isset($row['sale_id'])) echo "Sale ID: " . $row['sale_id'] . " | ";
                    echo "\n";
                }
                echo "</pre></details>";
            }
        } else {
            echo "<p>⚠️ {$label}: No data found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ {$label}: Error - " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Import instructions
echo "<h2>Setup Instructions</h2>";

// Check if sample data exists
$counterpartiesCount = $db->query("SELECT COUNT(*) as count FROM counterparties")->fetch()['count'];
$productsCount = $db->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];

if ($counterpartiesCount == 0 || $productsCount == 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<h3>⚠️ Missing Sample Data</h3>";
    echo "<p>Your database is missing sample data needed for testing. Please import the sample data:</p>";
    echo "<ol>";
    echo "<li>Go to your MySQL/phpMyAdmin interface</li>";
    echo "<li>Select your 'etrm' database</li>";
    echo "<li>Import the file: <code>database/sample_data_mysql.sql</code></li>";
    echo "<li>Refresh this page to verify the data was imported</li>";
    echo "</ol>";
    echo "</div>";
}

// Test form
if ($counterpartiesCount > 0 && $productsCount > 0) {
    echo "<h2>Test Physical Sales Creation</h2>";
    echo "<div style='background: #d1ecf1; padding: 15px; border: 1px solid #b8daff; border-radius: 5px;'>";
    echo "<p>✅ Sample data found! You can now test creating a physical sale:</p>";
    
    echo "<form id='testSaleForm'>";
    echo "<table>";
    echo "<tr><td>Sale ID:</td><td><input type='text' name='sale_id' value='TEST" . date('YmdHis') . "' required></td></tr>";
    
    // Get counterparties for dropdown
    $counterparties = $db->query("SELECT id, name FROM counterparties WHERE status = 'active' LIMIT 5")->fetchAll();
    echo "<tr><td>Counterparty:</td><td><select name='counterparty_id' required>";
    echo "<option value=''>Select Counterparty</option>";
    foreach ($counterparties as $cp) {
        echo "<option value='{$cp['id']}'>{$cp['name']}</option>";
    }
    echo "</select></td></tr>";
    
    // Get products for dropdown
    $products = $db->query("SELECT id, product_name FROM products WHERE active_status = 'active' LIMIT 5")->fetchAll();
    echo "<tr><td>Product:</td><td><select name='product_id' required>";
    echo "<option value=''>Select Product</option>";
    foreach ($products as $product) {
        echo "<option value='{$product['id']}'>{$product['product_name']}</option>";
    }
    echo "</select></td></tr>";
    
    echo "<tr><td>Quantity:</td><td><input type='number' name='quantity' value='1000' step='0.01' required></td></tr>";
    echo "<tr><td>Price:</td><td><input type='number' name='price' value='75.50' step='0.01' required></td></tr>";
    echo "<tr><td>Currency:</td><td><select name='currency'><option value='USD'>USD</option></select></td></tr>";
    echo "<tr><td>Delivery Date:</td><td><input type='date' name='delivery_date' value='" . date('Y-m-d', strtotime('+30 days')) . "' required></td></tr>";
    echo "<tr><td>Status:</td><td><select name='status'><option value='draft'>Draft</option><option value='confirmed'>Confirmed</option></select></td></tr>";
    echo "<tr><td>Notes:</td><td><textarea name='notes'>Test sale created via data flow test</textarea></td></tr>";
    echo "</table>";
    echo "<button type='button' onclick='testCreateSale()'>Create Test Sale</button>";
    echo "</form>";
    
    echo "<div id='testResult'></div>";
    echo "</div>";
}

?>

<script>
function testCreateSale() {
    const form = document.getElementById('testSaleForm');
    const formData = new FormData(form);
    const resultDiv = document.getElementById('testResult');
    
    resultDiv.innerHTML = '<p>⏳ Creating sale...</p>';
    
    fetch('api/trading/physical-sales.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                resultDiv.innerHTML = `
                    <div style="background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 10px;">
                        <h4>✅ Sale Created Successfully!</h4>
                        <p><strong>Sale ID:</strong> ${data.data.sale_id}</p>
                        <p><strong>Database ID:</strong> ${data.data.id}</p>
                        <p>Data has been successfully written to the database and can be retrieved via the API.</p>
                        <button onclick="location.reload()">Refresh Page to See Updated Counts</button>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-top: 10px;">
                        <h4>❌ Sale Creation Failed</h4>
                        <p><strong>Error:</strong> ${data.message || data.error || 'Unknown error'}</p>
                    </div>
                `;
            }
        } catch (e) {
            resultDiv.innerHTML = `
                <div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-top: 10px;">
                    <h4>❌ Invalid Response</h4>
                    <p><strong>Error:</strong> ${e.message}</p>
                    <p><strong>Raw Response:</strong> ${text.substring(0, 500)}...</p>
                </div>
            `;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; margin-top: 10px;">
                <h4>❌ Network Error</h4>
                <p><strong>Error:</strong> ${error.message}</p>
            </div>
        `;
    });
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1000px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
td { padding: 8px; vertical-align: top; }
input, select, textarea { padding: 5px; margin: 2px; }
button { padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:hover { background: #0056b3; }
pre { background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; }
details { margin: 5px 0; }
</style> 