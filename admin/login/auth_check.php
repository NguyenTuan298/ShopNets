<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Tính đường dẫn tương đối đến trang login
    $login_path = '../admin/login/login.php';
    
    // Nếu đang ở trong admin/ (không phải subfolder)
    if (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') {
        $login_path = 'login/login.php';
    }
    
    header('Location: ' . $login_path);
    exit();
}

// Timeout 30 phút
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    
    // Tính đường dẫn login
    $login_path = '../admin/login/login.php';
    if (basename(dirname($_SERVER['PHP_SELF'])) === 'admin') {
        $login_path = 'login/login.php';
    }
    
    header('Location: ' . $login_path . '?error=Session+expired');
    exit();
}
$_SESSION['last_activity'] = time();
?>