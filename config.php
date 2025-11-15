<?php
// Cấu hình database cho ShopNets
// File này chứa thông tin kết nối database cho cả localhost và hosting

// Cấu hình cho localhost (development)
define('LOCAL_DB_HOST', 'localhost');
define('LOCAL_DB_NAME', 'shopnets');
define('LOCAL_DB_USER', 'root');
define('LOCAL_DB_PASS', '');

// Cấu hình cho InfinityFree hosting (production)
define('PROD_DB_HOST', 'sql204.infinityfree.com');
define('PROD_DB_NAME', 'if0_40419512_Shopnets');
define('PROD_DB_USER', 'if0_40419512');
define('PROD_DB_PASS', 'shopnets123'); // Thay đổi password này

// Kiểm tra môi trường hiện tại
function isLocalhost() {
    $localhost_indicators = ['localhost', '127.0.0.1', '::1'];
    return in_array($_SERVER['HTTP_HOST'], $localhost_indicators) || 
           strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
           strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false;
}

// Lấy cấu hình database theo môi trường
function getDatabaseConfig() {
    if (isLocalhost()) {
        return [
            'host' => LOCAL_DB_HOST,
            'dbname' => LOCAL_DB_NAME,
            'username' => LOCAL_DB_USER,
            'password' => LOCAL_DB_PASS
        ];
    } else {
        return [
            'host' => PROD_DB_HOST,
            'dbname' => PROD_DB_NAME,
            'username' => PROD_DB_USER,
            'password' => PROD_DB_PASS
        ];
    }
}
?>