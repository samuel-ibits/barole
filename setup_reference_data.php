<?php
/**
 * Setup Reference Data
 * Ensures required master data exists for API testing
 */

// Load simple session management
require_once 'includes/simple_session.php';

// Only allow admin users
if (!isUserLoggedIn()) {
    echo "<p><strong>❌ Not logged in!</strong> <a href='login.php'>Please login first</a></p>";
    exit;
}

if (!hasUserRole('admin')) {
    echo "<p><strong>❌ Access denied!</strong> Admin access required.</p>";
    exit;
}

echo "<h1>Setup Reference Data</h1>";

try {
    require_once 'config/database.php';
    $db = getDB();
    
    echo "<h2>Setting up required reference data...</h2>";
    
    // 1. Check and create business units
    echo "<h3>1. Business Units</h3>";
    $existingBU = $db->query("SELECT COUNT(*) as count FROM business_units")->fetch();
    
    if ($existingBU['count'] == 0) {
        $businessUnits = [
            [
                'business_unit_name' => 'Crude Oil Trading',
                'description' => 'Crude oil trading division',
                'manager_id' => getCurrentUserId(),
                'status' => 'active'
            ],
            [
                'business_unit_name' => 'Refined Products',
                'description' => 'Gasoline, diesel, and other refined products',
                'manager_id' => getCurrentUserId(),
                'status' => 'active'
            ],
            [
                'business_unit_name' => 'Natural Gas',
                'description' => 'Natural gas and LNG trading',
                'manager_id' => getCurrentUserId(),
                'status' => 'active'
            ]
        ];
        
        foreach ($businessUnits as $bu) {
            $newId = $db->insert('business_units', $bu);
            echo "<p>✅ Created business unit: {$bu['business_unit_name']} (ID: {$newId})</p>";
        }
    } else {
        echo "<p>✅ Business units already exist ({$existingBU['count']} records)</p>";
    }
    
    // 2. Check and create counterparties
    echo "<h3>2. Counterparties</h3>";
    $existingCP = $db->query("SELECT COUNT(*) as count FROM counterparties")->fetch();
    
    if ($existingCP['count'] == 0) {
        $counterparties = [
            [
                'name' => 'Shell Trading International',
                'type' => 'supplier',
                'contact_email' => 'trading@shell.com',
                'contact_phone' => '+1-555-0001',
                'address' => 'Houston, TX',
                'city' => 'Houston',
                'country' => 'USA',
                'credit_rating' => 'AAA',
                'status' => 'active'
            ],
            [
                'name' => 'BP Trading Ltd',
                'type' => 'customer',
                'contact_email' => 'trading@bp.com',
                'contact_phone' => '+1-555-0002',
                'address' => 'London, UK',
                'city' => 'London',
                'country' => 'UK',
                'credit_rating' => 'AA+',
                'status' => 'active'
            ],
            [
                'name' => 'ExxonMobil Corporation',
                'type' => 'both',
                'contact_email' => 'trading@exxonmobil.com',
                'contact_phone' => '+1-555-0003',
                'address' => 'Irving, TX',
                'city' => 'Irving',
                'country' => 'USA',
                'credit_rating' => 'AAA',
                'status' => 'active'
            ]
        ];
        
        foreach ($counterparties as $cp) {
            $newId = $db->insert('counterparties', $cp);
            echo "<p>✅ Created counterparty: {$cp['name']} (ID: {$newId})</p>";
        }
    } else {
        echo "<p>✅ Counterparties already exist ({$existingCP['count']} records)</p>";
    }
    
    // 3. Check and create products
    echo "<h3>3. Products</h3>";
    $existingProd = $db->query("SELECT COUNT(*) as count FROM products")->fetch();
    
    if ($existingProd['count'] == 0) {
        $products = [
            [
                'product_name' => 'WTI Crude Oil',
                'product_code' => 'WTI',
                'category' => 'Crude Oil',
                'unit_of_measure' => 'BBL',
                'description' => 'West Texas Intermediate Crude Oil',
                'specifications' => 'API 39.6, Sulfur 0.24%',
                'status' => 'active'
            ],
            [
                'product_name' => 'Brent Crude Oil',
                'product_code' => 'BRENT',
                'category' => 'Crude Oil',
                'unit_of_measure' => 'BBL',
                'description' => 'North Sea Brent Crude Oil',
                'specifications' => 'API 38.3, Sulfur 0.37%',
                'status' => 'active'
            ],
            [
                'product_name' => 'RBOB Gasoline',
                'product_code' => 'RBOB',
                'category' => 'Refined Products',
                'unit_of_measure' => 'GAL',
                'description' => 'Reformulated Blendstock for Oxygenate Blending',
                'specifications' => 'Octane 87, RVP 9.0',
                'status' => 'active'
            ],
            [
                'product_name' => 'Ultra Low Sulfur Diesel',
                'product_code' => 'ULSD',
                'category' => 'Refined Products',
                'unit_of_measure' => 'GAL',
                'description' => 'Ultra Low Sulfur Diesel Fuel',
                'specifications' => 'Sulfur <15ppm, Cetane >40',
                'status' => 'active'
            ]
        ];
        
        foreach ($products as $prod) {
            $newId = $db->insert('products', $prod);
            echo "<p>✅ Created product: {$prod['product_name']} (ID: {$newId})</p>";
        }
    } else {
        echo "<p>✅ Products already exist ({$existingProd['count']} records)</p>";
    }
    
    // 4. Check and create ports
    echo "<h3>4. Ports</h3>";
    $existingPorts = $db->query("SELECT COUNT(*) as count FROM ports")->fetch();
    
    if ($existingPorts['count'] == 0) {
        $ports = [
            [
                'name' => 'Houston Ship Channel',
                'code' => 'USHOU',
                'country' => 'USA',
                'region' => 'Gulf Coast',
                'port_type' => 'Marine Terminal',
                'status' => 'active'
            ],
            [
                'name' => 'Rotterdam',
                'code' => 'NLRTM',
                'country' => 'Netherlands',
                'region' => 'Europe',
                'port_type' => 'Marine Terminal',
                'status' => 'active'
            ],
            [
                'name' => 'Singapore',
                'code' => 'SGSIN',
                'country' => 'Singapore',
                'region' => 'Asia',
                'port_type' => 'Marine Terminal',
                'status' => 'active'
            ]
        ];
        
        foreach ($ports as $port) {
            $newId = $db->insert('ports', $port);
            echo "<p>✅ Created port: {$port['name']} (ID: {$newId})</p>";
        }
    } else {
        echo "<p>✅ Ports already exist ({$existingPorts['count']} records)</p>";
    }
    
    // 5. Verify data integrity
    echo "<h3>5. Data Verification</h3>";
    $buCount = $db->query("SELECT COUNT(*) as count FROM business_units WHERE status = 'active'")->fetch();
    $cpCount = $db->query("SELECT COUNT(*) as count FROM counterparties WHERE status = 'active'")->fetch();
    $prodCount = $db->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'")->fetch();
    $portCount = $db->query("SELECT COUNT(*) as count FROM ports WHERE status = 'active'")->fetch();
    
    echo "<ul>";
    echo "<li>✅ Active Business Units: {$buCount['count']}</li>";
    echo "<li>✅ Active Counterparties: {$cpCount['count']}</li>";
    echo "<li>✅ Active Products: {$prodCount['count']}</li>";
    echo "<li>✅ Active Ports: {$portCount['count']}</li>";
    echo "</ul>";
    
    echo "<h2>✅ Reference Data Setup Complete!</h2>";
    echo "<p>You can now test the APIs with confidence. The following IDs are available:</p>";
    
    // Show available IDs for testing
    echo "<h4>Available for Testing:</h4>";
    
    $testBU = $db->query("SELECT id, business_unit_name FROM business_units WHERE status = 'active' LIMIT 3")->fetchAll();
    $testCP = $db->query("SELECT id, name FROM counterparties WHERE status = 'active' LIMIT 3")->fetchAll();
    $testProd = $db->query("SELECT id, product_name FROM products WHERE status = 'active' LIMIT 3")->fetchAll();
    $testPort = $db->query("SELECT id, name FROM ports WHERE status = 'active' LIMIT 3")->fetchAll();
    
    echo "<div style='display: flex; gap: 20px;'>";
    
    echo "<div><strong>Business Units:</strong><ul>";
    foreach ($testBU as $bu) {
        echo "<li>ID {$bu['id']}: {$bu['business_unit_name']}</li>";
    }
    echo "</ul></div>";
    
    echo "<div><strong>Counterparties:</strong><ul>";
    foreach ($testCP as $cp) {
        echo "<li>ID {$cp['id']}: {$cp['name']}</li>";
    }
    echo "</ul></div>";
    
    echo "<div><strong>Products:</strong><ul>";
    foreach ($testProd as $prod) {
        echo "<li>ID {$prod['id']}: {$prod['product_name']}</li>";
    }
    echo "</ul></div>";
    
    echo "<div><strong>Ports:</strong><ul>";
    foreach ($testPort as $port) {
        echo "<li>ID {$port['id']}: {$port['name']}</li>";
    }
    echo "</ul></div>";
    
    echo "</div>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='debug_create_record.php'>Test the Debug Script</a> - Should now work!</li>";
    echo "<li><a href='test_data_flow.php'>Test Data Flow</a> - Try creating records</li>";
    echo "<li><a href='index.php'>Visit Dashboard</a> - View your data</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("Setup reference data error: " . $e->getMessage());
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h1, h2, h3, h4 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
div[style*="display: flex"] > div { flex: 1; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
ol li { margin: 10px 0; }
</style> 