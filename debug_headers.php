<?php
/**
 * Debug Headers - Check for header issues
 * Use this to identify what's causing "headers already sent" errors
 */

echo "<h1>Header Debug Information</h1>\n";

// Check if headers have been sent
if (headers_sent($file, $line)) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>❌ Headers have already been sent!</strong><br>\n";
    echo "File: " . htmlspecialchars($file) . "<br>\n";
    echo "Line: " . $line . "<br>\n";
    echo "</div>\n";
} else {
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>✓ Headers have not been sent yet</strong><br>\n";
    echo "</div>\n";
}

// Check session status
echo "<h2>Session Status</h2>\n";
echo "Session Status: " . session_status() . " (0=disabled, 1=none, 2=active)<br>\n";

// Check if we can start session
echo "<h2>Session Test</h2>\n";
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
        echo "✓ Session started successfully<br>\n";
    } else {
        echo "❌ Cannot start session - headers already sent<br>\n";
    }
} else {
    echo "✓ Session already active<br>\n";
}

// Check output buffering
echo "<h2>Output Buffering</h2>\n";
echo "Output buffering level: " . ob_get_level() . "<br>\n";
echo "Output buffering active: " . (ob_get_level() > 0 ? "Yes" : "No") . "<br>\n";

// Show loaded files
echo "<h2>Loaded Files (first 10)</h2>\n";
$loadedFiles = get_included_files();
echo "<ol>\n";
foreach (array_slice($loadedFiles, 0, 10) as $file) {
    echo "<li>" . htmlspecialchars($file) . "</li>\n";
}
echo "</ol>\n";

// Show PHP version and settings
echo "<h2>PHP Configuration</h2>\n";
echo "PHP Version: " . PHP_VERSION . "<br>\n";
echo "Output buffering ini: " . ini_get('output_buffering') . "<br>\n";
echo "Implicit flush: " . ini_get('implicit_flush') . "<br>\n";

?> 