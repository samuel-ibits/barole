<?php
/**
 * Test Login Function - Verify Session Creation Works
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test Login Function</h1>";

require_once 'config/app.php';

// Clear any existing session
session_start();
session_destroy();
session_start();

echo "<h3>🔧 Testing createUserSession Function</h3>";

try {
    // Test the createUserSession function directly
    $result = createUserSession(1, 'admin', 'admin');
    
    if ($result) {
        echo "<div style='color: green;'>";
        echo "✅ <strong>createUserSession() SUCCESS!</strong><br>";
        echo "📍 Session variables set:<br>";
        echo "- user_id: " . ($_SESSION['user_id'] ?? 'not set') . "<br>";
        echo "- username: " . ($_SESSION['username'] ?? 'not set') . "<br>";
        echo "- role: " . ($_SESSION['role'] ?? 'not set') . "<br>";
        echo "- logged_in: " . (($_SESSION['logged_in'] ?? false) ? 'true' : 'false') . "<br>";
        echo "- last_activity: " . ($_SESSION['last_activity'] ?? 'not set') . "<br>";
        echo "</div>";
        
        echo "<h3>🔍 Testing Session Validation</h3>";
        
        // Test if isLoggedIn() recognizes the session
        if (isLoggedIn()) {
            echo "<div style='color: green;'>";
            echo "✅ <strong>isLoggedIn() returns TRUE</strong><br>";
            echo "📍 Session validation working correctly!<br>";
            echo "</div>";
            
            echo "<hr>";
            echo "<h3>🎯 Ready to Test:</h3>";
            echo "<div style='background: #e8f5e8; padding: 15px; border: 1px solid green;'>";
            echo "<p><strong>✅ Login function is now working!</strong></p>";
            echo "<ol>";
            echo "<li><strong>Test Dashboard:</strong> <a href='index.php'>Open Dashboard</a></li>";
            echo "<li><strong>Test Logout:</strong> <a href='logout.php'>Logout</a></li>";
            echo "<li><strong>Test Normal Login:</strong> <a href='login.php'>Login Page</a> (use admin/admin123)</li>";
            echo "</ol>";
            echo "</div>";
            
        } else {
            echo "<div style='color: red;'>";
            echo "❌ <strong>isLoggedIn() returns FALSE</strong><br>";
            echo "📍 Session validation still has issues<br>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='color: red;'>";
        echo "❌ <strong>createUserSession() FAILED</strong><br>";
        echo "📍 Check the error logs for details<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>";
    echo "❌ <strong>Error testing login:</strong> " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Note:</strong> Delete this test file after confirming everything works!</p>";
?>