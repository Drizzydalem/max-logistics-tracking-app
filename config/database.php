<?php
/**
 * MAX Logistics Tracking System - Database Configuration
 * 
 * This file contains database connection settings and configuration
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'max_logistics_tracking');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// Timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Database connection class
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            if (DEBUG_MODE) {
                error_log("Database connection established successfully");
            }
            
        } catch (PDOException $exception) {
            if (DEBUG_MODE) {
                error_log("Connection error: " . $exception->getMessage());
            }
            throw new Exception("Database connection failed: " . $exception->getMessage());
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

/**
 * Utility function to get database connection
 * @return PDO
 */
function getDBConnection() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate tracking number format
 * @param string $trackingNumber
 * @return bool
 */
function validateTrackingNumber($trackingNumber) {
    // MAX Logistics tracking numbers should start with 'MAX' followed by 9 digits
    return preg_match('/^MAX\d{9}$/', strtoupper($trackingNumber));
}

/**
 * Send JSON response
 * @param mixed $data
 * @param int $httpCode
 * @param string $message
 */
function sendResponse($data = null, $httpCode = 200, $message = 'Success') {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Log tracking requests for analytics
 * @param string $trackingNumber
 * @param string $ipAddress
 * @param string $userAgent
 */
function logTrackingRequest($trackingNumber, $ipAddress, $userAgent) {
    try {
        $db = getDBConnection();
        
        // Create logs table if it doesn't exist
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS tracking_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tracking_number VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tracking_number (tracking_number),
                INDEX idx_requested_at (requested_at)
            )
        ";
        
        $db->exec($createTableSQL);
        
        // Insert log entry
        $stmt = $db->prepare("
            INSERT INTO tracking_logs (tracking_number, ip_address, user_agent) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$trackingNumber, $ipAddress, $userAgent]);
        
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            error_log("Failed to log tracking request: " . $e->getMessage());
        }
    }
}
?>
