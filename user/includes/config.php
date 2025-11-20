<?php
// ========================
// ⚙️ Cấu hình hệ thống
// ========================

// Include file cấu hình chung
require_once __DIR__ . '/../../config.php';

// Lấy cấu hình database theo môi trường
$config = getDatabaseConfig();
define('DB_SERVER', $config['host']);
define('DB_USERNAME', $config['username']);
define('DB_PASSWORD', $config['password']);
define('DB_NAME', $config['dbname']);

// ========================
// 🌐 Đường dẫn hệ thống
// ========================

// BASE_URL: Dùng trong HTML (href, src, link, script)
// Tự động phát hiện môi trường
if (isLocalhost()) {
    define('BASE_URL', 'http://localhost/shopnets/user/');
} else {
    // URL production của bạn trên InfinityFree
    define('BASE_URL', 'https://shopnets.infinityfreeapp.com/user/');
}

// UPLOAD_DIR: Đường dẫn vật lý trên server (dùng cho PHP)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCT_IMAGE_DIR', BASE_URL . 'uploads/products/');

// ROOT_PATH: Đường dẫn vật lý đến thư mục gốc dự án
// ĐÚNG: Chỉ dùng đường dẫn file, không dùng URL
define('ROOT_PATH', __DIR__ . '/..');  // includes/.. → shopnets/

// ========================
// 🧩 Kết nối Database
// ========================

// Debug: Hiển thị thông tin kết nối
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<div style='background:#f0f0f0;padding:20px;margin:20px;border:2px solid #333;'>";
echo "<h3>🔍 DEBUG THÔNG TIN KẾT NỐI DATABASE</h3>";
echo "<strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>Environment:</strong> " . (isLocalhost() ? '🏠 LOCALHOST' : '🌐 PRODUCTION') . "<br>";
echo "<strong>DB_SERVER:</strong> " . DB_SERVER . "<br>";
echo "<strong>DB_USERNAME:</strong> " . DB_USERNAME . "<br>";
echo "<strong>DB_NAME:</strong> " . DB_NAME . "<br>";
echo "<strong>DB_PASSWORD:</strong> " . (DB_PASSWORD ? '***có password***' : '***RỖNG***') . "<br>";
echo "</div>";

try {
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$conn) {
        throw new Exception(mysqli_connect_error());
    }
    
    // Thiết lập charset
    mysqli_set_charset($conn, 'utf8mb4');
    echo "✅ Kết nối database thành công!<br>";
    
} catch (Exception $e) {
    // Luôn hiển thị lỗi chi tiết để debug
    die("❌ Kết nối database thất bại:<br>" . 
        "Error: " . $e->getMessage() . "<br>" .
        "Host: " . DB_SERVER . "<br>" . 
        "User: " . DB_USERNAME . "<br>" .
        "DB: " . DB_NAME . "<br>" .
        "Password: " . (DB_PASSWORD ? '***có***' : '***rỗng***'));
}
?>