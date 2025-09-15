<?php
/**
 * ETRM System - Status Page
 * Show system health and information
 */

// Load configuration
require_once 'config/app.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>ETRM System - Status</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-4'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card'>
                    <div class='card-header'>
                        <h3 class='mb-0'>
                            <i class='bi bi-lightning-charge text-primary'></i>
                            ETRM System Status
                        </h3>
                    </div>
                    <div class='card-body'>";

try {
    // System Information
    echo "<h5><i class='bi bi-info-circle'></i> System Information</h5>";
    echo "<ul class='list-group list-group-flush mb-3'>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "<span>System Name:</span>";
    echo "<span class='fw-bold'>" . htmlspecialchars(APP_NAME) . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "<span>Version:</span>";
    echo "<span class='fw-bold'>" . htmlspecialchars(APP_VERSION) . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "<span>Environment:</span>";
    echo "<span class='fw-bold'>" . htmlspecialchars(APP_ENV) . "</span>";
    echo "</li>";
    echo "<li class='list-group-item d-flex justify-content-between'>";
    echo "<span>PHP Version:</span>";
    echo "<span class='fw-bold'>" . htmlspecialchars(PHP_VERSION) . "</span>";
    echo "</li>";
    echo "</ul>";

    // Database Status
    echo "<h5><i class='bi bi-database'></i> Database Status</h5>";
    $db = getDB();
    echo "<div class='alert alert-success'>";
    echo "<i class='bi bi-check-circle'></i> Database connection successful";
    echo "</div>";

    // User Count
    $userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<div class='alert alert-info'>";
    echo "<i class='bi bi-people'></i> Total users: " . $userCount['count'];
    echo "</div>";

    // System Health
    echo "<h5><i class='bi bi-heart-pulse'></i> System Health</h5>";
    
    // Check if required directories exist
    $directories = ['uploads', 'logs', 'backups', 'database'];
    $allGood = true;
    
    foreach ($directories as $dir) {
        if (is_dir($dir) && is_writable($dir)) {
            echo "<div class='alert alert-success'>";
            echo "<i class='bi bi-check-circle'></i> Directory '$dir' is accessible and writable";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<i class='bi bi-exclamation-triangle'></i> Directory '$dir' may have permission issues";
            echo "</div>";
            $allGood = false;
        }
    }

    // Check database file
    if (file_exists('database/etrm_system.db') && is_readable('database/etrm_system.db')) {
        echo "<div class='alert alert-success'>";
        echo "<i class='bi bi-check-circle'></i> Database file is accessible";
        echo "</div>";
    } else {
        echo "<div class='alert alert-danger'>";
        echo "<i class='bi bi-x-circle'></i> Database file is not accessible";
        echo "</div>";
        $allGood = false;
    }

    // Overall Status
    if ($allGood) {
        echo "<div class='alert alert-success'>";
        echo "<h6><i class='bi bi-check-circle'></i> System Status: HEALTHY</h6>";
        echo "<p class='mb-0'>All systems are operational and ready for use.</p>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning'>";
        echo "<h6><i class='bi bi-exclamation-triangle'></i> System Status: WARNING</h6>";
        echo "<p class='mb-0'>Some issues detected. Please check the warnings above.</p>";
        echo "</div>";
    }

    // Quick Actions
    echo "<h5><i class='bi bi-lightning'></i> Quick Actions</h5>";
    echo "<div class='d-grid gap-2 d-md-block'>";
    echo "<a href='login.php' class='btn btn-primary'>";
    echo "<i class='bi bi-box-arrow-in-right'></i> Login to System";
    echo "</a>";
    echo "<a href='test.php' class='btn btn-outline-secondary'>";
    echo "<i class='bi bi-gear'></i> Run System Test";
    echo "</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h6><i class='bi bi-x-circle'></i> System Error</h6>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?> 