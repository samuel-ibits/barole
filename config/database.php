<?php
/**
 * Database Configuration
 * ETRM System - Database connection and configuration
 */
require_once 'app.php';

// Environment detection
// $isProduction = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1'&& $_SERVER['HTTP_HOST'] !== 'barole.io');
$isProduction = (APP_ENV === 'production');

if ($isProduction) {
    // Production Database Configuration (MySQL for cPanel/hosting)
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'energytrm');  // Update this with your actual database name
    define('DB_USER', 'energytrmuser');        // Update this with your actual database user
    define('DB_PASS', '#!.RUe9(R-In]N}b');  // Update this with your actual database password
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Development Database Configuration (SQLite for local development)
    define('DB_TYPE', 'mysql');
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'etrm_system');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
    define('DB_PATH', __DIR__ . '/../database/etrm_system.db');
}

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Detect environment
        $isProduction = (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== '127.0.0.1');
        
        try {
            if (DB_TYPE === 'sqlite') {
                // SQLite connection for development
                if (!file_exists(DB_PATH)) {
                    throw new Exception("SQLite database file not found: " . DB_PATH);
                }
                
                $dsn = "sqlite:" . DB_PATH;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                
                $this->connection = new PDO($dsn, null, null, $options);
                
                // Enable foreign keys for SQLite
                $this->connection->exec("PRAGMA foreign_keys = ON");
                
            } else {
                // MySQL connection for production
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
                ];
                
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                
                // Set timezone for MySQL
                $this->connection->exec("SET time_zone = '+00:00'");
            }
            
        } catch (PDOException $e) {
            $errorMessage = "Database connection failed: " . $e->getMessage();
            error_log($errorMessage);
            
            // In development, show the error
            if (!$isProduction) {
                throw new Exception($errorMessage);
            } else {
                // In production, show generic error
                throw new Exception("Database connection failed. Please contact administrator.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Insert data into table
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $columnList = implode(', ', $columns);
        
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})";
        
        $stmt = $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update data in table
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params);
    }
    
    /**
     * Delete data from table
     */
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $whereParams);
    }
}

/**
 * Get database instance (helper function)
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Test database connection
 */
function testDatabaseConnection() {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT 1 as test");
        $result = $stmt->fetch();
        return $result['test'] === 1;
    } catch (Exception $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        return false;
    }
} 

function parseFormDataInput() {
    $rawData = file_get_contents("php://input");
    $data = [];

    // Check if Content-Type is multipart/form-data
    if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
        // Extract boundary
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        $boundary = $matches[1] ?? '';
        $blocks = preg_split("/-+$boundary/", $rawData);
        
        foreach ($blocks as $block) {
            if (empty(trim($block))) continue;
            if (strpos($block, 'application/octet-stream') !== false) continue; // skip files for now

            if (preg_match('/name="([^"]*)"\s*(?:;[^\r\n]*)?\r\n\r\n(.*)\r\n$/s', $block, $matches)) {
                $name = $matches[1];
                $value = trim($matches[2]);
                $data[$name] = $value;
            }
        }
    } else {
        // Try to parse as JSON
        $json = json_decode($rawData, true);
        if (is_array($json)) {
            $data = $json;
        }
    }

    return $data;
}

function handleGenericGet($db, $table, $allowedFields = [], $searchableFields = [], $defaultOrder = 'id ASC') {
    // Handle single record fetch
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        $query = "SELECT * FROM {$table} WHERE id = ? LIMIT 1";
        $record = $db->query($query, [$id])->fetch();

        if ($record) {
            sendJSONResponse([
                'success' => true,
                'data' => $record
            ]);
        } else {
            sendJSONResponse([
                'success' => false,
                'message' => ucfirst($table) . ' not found'
            ]);
        }
        return;
    }

    // Pagination setup
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 25;
    $offset = ($page - 1) * $limit;

    // Search and filters
    $whereConditions = [];
    $params = [];

    if (!empty($_GET['search']) && !empty($searchableFields)) {
        $searchTerm = '%' . trim($_GET['search']) . '%';
        $conditions = array_map(fn($f) => "{$f} LIKE ?", $searchableFields);
        $whereConditions[] = '(' . implode(' OR ', $conditions) . ')';
        foreach ($searchableFields as $f) $params[] = $searchTerm;
    }

    // Optional status or other filters
    foreach ($_GET as $key => $value) {
        if (!in_array($key, ['page', 'limit', 'search', 'id']) && $value !== '') {
            $whereConditions[] = "{$key} = ?";
            $params[] = $value;
        }
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Count total
    $countQuery = "SELECT COUNT(*) as total FROM {$table} {$whereClause}";
    $totalResult = $db->query($countQuery, $params)->fetch();
    $total = $totalResult['total'] ?? 0;

    // Fetch data
    $query = "SELECT * FROM {$table} {$whereClause} ORDER BY {$defaultOrder} LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $records = $db->query($query, $params)->fetchAll();

    // Filter out unwanted fields if specified
    if (!empty($allowedFields)) {
        $records = array_map(function($r) use ($allowedFields) {
            return array_intersect_key($r, array_flip($allowedFields));
        }, $records);
    }

    // Response
    sendJSONResponse([
        'success' => true,
        'data' => $records,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}
function handleGenericCreate($db, $table, $requiredFields = [], $uniqueFields = []) {
    // Ensure we’re using form-data or x-www-form-urlencoded
    if (empty($_POST)) {
        sendErrorResponse('No form data provided');
        return;
    }

    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = trim($value);
    }

    // --- 1️⃣ Validate required fields ---
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            sendErrorResponse("Field '{$field}' is required");
            return;
        }
    }

    // --- 2️⃣ Validate unique fields ---
    foreach ($uniqueFields as $field) {
        if (!empty($data[$field])) {
            $existing = $db->query("SELECT id FROM {$table} WHERE {$field} = ?", [$data[$field]])->fetch();
            if ($existing) {
                sendErrorResponse("{$field} already exists");
                return;
            }
        }
    }

    // --- 3️⃣ Auto default fields ---
    if (!isset($data['status'])) {
        $data['status'] = 'active';
    }
    if (!isset($data['created_at'])) {
        $data['created_at'] = date('Y-m-d H:i:s');
    }

    // --- 4️⃣ Insert record ---
    $newId = $db->insert($table, $data);

    if ($newId) {
        // Optional: Log action if function exists
        if (function_exists('logUserActivity')) {
            logUserActivity("create_{$table}", "Created new record in {$table}");
        }

        sendSuccessResponse([
            'id' => $newId,
            'data' => $data
        ], ucfirst($table) . ' created successfully');
    } else {
        sendErrorResponse('Failed to create record');
    }
}
