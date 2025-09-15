<?php
/**
 * ETRM System Setup Script
 * Run this script to set up directories and get database schema
 */

// Load configuration
require_once 'config/app.php';

echo "<h1>ETRM System Setup</h1>\n";

// Quick status check
echo "<div style='background-color: #f8f9fa; border: 2px solid #dee2e6; padding: 20px; border-radius: 10px; margin: 20px 0;'>\n";
echo "<h2>🔍 System Status Check</h2>\n";

try {
    $db = getDB();
    $connection = $db->getConnection();
    
    // Check if users table exists
    $stmt = $connection->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->rowCount() > 0;
    
    // Check if admin user exists
    $adminExists = false;
    if ($usersTableExists) {
        $stmt = $connection->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
        $adminExists = $stmt->fetch()['count'] > 0;
    }
    
    if ($usersTableExists && $adminExists) {
        echo "<div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;'>\n";
        echo "<strong>🎉 SYSTEM IS READY!</strong><br>\n";
        echo "✓ Database connection working<br>\n";
        echo "✓ Users table exists<br>\n";
        echo "✓ Admin user created<br>\n";
        echo "<br><strong>Next step:</strong> <a href='login.php' style='color: #007bff; text-decoration: none; font-weight: bold;'>Go to Login Page</a><br>\n";
        echo "<strong>Login with:</strong> username: admin, password: admin123\n";
        echo "</div>\n";
    } else {
        echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>\n";
        echo "<strong>⚠️ SETUP REQUIRED</strong><br>\n";
        echo "Database connection: " . ($connection ? "✓ Working" : "✗ Failed") . "<br>\n";
        echo "Users table: " . ($usersTableExists ? "✓ Exists" : "✗ Missing") . "<br>\n";
        echo "Admin user: " . ($adminExists ? "✓ Created" : "✗ Missing") . "<br>\n";
        echo "<br><strong>Follow the setup instructions below.</strong>\n";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>\n";
    echo "<strong>❌ DATABASE CONNECTION FAILED</strong><br>\n";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    echo "<strong>Fix database credentials in config/database.php first.</strong>\n";
    echo "</div>\n";
}

echo "</div>\n";

// Create necessary directories
$directories = [
    'logs',
    'uploads',
    'uploads/temp',
    'backups'
];

echo "<h2>Creating Directories:</h2>\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir<br>\n";
        } else {
            echo "✗ Failed to create directory: $dir<br>\n";
        }
    } else {
        echo "✓ Directory already exists: $dir<br>\n";
    }
}

// Test database connection
echo "<h2>Testing Database Connection:</h2>\n";
try {
    $db = getDB();
    $connection = $db->getConnection();
    echo "✓ Database connection successful<br>\n";
    
    // Test basic query
    $stmt = $connection->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result['test'] == 1) {
        echo "✓ Database query test successful<br>\n";
    }
    
    // Check MySQL version
    $stmt = $connection->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "✓ MySQL Version: " . $version['version'] . "<br>\n";
    
    // Check if users table exists
    $stmt = $connection->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists<br>\n";
        
        // Check if admin user exists
        $stmt = $connection->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
        $adminCount = $stmt->fetch();
        if ($adminCount['count'] > 0) {
            echo "✓ Admin user already exists<br>\n";
            
            // Show admin user details
            $stmt = $connection->query("SELECT username, email, full_name, role, created_at FROM users WHERE username = 'admin'");
            $admin = $stmt->fetch();
            echo "<strong>Admin User Details:</strong><br>\n";
            echo "- Username: {$admin['username']}<br>\n";
            echo "- Email: {$admin['email']}<br>\n";
            echo "- Full Name: {$admin['full_name']}<br>\n";
            echo "- Role: {$admin['role']}<br>\n";
            echo "- Created: {$admin['created_at']}<br>\n";
        } else {
            echo "⚠ Admin user does not exist yet<br>\n";
        }
        
        // Show table structure
        echo "<h3>Current Users Table Structure:</h3>\n";
        $stmt = $connection->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>\n";
        foreach ($columns as $column) {
            echo "<tr><td>{$column['Field']}</td><td>{$column['Type']}</td><td>{$column['Null']}</td><td>{$column['Key']}</td><td>{$column['Default']}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "⚠ Users table does not exist<br>\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "<br>\n";
    echo "<strong>Please check your database credentials in config/database.php</strong><br>\n";
}

