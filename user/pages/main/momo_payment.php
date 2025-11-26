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

// Lấy order_id từ GET parameter hoặc session
$order_id = $_GET['order_id'] ?? $_SESSION['current_order_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    die("Thiếu thông tin đơn hàng. Vui lòng quay lại trang thanh toán.");
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
if ($order['user_id'] != $user_id) {
    die("Bạn không có quyền truy cập đơn hàng này.");
}

// Kiểm tra xem đơn hàng đã được thanh toán chưa
if ($order['payment_status'] == 'paid') {
    echo "<div class='container mt-5'>";
    echo "<div class='alert alert-success'>";
    echo "<h4>Đơn hàng đã được thanh toán</h4>";
    echo "<p>Đơn hàng " . htmlspecialchars($order['order_number']) . " đã được thanh toán thành công.</p>";
    echo "</div>";
    echo "</div>";
    exit;
}

// Xử lý form nhập số điện thoại test
$phone_number = '';
$show_phone_input = true;
$test_mode = true; // Chế độ test

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
    $phone_number = trim($_POST['phone_number']);
    
    // Validate số điện thoại
    if (preg_match('/^(0|\+84)(3[2-9]|5[2689]|7[06-9]|8[1-9]|9[0-9])[0-9]{7}$/', $phone_number)) {
        $show_phone_input = false;
        $_SESSION['momo_test_phone'] = $phone_number;
    } else {
        $phone_error = "Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại Việt Nam.";
    }
}

// CẤU HÌNH MOMO TEST - Sử dụng thông tin test
$momo_config = [
    'partnerCode' => 'MOMOBKUN20180529',
    'accessKey' => 'klm05TvNBzhg7h7j',  
    'secretKey' => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
    'endpoint' => 'https://test-payment.momo.vn/v2/gateway/api/create',
    'returnUrl' => BASE_URL . 'pages/payment/momo_return.php?order_id=' . $order_id,
    'notifyUrl' => BASE_URL . 'pages/payment/momo_webhook.php',
    'extraData' => base64_encode(json_encode([
        'order_id' => $order_id,
        'test_phone' => $_SESSION['momo_test_phone'] ?? ''
    ]))
];

