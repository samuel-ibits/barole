<?php
/**
 * ETRM System - Simple Test File
 * Use this to verify PHP is working on your cPanel hosting
 */

echo "<h1>🧪 ETRM System - PHP Test</h1>";
echo "<hr>";

// Basic PHP info
echo "<h2>✅ PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Script:</strong> " . __FILE__ . "</p>";

// Check required extensions
echo "<h2>🔧 Required PHP Extensions</h2>";
$extensions = ['PDO', 'pdo_mysql', 'mbstring', 'openssl', 'curl', 'json'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>{$status} {$ext}</p>";
}

// Test file permissions
echo "<h2>📁 File System Test</h2>";
$testDir = 'uploads';
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

if (is_writable($testDir)) {
    echo "<p>✅ uploads/ directory is writable</p>";
} else {
    echo "<p>❌ uploads/ directory is not writable</p>";
}

// Test database connection (if config exists)
echo "<h2>🗄️ Database Connection Test</h2>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        $db = Database::getInstance();
        echo "<p>✅ Database connection successful</p>";
    } catch (Exception $e) {
        echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>⚠️ Database config not found</p>";
}

// Display current time
echo "<h2>⏰ Server Time</h2>";
echo "<p>" . date('Y-m-d H:i:s T') . "</p>";

echo "<hr>";
echo "<p><strong>If all tests show ✅, your server is ready for ETRM System!</strong></p>";
echo "<p><em>Remember to delete this file after testing for security.</em></p>";
?> 