<?php
/**
 * Application Configuration
 * ETRM System - Main application settings
 */

// Application settings
define('APP_NAME', 'ETRM System');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'development'); // Changed to production but will show errors temporarily

// Paths
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');

// URL settings
define('BASE_URL', 'https://barole.3d7tech.com');
define('API_URL', BASE_URL . '/api');

// Session settings
define('SESSION_NAME', 'ETRM_SESSION');
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', true);  // Enable for HTTPS
define('SESSION_HTTP_ONLY', true);

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', UPLOADS_PATH);

// Pagination settings
define('DEFAULT_PAGE_SIZE', 25);
define('MAX_PAGE_SIZE', 100);

// Timezone
date_default_timezone_set('UTC');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);  // Temporarily show all errors for debugging
    ini_set('display_errors', 1);  // Temporarily display errors for debugging
}

// Logging
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php_errors.log');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_TRADER', 'trader');
define('ROLE_ANALYST', 'analyst');
define('ROLE_VIEWER', 'viewer');

// Trade types
define('TRADE_TYPE_PHYSICAL', 'physical');
define('TRADE_TYPE_FINANCIAL', 'financial');
define('TRADE_TYPE_FX', 'fx');

// Trade statuses
define('TRADE_STATUS_PENDING', 'pending');
define('TRADE_STATUS_CONFIRMED', 'confirmed');
define('TRADE_STATUS_EXECUTED', 'executed');
define('TRADE_STATUS_SETTLED', 'settled');
define('TRADE_STATUS_CANCELLED', 'cancelled');

// User statuses
define('USER_STATUS_ACTIVE', 'active');
define('USER_STATUS_INACTIVE', 'inactive');
define('USER_STATUS_SUSPENDED', 'suspended');

// Risk alert severities
define('ALERT_SEVERITY_LOW', 'low');
define('ALERT_SEVERITY_MEDIUM', 'medium');
define('ALERT_SEVERITY_HIGH', 'high');
define('ALERT_SEVERITY_CRITICAL', 'critical');

// Dashboard refresh intervals (in seconds)
define('DASHBOARD_REFRESH_INTERVALS', [
    30 => '30 seconds',
    60 => '1 minute',
    300 => '5 minutes',
    600 => '10 minutes',
    1800 => '30 minutes'
]);

// Chart.js colors for consistency
define('CHART_COLORS', [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
]);

// Email settings (for notifications)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// API rate limiting
define('API_RATE_LIMIT', 100); // requests per minute
define('API_RATE_LIMIT_WINDOW', 60); // seconds

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 300); // 5 minutes

// Backup settings
define('BACKUP_ENABLED', true);
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_PATH', BASE_PATH . '/backups');

// System maintenance
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'System is under maintenance. Please try again later.');

// Include required files
// require_once CONFIG_PATH . '/database.php';
// require_once INCLUDES_PATH . '/functions.php';
// require_once INCLUDES_PATH . '/auth.php';
// require_once INCLUDES_PATH . '/session.php';

// NOTE: Session initialization moved to individual pages to prevent conflicts 