// Display required database schema
echo "<h2>Required Database Schema:</h2>\n";
echo "<p>You need to create the following tables in your MySQL database. Run these SQL commands in your cPanel phpMyAdmin:</p>\n";

$schema = "
-- ETRM System Database Schema
-- Compatible with MySQL 5.5+ and newer versions

-- Drop existing tables if they exist (BE CAREFUL - this will delete data!)
-- Uncomment these lines only if you want to start fresh:
-- DROP TABLE IF EXISTS audit_logs;
-- DROP TABLE IF EXISTS user_activity_logs;
-- DROP TABLE IF EXISTS user_sessions;
-- DROP TABLE IF EXISTS users;
-- DROP TABLE IF EXISTS system_settings;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    role ENUM('admin', 'manager', 'trader', 'analyst', 'viewer') NOT NULL DEFAULT 'viewer',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    permissions TEXT,  -- Changed from JSON to TEXT for compatibility
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values TEXT,  -- Changed from JSON to TEXT for compatibility
    new_values TEXT,  -- Changed from JSON to TEXT for compatibility
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

echo "<textarea style='width: 100%; height: 400px; font-family: monospace;'>" . htmlspecialchars($schema) . "</textarea>\n";

echo "<h2>Step 2: Insert Default Admin User</h2>\n";

// Check if admin user exists for better instructions
try {
    $stmt = $connection->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $adminExists = $stmt->fetch()['count'] > 0;
    
    if ($adminExists) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<strong>✓ Admin user already exists!</strong><br>\n";
        echo "You can skip this step and go directly to testing the login.<br>\n";
        echo "<strong>Login credentials:</strong> username: admin, password: admin123\n";
        echo "</div>\n";
    } else {
        echo "<p>After creating the tables above, run this INSERT command separately:</p>\n";
    }
} catch (Exception $e) {
    echo "<p>After creating the tables above, run this INSERT command separately:</p>\n";
}

$adminInsert = "
-- Insert default admin user (password: admin123)
INSERT INTO users (username, password_hash, email, full_name, role, permissions) VALUES 
('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@etrm.com', 'System Administrator', 'admin', 
'[\"user_manage\", \"system_config\", \"audit_view\", \"report_generate\", \"trade_create\", \"trade_edit\", \"trade_delete\", \"trade_approve\", \"master_data_manage\", \"risk_manage\", \"dashboard_configure\"]');
";

echo "<textarea style='width: 100%; height: 150px; font-family: monospace;'>" . htmlspecialchars($adminInsert) . "</textarea>\n";

echo "<h2>Alternative: If You Get Permissions Column Error</h2>\n";
echo "<p>If the INSERT above fails with 'Unknown column permissions', use this simpler version instead:</p>\n";

$adminInsertSimple = "
-- Simple admin user without permissions (they'll be set by the application)
INSERT INTO users (username, password_hash, email, full_name, role) VALUES 
('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@etrm.com', 'System Administrator', 'admin');
";

echo "<textarea style='width: 100%; height: 100px; font-family: monospace;'>" . htmlspecialchars($adminInsertSimple) . "</textarea>\n";

echo "<h2>Next Steps:</h2>\n";
echo "<ol>\n";
echo "<li><strong>Step 1:</strong> Copy the table creation SQL above and run it in your cPanel phpMyAdmin</li>\n";
echo "<li><strong>Step 2:</strong> Run the admin user INSERT command separately (try the first version, if it fails use the alternative)</li>\n";
echo "<li><strong>Step 3:</strong> Update the database password in config/database.php (currently set to 'your_actual_db_password_here')</li>\n";
echo "<li><strong>Step 4:</strong> Refresh this page to verify the table structure</li>\n";
echo "<li><strong>Step 5:</strong> Try accessing the login page</li>\n";
echo "<li><strong>Step 6:</strong> Delete this setup.php file after everything works</li>\n";
echo "</ol>\n";

echo "<h3>Default Login Credentials (after database setup):</h3>\n";
echo "<strong>Username:</strong> admin<br>\n";
echo "<strong>Password:</strong> admin123<br>\n";

?> 