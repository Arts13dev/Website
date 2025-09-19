<?php
/**
 * Database Configuration for SmartTech & Beauty Store
 * Local phpMyAdmin Environment Setup
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_NAME', 'smarttech_beauty');
define('DB_USER', 'root');  // Default phpMyAdmin username
define('DB_PASS', '');      // Default phpMyAdmin password (usually empty for localhost)
define('DB_CHARSET', 'utf8mb4');

// Database connection class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo = null;
    
    /**
     * Get database connection
     * @return PDO
     */
    public function connect() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please check your configuration.");
            }
        }
        
        return $this->pdo;
    }
    
    /**
     * Close database connection
     */
    public function disconnect() {
        $this->pdo = null;
    }
}

// Create global database connection instance
function getDBConnection() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->connect();
}

// Test database connection
function testConnection() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        return false;
    }
}
?>