// Chỉ tạo yêu cầu thanh toán nếu đã có số điện thoại test
if (!$show_phone_input) {
    // Tạo các tham số cho MoMo
    $requestId = time() . rand(1000, 9999);
    $orderId = $order['order_number'] . '_' . time();
    $amount = round($order['total_amount']);
    $orderInfo = "Thanh toán đơn hàng " . $order['order_number'];
    $requestType = "captureWallet";
    $lang = "vi";

    // Tạo chữ ký
    $rawHash = "accessKey=" . $momo_config['accessKey'] . 
               "&amount=" . $amount . 
               "&extraData=" . $momo_config['extraData'] . 
               "&ipnUrl=" . $momo_config['notifyUrl'] . 
               "&orderId=" . $orderId . 
               "&orderInfo=" . $orderInfo . 
               "&partnerCode=" . $momo_config['partnerCode'] . 
               "&redirectUrl=" . $momo_config['returnUrl'] . 
               "&requestId=" . $requestId . 
               "&requestType=" . $requestType;

    $signature = hash_hmac('sha256', $rawHash, $momo_config['secretKey']);

    // Dữ liệu gửi đến MoMo
    $data = array(
        'partnerCode' => $momo_config['partnerCode'],
        'partnerName' => "ShopNets",
        'storeId' => "ShopNets",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $momo_config['returnUrl'],
        'ipnUrl' => $momo_config['notifyUrl'],
        'lang' => $lang,
        'extraData' => $momo_config['extraData'],
        'requestType' => $requestType,
        'signature' => $signature
    );

    // Gọi API MoMo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $momo_config['endpoint']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Xử lý kết quả
    if ($http_status == 200) {
        $response = json_decode($result, true);
        
        if (isset($response['resultCode']) && $response['resultCode'] == 0) {
            // Thành công - chuyển hướng đến MoMo
            header('Location: ' . $response['payUrl']);
            exit;
        } else {
            $error_message = "Lỗi MoMo: " . ($response['message'] ?? 'Unknown error') . " (Code: " . ($response['resultCode'] ?? 'N/A') . ")";
        }
    } else {
        $error_message = "Lỗi kết nối đến MoMo. HTTP Status: " . $http_status;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán MoMo - ShopNets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .momo-logo {
            color: #ae2070;
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
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #ae2070;
            margin: 15px 0;
        }
        .btn-momo {
            background: #ae2070;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .debug-info {
            background: #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            font-size: 12px;
            text-align: left;
        }
        .test-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <?php if ($show_phone_input): ?>
            <!-- Form nhập số điện thoại test -->
            <div class="momo-logo">
                <i class="bi bi-phone"></i>
            </div>
            
            <h3 class="mb-3">Nhập số điện thoại test MoMo</h3>
            
            <div class="test-info">
                <h5><i class="bi bi-info-circle me-2"></i>Chế độ kiểm thử</h5>
                <p class="mb-2">Vui lòng nhập số điện thoại để mô phỏng thanh toán qua MoMo.</p>
                <p class="mb-0"><strong>Số điện thoại test mẫu:</strong> 0912345678</p>
            </div>
            
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
            </div>

            <?php if (isset($phone_error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($phone_error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Số điện thoại MoMo test:</label>
                    <input type="tel" 
                           class="form-control form-control-lg" 
                           id="phone_number" 
                           name="phone_number" 
                           value="<?= htmlspecialchars($phone_number) ?>" 
                           placeholder="Nhập số điện thoại test..."
                           required
                           pattern="(0|\+84)(3[2-9]|5[2689]|7[06-9]|8[1-9]|9[0-9])[0-9]{7}">
                    <div class="form-text">
                        Định dạng: 0912345678 hoặc +84912345678
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-momo btn-lg">
                        <i class="bi bi-send-check me-2"></i>Tiếp tục thanh toán
                    </button>
                    <a href="<?= BASE_URL ?>pages/checkout/checkout.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Quay lại thanh toán
                    </a>
                </div>
            </form>

        <?php elseif (isset($error_message)): ?>
            <!-- Hiển thị lỗi -->
            <div class="momo-logo">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            
            <h3 class="mb-3">Lỗi thanh toán MoMo</h3>
            
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
            
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
                    <span>Số điện thoại test:</span>
                    <span><?= htmlspecialchars($_SESSION['momo_test_phone'] ?? '') ?></span>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="?order_id=<?= $order_id ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Thử lại
                </a>
                <a href="<?= BASE_URL ?>pages/checkout/checkout.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại thanh toán
                </a>
            </div>

        <?php else: ?>
            <!-- Đang xử lý -->
            <div class="momo-logo">
                <i class="bi bi-wallet2"></i>
            </div>
            
            <h3 class="mb-3">Đang xử lý thanh toán</h3>
            <p class="text-muted">Vui lòng chờ trong giây lát...</p>
            
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            
            <div class="order-info">
                <h5>Thông tin thanh toán</h5>
                <div class="info-item">
                    <span>Mã đơn hàng:</span>
                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                </div>
                <div class="info-item">
                    <span>Số tiền:</span>
                    <strong class="text-primary"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong>
                </div>
                <div class="info-item">
                    <span>Số điện thoại test:</span>
                    <span><?= htmlspecialchars($_SESSION['momo_test_phone'] ?? '') ?></span>
                </div>
                <div class="info-item">
                    <span>Phương thức:</span>
                    <span>Ví điện tử MoMo</span>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Đang chuyển hướng đến cổng thanh toán MoMo...
                </small>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (!$show_phone_input && !isset($error_message)): ?>
    <script>
        // Tự động submit form ẩn để tiếp tục thanh toán
        setTimeout(function() {
            // Nếu không tự động chuyển hướng, hiển thị thông báo
            document.querySelector('.alert-info').innerHTML = 
                '<small><i class="bi bi-exclamation-triangle me-1"></i>' +
                'Không thể chuyển hướng tự động. Vui lòng <a href="?order_id=<?= $order_id ?>">thử lại</a>.</small>';
        }, 5000);
    </script>
    <?php endif; ?>
</body>
</html>