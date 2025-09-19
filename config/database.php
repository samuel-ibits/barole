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
    define('DB_NAME', 'etrm');  // Update this with your actual database name
    define('DB_USER', 'barole_etrm');        // Update this with your actual database user
    define('DB_PASS', 'barole_etrm');  // Update this with your actual database password
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