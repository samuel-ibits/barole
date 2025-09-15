<?php
/**
 * ETRM System - Debug Version of Main Entry Point
 */

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== INDEX.PHP DEBUG ===<br><br>";

// Step 1: Load configuration
echo "<h3>Step 1: Loading Configuration</h3>";
try {
    require_once 'config/app.php';
    echo "✅ app.php loaded<br>";
} catch (Exception $e) {
    echo "❌ app.php failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Initialize session
echo "<h3>Step 2: Initialize Session</h3>";
try {
    initSecureSession();
    echo "✅ Session initialized<br>";
} catch (Exception $e) {
    echo "❌ Session init failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 3: Check login status
echo "<h3>Step 3: Check Login Status</h3>";
try {
    $loggedIn = isLoggedIn();
    if ($loggedIn) {
        echo "✅ User is logged in<br>";
    } else {
        echo "⚠️ User is NOT logged in - should redirect to login.php<br>";
        echo "📍 This means you need to login first!<br>";
        echo "🔗 <a href='login.php'>Go to Login Page</a><br>";
        exit; // Stop here since user isn't logged in
    }
} catch (Exception $e) {
    echo "❌ Login check failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 4: Get current user info (only if logged in)
echo "<h3>Step 4: Get User Information</h3>";
try {
    $userId = getCurrentUserId();
    $username = getCurrentUsername();  
    $role = getCurrentUserRole();
    
    echo "✅ User ID: " . ($userId ?: 'null') . "<br>";
    echo "✅ Username: " . ($username ?: 'null') . "<br>";
    echo "✅ Role: " . ($role ?: 'null') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Get user info failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 5: Get user details
echo "<h3>Step 5: Get User Details</h3>";
try {
    $userDetails = getUserById($userId);
    if ($userDetails) {
        echo "✅ User details loaded<br>";
        echo "📍 Full name: " . ($userDetails['full_name'] ?? 'Not set') . "<br>";
        echo "📍 Email: " . ($userDetails['email'] ?? 'Not set') . "<br>";
    } else {
        echo "⚠️ User details not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Get user details failed: " . $e->getMessage() . "<br>";
}

echo "<h3>Debug Complete</h3>";
echo "If you see this message, the index.php logic is working!<br>";
echo "The issue was likely that you need to login first.<br>";
echo "🔗 <a href='login.php'>Go to Login Page</a><br>";
?>