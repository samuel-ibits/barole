<?php
/**
 * Debug Dashboard - Find where index.php is failing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DASHBOARD DEBUG ===<br><br>";

// Step 1: Load configuration
echo "<h3>Step 1: Configuration</h3>";
try {
    require_once 'config/app.php';
    echo "✅ app.php loaded<br>";
} catch (Exception $e) {
    echo "❌ app.php failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Initialize session  
echo "<h3>Step 2: Session</h3>";
try {
    initSecureSession();
    echo "✅ Session initialized<br>";
} catch (Exception $e) {
    echo "❌ Session failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 3: Check login (this should pass now)
echo "<h3>Step 3: Login Check</h3>";
if (!isLoggedIn()) {
    echo "❌ Not logged in - redirecting...<br>";
    header('Location: login.php');
    exit;
} else {
    echo "✅ User is logged in<br>";
}

// Step 4: Get user information (this might be failing)
echo "<h3>Step 4: User Information</h3>";
try {
    $currentUser = [
        'id' => getCurrentUserId(),
        'username' => getCurrentUsername(),
        'role' => getCurrentUserRole()
    ];
    
    echo "✅ Current user data:<br>";
    echo "- ID: " . $currentUser['id'] . "<br>";
    echo "- Username: " . $currentUser['username'] . "<br>";
    echo "- Role: " . $currentUser['role'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Get user info failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 5: Get user details (this might be failing)
echo "<h3>Step 5: User Details</h3>";
try {
    $userDetails = getUserById($currentUser['id']);
    if ($userDetails) {
        echo "✅ User details loaded:<br>";
        echo "- Full name: " . ($userDetails['full_name'] ?? 'Not set') . "<br>";
        echo "- Email: " . ($userDetails['email'] ?? 'Not set') . "<br>";
        echo "- Department: " . ($userDetails['department'] ?? 'Not set') . "<br>";
    } else {
        echo "⚠️ User details not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Get user details failed: " . $e->getMessage() . "<br>";
    // Don't exit here, continue to see if we can render the page
}

// Step 6: Test HTML rendering
echo "<h3>Step 6: HTML Rendering Test</h3>";
try {
    echo "✅ Basic HTML output working<br>";
    echo "✅ PHP execution working<br>";
    
    // Test if we can include CSS/JS links
    echo "✅ Testing external resources...<br>";
    
} catch (Exception $e) {
    echo "❌ HTML rendering failed: " . $e->getMessage() . "<br>";
}

// Step 7: Show what index.php should render
echo "<h3>Step 7: Simple Dashboard Test</h3>";
echo "<div style='border: 1px solid #ccc; padding: 15px; background: #f9f9f9;'>";
echo "<h4>🎯 ETRM Dashboard</h4>";
echo "<p><strong>Welcome, " . ($currentUser['username'] ?? 'User') . "!</strong></p>";
echo "<p>Role: " . ($currentUser['role'] ?? 'Unknown') . "</p>";
echo "<p>This proves the core functionality is working.</p>";

echo "<div style='margin: 10px 0;'>";
echo "<h5>Navigation Test:</h5>";
echo "<ul>";
echo "<li>📊 Dashboard (you are here)</li>";
echo "<li>💰 Trading</li>";
echo "<li>📋 Operations</li>";  
echo "<li>🏢 Master Data</li>";
echo "<li>⚠️ Risk & Analytics</li>";
echo "<li>👥 User Management</li>";
echo "</ul>";
echo "</div>";

echo "<p><strong>🔗 Test Links:</strong></p>";
echo "<ul>";
echo "<li><a href='logout.php'>Logout</a></li>";
echo "<li><a href='login.php'>Login Page</a> (should redirect back)</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🔧 Next Steps:</h3>";
echo "<p>If you see this message, the core logic is working.</p>";
echo "<p>The issue is likely in the HTML/CSS rendering of the main index.php file.</p>";
echo "<p>Try accessing the real index.php again, or I can create a simplified version.</p>";
?>