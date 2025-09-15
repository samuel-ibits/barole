<?php
/**
 * Create Default Admin User
 * Run this once to create the admin user in your database
 */

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Create Default Admin User</h1>";

try {
    require_once 'config/app.php';
    $db = getDB();
    
    echo "<h3>✅ Database Connected</h3>";
    
    // Check if admin user already exists
    $existingAdmin = $db->fetchOne("SELECT id, username FROM users WHERE username = 'admin'");
    
    if ($existingAdmin) {
        echo "<div style='color: orange;'>";
        echo "⚠️ <strong>Admin user already exists!</strong><br>";
        echo "📍 Username: " . $existingAdmin['username'] . "<br>";
        echo "📍 User ID: " . $existingAdmin['id'] . "<br>";
        echo "📍 You can try logging in with: admin / admin123<br>";
        echo "</div>";
    } else {
        echo "<h3>👤 Creating Admin User</h3>";
        
        // Create password hash for 'admin123'
        $password = 'admin123';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert admin user
        $userData = [
            'username' => 'admin',
            'password_hash' => $passwordHash,
            'email' => 'admin@etrm.local',
            'full_name' => 'System Administrator',
            'department' => 'IT',
            'role' => 'admin',
            'status' => 'active',
            'permissions' => '["all"]',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $userId = $db->insert('users', $userData);
        
        if ($userId) {
            echo "<div style='color: green;'>";
            echo "✅ <strong>Admin user created successfully!</strong><br>";
            echo "📍 User ID: $userId<br>";
            echo "📍 Username: admin<br>";
            echo "📍 Password: admin123<br>";
            echo "📍 Email: admin@etrm.local<br>";
            echo "📍 Role: admin<br>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h3>🎯 Next Steps:</h3>";
            echo "<ol>";
            echo "<li><strong>Go to login page:</strong> <a href='login.php'>login.php</a></li>";
            echo "<li><strong>Login with:</strong> admin / admin123</li>";
            echo "<li><strong>Change password</strong> after first login for security</li>";
            echo "</ol>";
            
        } else {
            echo "<div style='color: red;'>";
            echo "❌ <strong>Failed to create admin user!</strong><br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Security Note:</strong> Delete this file after creating the admin user!</p>";
?> 