<?php
// Include file cấu hình chung
require_once __DIR__ . '/../../config.php';

// Lấy cấu hình database theo môi trường
$config = getDatabaseConfig();
$host = $config['host'];
$dbname = $config['dbname'];
$username = $config['username'];
$password = $config['password'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Hiển thị lỗi chi tiết trong development, ẩn trong production
    if (isLocalhost()) {
        die("Lỗi kết nối database: " . $e->getMessage() . "<br>Host: $host<br>DB: $dbname");
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("Lỗi kết nối database. Vui lòng liên hệ quản trị viên.");
    }
}
?>