<?php
// ========================
// ⚙️ Cấu hình hệ thống
// ========================

// Thông tin Database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'shopnets');

// ========================
// 🌐 Đường dẫn hệ thống
// ========================

// BASE_URL: Dùng trong HTML (href, src, link, script)
define('BASE_URL', 'http://localhost/shopnets/user/');

// UPLOAD_DIR: Đường dẫn vật lý trên server (dùng cho PHP)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCT_IMAGE_DIR', BASE_URL . 'uploads/products/');

// ROOT_PATH: Đường dẫn vật lý đến thư mục gốc dự án
// ĐÚNG: Chỉ dùng đường dẫn file, không dùng URL
define('ROOT_PATH', __DIR__ . '/..');  // includes/.. → shopnets/

// ========================
// 🧩 Kết nối Database
// ========================
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Optional: Thiết lập charset
mysqli_set_charset($conn, 'utf8mb4');
?>