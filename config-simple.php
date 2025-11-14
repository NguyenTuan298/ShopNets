<?php
/**
 * Simple database configuration for deployment
 */

// Simple database configuration function
function getDatabaseConfig() {
    // Check if DATABASE_URL is set (for production deployment)
    $database_url = getenv('DATABASE_URL');
    if (!empty($database_url)) {
        $url = parse_url($database_url);
        return [
            'host' => $url['host'],
            'port' => isset($url['port']) ? $url['port'] : 3306,
            'dbname' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'] ?? ''
        ];
    }
    
    // Fallback to environment variables or defaults
    return [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: 3306,
        'dbname' => getenv('DB_NAME') ?: 'shopnets',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: ''
    ];
}

// Create PDO connection
function createPDOConnection() {
    $config = getDatabaseConfig();
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please check your configuration.");
    }
}

// Application configuration
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('UPLOAD_PATH', getenv('UPLOAD_PATH') ?: 'assets/images/uploads/');
define('MAX_UPLOAD_SIZE', getenv('MAX_UPLOAD_SIZE') ?: 5242880);

// Set error reporting based on environment
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>