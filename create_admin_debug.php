<?php
/**
 * Debug Admin Creation - Show Exact Errors
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Debug Admin User Creation</h1>";

try {
    require_once 'config/app.php';
    $db = getDB();
    
    echo "<h3>✅ Database Connected</h3>";
    
    // Check users table structure
    echo "<h3>📋 Users Table Structure</h3>";
    try {
        $columns = $db->fetchAll("DESCRIBE users");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "❌ Error checking table structure: " . $e->getMessage() . "<br>";
    }
    
    // Try direct SQL insert
    echo "<h3>👤 Attempting Direct SQL Insert</h3>";
    
    $password = 'admin123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Method 1: Direct SQL with PDO
        $sql = "INSERT INTO users (username, password_hash, email, full_name, department, role, status, permissions, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->getConnection()->prepare($sql);
        $result = $stmt->execute([
            'admin',
            $passwordHash,
            'admin@etrm.local',
            'System Administrator',
            'IT',
            'admin',
            'active',
            '["all"]',
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $userId = $db->getConnection()->lastInsertId();
            echo "<div style='color: green;'>";
            echo "✅ <strong>SUCCESS! Admin user created with direct SQL</strong><br>";
            echo "📍 User ID: $userId<br>";
            echo "📍 Username: admin<br>";
            echo "📍 Password: admin123<br>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h3>🎯 Next Steps:</h3>";
            echo "<ol>";
            echo "<li><a href='login.php'>Go to Login Page</a></li>";
            echo "<li>Login with: admin / admin123</li>";
            echo "</ol>";
            
        } else {
            echo "❌ Direct SQL insert failed<br>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red;'>";
        echo "❌ <strong>Direct SQL Error:</strong> " . $e->getMessage() . "<br>";
        echo "</div>";
        
        // Try simpler insert
        echo "<h3>🔄 Trying Simpler Insert</h3>";
        try {
            $simpleSql = "INSERT INTO users (username, password_hash, email, full_name, role, status) 
                          VALUES ('admin', ?, 'admin@etrm.local', 'Administrator', 'admin', 'active')";
            
            $stmt2 = $db->getConnection()->prepare($simpleSql);
            $result2 = $stmt2->execute([$passwordHash]);
            
            if ($result2) {
                $userId2 = $db->getConnection()->lastInsertId();
                echo "<div style='color: green;'>";
                echo "✅ <strong>SUCCESS! Simple admin user created</strong><br>";
                echo "📍 User ID: $userId2<br>";
                echo "</div>";
            } else {
                echo "❌ Simple insert also failed<br>";
            }
            
        } catch (Exception $e2) {
            echo "❌ Simple insert error: " . $e2->getMessage() . "<br>";
        }
    }
    
    // Check if user was created
    echo "<h3>🔍 Final Check - Users in Database</h3>";
    try {
        $users = $db->fetchAll("SELECT id, username, role, status FROM users");
        if (!empty($users)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Status</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['role'] . "</td>";
                echo "<td>" . $user['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ Still no users found<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error checking users: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ <strong>Connection Error:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}
?> 