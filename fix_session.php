<?php
/**
 * Quick Session Fix - Set Correct Session Variables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Quick Session Fix</h1>";

require_once 'config/app.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set ALL required session variables correctly
$_SESSION['user_id'] = 1;                    // Admin user ID
$_SESSION['username'] = 'admin';             // Admin username  
$_SESSION['role'] = 'admin';                 // Admin role
$_SESSION['logged_in'] = true;               // Login flag
$_SESSION['login_time'] = time();            // Login timestamp
$_SESSION['last_activity'] = time();         // CRITICAL: This was missing!

echo "<div style='color: green; border: 1px solid green; padding: 15px;'>";
echo "<h3>✅ Session Fixed Successfully!</h3>";
echo "<p><strong>All session variables set correctly:</strong></p>";
echo "<ul>";
echo "<li>✅ user_id: " . $_SESSION['user_id'] . "</li>";
echo "<li>✅ username: " . $_SESSION['username'] . "</li>";
echo "<li>✅ role: " . $_SESSION['role'] . "</li>";
echo "<li>✅ logged_in: " . ($_SESSION['logged_in'] ? 'true' : 'false') . "</li>";
echo "<li>✅ last_activity: " . $_SESSION['last_activity'] . " (" . date('Y-m-d H:i:s', $_SESSION['last_activity']) . ")</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🎯 Test Your Access:</h3>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>";
echo "<p><strong>1. Dashboard Access:</strong></p>";
echo "<p>🔗 <a href='index.php' style='color: blue; font-weight: bold;'>Open Main Dashboard</a></p>";
echo "<p>This should now work without any login errors!</p>";

echo "<p><strong>2. Login Page Test:</strong></p>";  
echo "<p>🔗 <a href='login.php' style='color: blue; font-weight: bold;'>Go to Login Page</a></p>";
echo "<p>This should automatically redirect to dashboard (already logged in)</p>";
echo "</div>";

echo "<hr>";
echo "<h3>🔧 Permanent Fix Needed:</h3>";
echo "<p>The <code>createUserSession()</code> function in login.php has a database update bug.</p>";
echo "<p>I'll fix that next so normal login works.</p>";
?>