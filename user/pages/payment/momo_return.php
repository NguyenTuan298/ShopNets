<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Lấy order_id từ GET parameter
$order_id = $_GET['order_id'] ?? null;
$resultCode = $_GET['resultCode'] ?? null;

if (!$order_id) {
    die("Thiếu thông tin đơn hàng.");
}

// Lấy thông tin đơn hàng
$query = "SELECT o.*, u.username 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-danger'>";
    echo "<h4>Lỗi: Không tìm thấy đơn hàng</h4>";
    echo "<p>Order ID: " . htmlspecialchars($order_id) . "</p>";
    echo "</div>";
    echo "</div>";
    exit;
}

// Kiểm tra quyền truy cập
if ($order['user_id'] != $_SESSION['user_id']) {
    die("Bạn không có quyền truy cập đơn hàng này.");
}

// Xử lý kết quả thanh toán
$payment_status = '';
$message = '';

if ($resultCode == '0') {
    // Thanh toán thành công
    $payment_status = 'paid';
    $message = 'Thanh toán thành công qua MoMo';
    
    // Cập nhật trạng thái đơn hàng
    $update_query = "UPDATE orders SET 
                    payment_status = ?, 
                    payment_method = 'momo',
                    updated_at = NOW()
                    WHERE id = ?";
    $stmt = $db->prepare($update_query);
    $stmt->execute([$payment_status, $order_id]);
    
    // Ghi log thanh toán
    $log_query = "INSERT INTO payment_logs (order_id, payment_method, amount, status, response_data, created_at) 
                  VALUES (?, 'momo', ?, 'success', ?, NOW())";
    $stmt = $db->prepare($log_query);
    $stmt->execute([$order_id, $order['total_amount'], json_encode($_GET)]);
    
} else {
    // Thanh toán thất bại
    $payment_status = 'failed';
    $error_codes = [
        '11' => 'Giao dịch bị từ chối do tài khoản bị khóa',
        '12' => 'Giao dịch bị từ chối do không đủ số dư',
        '13' => 'Giao dịch bị từ chối do thẻ/tài khoản không hợp lệ',
        '20' => 'Giao dịch bị từ chối do sai thông tin',
        '21' => 'Giao dịch bị từ chối do số tiền không hợp lệ',
        '22' => 'Giao dịch bị từ chối do yêu cầu quá hạn',
        '40' => 'Giao dịch bị từ chối do lỗi hệ thống',
        '41' => 'Giao dịch bị từ chối do token không hợp lệ',
        '42' => 'Giao dịch bị từ chối do sai checksum',
        '43' => 'Giao dịch bị từ chối do sai signature',
        '99' => 'Giao dịch bị từ chối do lỗi khác'
    ];
    
    $message = $error_codes[$resultCode] ?? 'Thanh toán thất bại. Mã lỗi: ' . $resultCode;
    
    // Cập nhật trạng thái đơn hàng
    $update_query = "UPDATE orders SET 
                    payment_status = ?, 
                    updated_at = NOW()
                    WHERE id = ?";
    $stmt = $db->prepare($update_query);
    $stmt->execute([$payment_status, $order_id]);
    
    // Ghi log thanh toán thất bại
    $log_query = "INSERT INTO payment_logs (order_id, payment_method, amount, status, response_data, created_at) 
                  VALUES (?, 'momo', ?, 'failed', ?, NOW())";
    $stmt = $db->prepare($log_query);
    $stmt->execute([$order_id, $order['total_amount'], json_encode($_GET)]);
}

// Xóa session sandbox nếu có
if (isset($_SESSION['sandbox_payment'])) {
    unset($_SESSION['sandbox_payment']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán - ShopNets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .result-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .btn-success {
            background: #28a745;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <?php if ($resultCode == '0'): ?>
            <!-- Thành công -->
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 class="text-success mb-3">Thanh toán thành công!</h3>
            <p class="text-muted mb-4">Cảm ơn bạn đã mua hàng tại ShopNets</p>
            
        <?php else: ?>
            <!-- Thất bại -->
            <div class="error-icon">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h3 class="text-danger mb-3">Thanh toán thất bại</h3>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="order-info">
            <h5>Thông tin đơn hàng</h5>
            <div class="info-item">
                <span>Mã đơn hàng:</span>
                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
            </div>
            <div class="info-item">
                <span>Khách hàng:</span>
                <span><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
            <div class="info-item">
                <span>Số tiền:</span>
                <strong class="text-primary"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong>
            </div>
            <div class="info-item">
                <span>Trạng thái:</span>
                <strong class="<?= $resultCode == '0' ? 'text-success' : 'text-danger' ?>">
                    <?= $resultCode == '0' ? 'Đã thanh toán' : 'Thanh toán thất bại' ?>
                </strong>
            </div>
        </div>

        <div class="d-grid gap-2">
            <?php if ($resultCode == '0'): ?>
                <a href="<?= BASE_URL ?>pages/user/order-detail.php?id=<?= $order_id ?>" class="btn btn-success">
                    <i class="bi bi-eye me-2"></i>Xem chi tiết đơn hàng
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pages/checkout/checkout.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Thử lại thanh toán
                </a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house me-2"></i>Về trang chủ
            </a>
        </div>

        <?php if ($resultCode == '0'): ?>
            <div class="mt-4">
                <div class="alert alert-info">
                    <small>
                        <i class="bi bi-info-circle me-2"></i>
                        Đơn hàng của bạn đã được xác nhận. Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.
                    </small>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tự động chuyển hướng sau 10 giây nếu người dùng không click
        setTimeout(function() {
            window.location.href = '<?= BASE_URL ?>index.php';
        }, 10000);
    </script>
</body>
</html>