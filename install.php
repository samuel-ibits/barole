<?php
/**
 * ETRM System - Installation Script
 * Set up database and initial configuration
 */

// Check if already installed
if (file_exists('installed.lock')) {
    die('ETRM System is already installed. Remove installed.lock to reinstall.');
}

echo "<h1>ETRM System Installation</h1>\n";
echo "<p>Setting up Energy Trading and Risk Management System...</p>\n";

try {
    // Load database configuration only
    require_once 'config/database.php';
    
    // Test database connection
    echo "<p>Testing database connection...</p>\n";
    
    if (defined('DB_TYPE') && DB_TYPE === 'sqlite') {
        // SQLite setup
        $dbPath = DB_PATH;
        $dbDir = dirname($dbPath);
        
        // Create database directory if it doesn't exist
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        // Create SQLite database
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✓ SQLite database created successfully</p>\n";
        
        // Create database tables
        echo "<p>Creating database tables...</p>\n";
        $schema = file_get_contents('database/schema.sqlite.sql');
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (Exception $e) {
                    // Ignore errors for existing tables
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
        echo "<p style='color: green;'>✓ Database tables created</p>\n";
        
    } else {
        // MySQL setup
        require_once 'config/app.php';
        $db = getDB();
        echo "<p style='color: green;'>✓ Database connection successful</p>\n";
        
        // Create database tables
        echo "<p>Creating database tables...</p>\n";
        $schema = file_get_contents('database/schema.sql');
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^(--|#)/', $statement)) {
                try {
                    $db->query($statement);
                } catch (Exception $e) {
                    // Ignore errors for existing tables
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }
        echo "<p style='color: green;'>✓ Database tables created</p>\n";
    }
    
    // Create upload directories
    echo "<p>Creating upload directories...</p>\n";
    $directories = [
        'uploads',
        'uploads/documents',
        'uploads/reports',
        'logs',
        'backups'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    echo "<p style='color: green;'>✓ Upload directories created</p>\n";
    
    // Create installation lock file
    file_put_contents('installed.lock', date('Y-m-d H:i:s'));
    
    echo "<h2 style='color: green;'>Installation Complete!</h2>\n";
    echo "<p>The ETRM System has been successfully installed.</p>\n";
    echo "<p><strong>Default login credentials:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Username: admin</li>\n";
    echo "<li>Password: admin123</li>\n";
    echo "</ul>\n";
    echo "<p><strong>Important:</strong> Please change the default password after your first login.</p>\n";
    echo "<p><a href='login.php'>Go to Login Page</a></p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Installation failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database configuration in config/database.php</p>\n";
}
?> 