<?php
/**
 * Emergency Fix: Add permissions column to users table
 * Run this if you get "Unknown column 'permissions'" error
 */

// Load configuration
require_once 'config/app.php';

echo "<h1>Fix Permissions Column</h1>\n";

try {
    $db = getDB();
    $connection = $db->getConnection();
    
    // Check if permissions column exists
    $stmt = $connection->query("SHOW COLUMNS FROM users LIKE 'permissions'");
    
    if ($stmt->rowCount() == 0) {
        echo "Adding permissions column to users table...<br>\n";
        
        // Add permissions column
        $connection->exec("ALTER TABLE users ADD COLUMN permissions TEXT AFTER status");
        
        echo "✓ Permissions column added successfully<br>\n";
        
        // Update admin user with permissions
        $permissions = json_encode([
            "user_manage", "system_config", "audit_view", "report_generate",
            "trade_create", "trade_edit", "trade_delete", "trade_approve",
            "master_data_manage", "risk_manage", "dashboard_configure"
        ]);
        
        $stmt = $connection->prepare("UPDATE users SET permissions = ? WHERE role = 'admin'");
        $stmt->execute([$permissions]);
        
        echo "✓ Admin permissions updated<br>\n";
        
    } else {
        echo "✓ Permissions column already exists<br>\n";
    }
    
    // Show current table structure
    echo "<h2>Current Users Table Structure:</h2>\n";
    $stmt = $connection->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td><td>{$column['Null']}</td><td>{$column['Key']}</td><td>{$column['Default']}</td></tr>\n";
    }
    echo "</table><br>\n";
    
    echo "<p><strong>Now you can try the normal admin user INSERT command or try logging in.</strong></p>\n";
    echo "<p><a href='login.php'>Go to Login Page</a> | <a href='setup.php'>Back to Setup</a></p>\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>\n";
}

?> 