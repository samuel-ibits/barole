<?php
/**
 * Debug Session Creation Issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug Session Creation</h1>";

try {
    require_once 'config/app.php';
    $db = getDB();
    
    echo "<h3>✅ Database Connected</h3>";
    
    // Check user_sessions table structure
    echo "<h3>📋 User Sessions Table Structure</h3>";
    try {
        $columns = $db->fetchAll("DESCRIBE user_sessions");
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
        echo "❌ Error checking user_sessions table: " . $e->getMessage() . "<br>";
    }
    
    // Check if admin user exists and get details
    echo "<h3>👤 Admin User Check</h3>";
    try {
        $user = $db->fetchOne("SELECT id, username, role, status FROM users WHERE username = 'admin'");
        if ($user) {
            echo "<div style='color: green;'>";
            echo "✅ Admin user found:<br>";
            echo "📍 ID: " . $user['id'] . "<br>";
            echo "📍 Username: " . $user['username'] . "<br>";
            echo "📍 Role: " . $user['role'] . "<br>";
            echo "📍 Status: " . $user['status'] . "<br>";
            echo "</div>";
            
            $userId = $user['id'];
            $username = $user['username'];
            $role = $user['role'];
            
        } else {
            echo "❌ Admin user not found<br>";
            exit;
        }
    } catch (Exception $e) {
        echo "❌ Error checking admin user: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // Test session creation manually
    echo "<h3>🔧 Testing Session Creation</h3>";
    
    try {
        // Initialize session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        echo "✅ PHP session started<br>";
        
        // Try to create user session manually
        echo "<h4>Method 1: Direct Session Variables</h4>";
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        echo "✅ Session variables set directly<br>";
        echo "📍 Session ID: " . session_id() . "<br>";
        echo "📍 User ID in session: " . ($_SESSION['user_id'] ?? 'not set') . "<br>";
        
        // Try database session record
        echo "<h4>Method 2: Database Session Record</h4>";
        try {
            $sessionToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $sessionData = [
                'user_id' => $userId,
                'session_token' => $sessionToken,
                'ip_address' => $ipAddress,
                'user_agent' => substr($userAgent, 0, 255), // Limit length
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt
            ];
            
            $sessionId = $db->insert('user_sessions', $sessionData);
            
            if ($sessionId) {
                echo "✅ Database session record created<br>";
                echo "📍 Session record ID: $sessionId<br>";
                echo "📍 Session token: " . substr($sessionToken, 0, 16) . "...<br>";
            } else {
                echo "❌ Failed to create database session record<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Database session error: " . $e->getMessage() . "<br>";
        }
        
        // Test if session is working
        echo "<h4>✅ Session Test Complete</h4>";
        echo "<div style='color: green; border: 1px solid green; padding: 10px;'>";
        echo "<strong>Manual session created successfully!</strong><br>";
        echo "🔗 <a href='index.php'>Test Main Dashboard</a><br>";
        echo "🔗 <a href='login.php'>Back to Login (should skip to dashboard)</a><br>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "❌ Session creation error: " . $e->getMessage() . "<br>";
    }
    
    // Show existing sessions
    echo "<h3>📊 Existing Sessions</h3>";
    try {
        $sessions = $db->fetchAll("SELECT id, user_id, ip_address, created_at, expires_at FROM user_sessions ORDER BY created_at DESC LIMIT 5");
        if (!empty($sessions)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User ID</th><th>IP</th><th>Created</th><th>Expires</th></tr>";
            foreach ($sessions as $session) {
                echo "<tr>";
                echo "<td>" . $session['id'] . "</td>";
                echo "<td>" . $session['user_id'] . "</td>";
                echo "<td>" . $session['ip_address'] . "</td>";
                echo "<td>" . $session['created_at'] . "</td>";
                echo "<td>" . $session['expires_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No existing sessions found<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error checking sessions: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🎯 Next Steps:</h3>";
echo "<ul>";
echo "<li>If manual session worked: Try <a href='index.php'>accessing dashboard</a></li>";
echo "<li>If still issues: Check the exact error in login.php</li>";
echo "<li>Session should now be active for testing</li>";
echo "</ul>";
?> 