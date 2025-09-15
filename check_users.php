<?php
/**
 * Check Users in Database - Debug Script
 */

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Database Users Check</h1>";

try {
    require_once 'config/app.php';
    $db = getDB();
    
    echo "<h3>✅ Database Connected Successfully</h3>";
    
    // Check if users table exists
    echo "<h3>📋 Checking Tables</h3>";
    $tables = $db->fetchAll("SHOW TABLES");
    echo "<p><strong>Tables found:</strong></p><ul>";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "<li>$tableName</li>";
    }
    echo "</ul>";
    
    // Check users table specifically
    echo "<h3>👥 Checking Users Table</h3>";
    try {
        $users = $db->fetchAll("SELECT id, username, email, role, status, created_at FROM users LIMIT 10");
        
        if (empty($users)) {
            echo "<div style='color: red;'>";
            echo "❌ <strong>No users found in database!</strong><br>";
            echo "📍 This means the database schema wasn't imported properly.<br>";
            echo "</div>";
        } else {
            echo "<p><strong>Found " . count($users) . " users:</strong></p>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['email'] . "</td>";
                echo "<td>" . $user['role'] . "</td>";
                echo "<td>" . $user['status'] . "</td>";
                echo "<td>" . $user['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>";
        echo "❌ <strong>Users table doesn't exist or has wrong structure!</strong><br>";
        echo "📍 Error: " . $e->getMessage() . "<br>";
        echo "📍 You need to import the database schema.<br>";
        echo "</div>";
    }
    
    // Check database name
    echo "<h3>🗄️ Database Info</h3>";
    $dbResult = $db->fetchOne("SELECT DATABASE() as db_name");
    echo "<p><strong>Current database:</strong> " . $dbResult['db_name'] . "</p>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ <strong>Database connection failed!</strong><br>";
    echo "📍 Error: " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🎯 What to do next:</h3>";
echo "<ul>";
echo "<li><strong>If no users found:</strong> You need to import the database schema and sample data</li>";
echo "<li><strong>If users table doesn't exist:</strong> Import database/schema_cpanel.sql via phpMyAdmin</li>";
echo "<li><strong>If database connection failed:</strong> Check your database credentials in config/database.php</li>";
echo "</ul>";
?> 