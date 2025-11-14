<?php
// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

// Try to load .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Database configuration
function getDatabaseConfig() {
    // Check if DATABASE_URL is set (for production deployment)
    if (!empty($_ENV['DATABASE_URL'])) {
        $url = parse_url($_ENV['DATABASE_URL']);
        return [
            'host' => $url['host'],
            'port' => isset($url['port']) ? $url['port'] : 3306,
            'dbname' => ltrim($url['path'], '/'),
            'username' => $url['user'],
            'password' => $url['pass'] ?? ''
        ];
    }
    
    // Fallback to individual environment variables
    return [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'dbname' => $_ENV['DB_NAME'] ?? 'shopnets',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? ''
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
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? 'assets/images/uploads/');
define('MAX_UPLOAD_SIZE', $_ENV['MAX_UPLOAD_SIZE'] ?? 5242880);

// Set error reporting based on environment
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>