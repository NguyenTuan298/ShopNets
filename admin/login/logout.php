<?php
session_start();

$isApiRequest = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
               (isset($_GET['api']) && $_GET['api'] == '1');

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? null;

session_unset();
session_destroy();

session_start();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

error_log("User logout: ID=$userId, Role=$userRole, Time=" . date('Y-m-d H:i:s'));

if ($isApiRequest) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit();
}

header('Location: login.php?message=Đăng+xuất+thành+công');
exit();
?>
