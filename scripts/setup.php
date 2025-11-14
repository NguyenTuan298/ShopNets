<?php
/**
 * Setup script for ShopNets deployment
 * This script runs during deployment to set up the database and necessary files
 */

echo "Starting ShopNets setup...\n";

// Check if composer autoload exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "Error: Composer dependencies not installed. Run 'composer install' first.\n";
    exit(1);
}

// Load configuration
require_once __DIR__ . '/../config.php';

try {
    // Create database connection
    $pdo = createPDOConnection();
    echo "Database connection established.\n";
    
    // Check if we need to run database setup
    $tables_exist = false;
    try {
        $result = $pdo->query("SELECT COUNT(*) FROM users LIMIT 1");
        $tables_exist = true;
        echo "Database tables already exist.\n";
    } catch (PDOException $e) {
        echo "Database tables need to be created.\n";
    }
    
    // Run database setup if needed
    if (!$tables_exist) {
        echo "Setting up database schema...\n";
        $schema_file = __DIR__ . '/../database/schema.sql';
        
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        echo "Warning: SQL statement failed: " . $e->getMessage() . "\n";
                    }
                }
            }
            echo "Database schema setup completed.\n";
        } else {
            echo "Warning: Schema file not found at $schema_file\n";
        }
    }
    
    // Create necessary directories
    $directories = [
        'admin/assets/images/uploads',
        'user/assets/images/uploads',
        'user/assets/images/products/phones',
        'user/assets/images/products/laptops',
        'user/assets/images/products/headphones',
        'user/assets/images/products/tablets',
        'user/assets/images/products/smartwatches',
        'user/assets/images/products/accessories',
        'logs'
    ];
    
    foreach ($directories as $dir) {
        $full_path = __DIR__ . '/../' . $dir;
        if (!is_dir($full_path)) {
            if (mkdir($full_path, 0755, true)) {
                echo "Created directory: $dir\n";
            } else {
                echo "Warning: Could not create directory: $dir\n";
            }
        }
    }
    
    // Set permissions for upload directories
    $upload_dirs = [
        'admin/assets/images/uploads',
        'user/assets/images/uploads'
    ];
    
    foreach ($upload_dirs as $dir) {
        $full_path = __DIR__ . '/../' . $dir;
        if (is_dir($full_path)) {
            chmod($full_path, 0755);
            echo "Set permissions for: $dir\n";
        }
    }
    
    echo "ShopNets setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>