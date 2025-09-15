<?php
/**
 * Ultra Simple Debug File
 * If this doesn't work, PHP itself is broken
 */

// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test if files exist
$files = [
    'index.php',
    'config/database.php',
    'config/app.php',
    'includes/functions.php'
];

echo "<h3>File Check:</h3>";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file MISSING<br>";
    }
}

// Test database config
echo "<h3>Database Config Check:</h3>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        echo "✅ Database config loaded<br>";
        
        // Check if constants are defined
        if (defined('DB_HOST')) {
            echo "✅ DB_HOST: " . DB_HOST . "<br>";
        } else {
            echo "❌ DB_HOST not defined<br>";
        }
        
        if (defined('DB_NAME')) {
            echo "✅ DB_NAME: " . DB_NAME . "<br>";
        } else {
            echo "❌ DB_NAME not defined<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Database config error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config/database.php not found<br>";
}

echo "<h3>Next Steps:</h3>";
echo "If you see this message, PHP is working. The blank screen is from index.php.<br>";
echo "Check the error log or contact me with this debug info.<br>";
?>