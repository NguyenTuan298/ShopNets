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

// Xử lý sandbox payment nếu có request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sandbox_payment'])) {
    $order_id = $_POST['order_id'] ?? null;
    $result_type = $_POST['result_type'] ?? 'success';
    
    if ($order_id) {
        // Lưu thông tin sandbox vào session
        $_SESSION['sandbox_payment'] = [
            'order_id' => $order_id,
            'result_code' => '0', // Luôn thành công
            'phone_number' => '0912345678',
            'result_type' => $result_type,
            'timestamp' => time()
        ];
        
        // Chuyển hướng đến trang return
        header('Location: ' . BASE_URL . 'pages/payment/momo_return.php?order_id=' . $order_id . '&resultCode=0');
        exit;
    }
}

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

// Xử lý lựa chọn phương thức
$payment_method = $_GET['method'] ?? 'sandbox'; // Mặc định là sandbox test

// CẤU HÌNH MOMO SANDBOX
$momo_config = [
    'partnerCode' => 'MOMOBKUN20180529',
    'accessKey' => 'klm05TvNBzhg7h7j',  
    'secretKey' => 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa',
    'endpoint' => 'https://test-payment.momo.vn/v2/gateway/api/create',
    'returnUrl' => BASE_URL . 'pages/payment/momo_return.php?order_id=' . $order_id,
    'notifyUrl' => BASE_URL . 'pages/payment/momo_webhook.php',
    'extraData' => base64_encode(json_encode(['order_id' => $order_id]))
];

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
$payUrl = null;
$qrCodeUrl = null;
$deeplink = null;
$error_message = null;

if ($http_status == 200) {
    $response = json_decode($result, true);
    
    if (isset($response['resultCode']) && $response['resultCode'] == 0) {
        $payUrl = $response['payUrl'];
        $qrCodeUrl = $response['qrCodeUrl'] ?? null;
        $deeplink = $response['deeplink'] ?? null;
    } else {
        $error_message = "Lỗi MoMo: " . ($response['message'] ?? 'Unknown error') . " (Code: " . ($response['resultCode'] ?? 'N/A') . ")";
    }
} else {
    $error_message = "Lỗi kết nối đến MoMo. HTTP Status: " . $http_status;
}

// Tài khoản test MoMo Sandbox (chỉ 1 tài khoản thành công)
$test_account = [
    'name' => 'Tài khoản test thành công',
    'phone' => '0912345678',
    'password' => 'MoMo1234',
    'otp' => '123456',
    'result' => 'success',
    'description' => 'Tài khoản test thanh toán thành công'
];
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .momo-logo {
            color: #ae2070;
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
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
        .method-tabs {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            border-bottom: 2px solid #e9ecef;
        }
        .method-tab {
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .method-tab.active {
            border-bottom-color: #ae2070;
            color: #ae2070;
            font-weight: 600;
        }
        .qr-container {
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .test-account-card {
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            background: #f8fff9;
            cursor: pointer;
            transition: all 0.3s;
        }
        .test-account-card:hover {
            border-color: #218838;
            background: #e8f5e8;
            transform: translateY(-2px);
        }
        .sandbox-info {
            background: #e7f3ff;
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="text-center">
            <div class="momo-logo">
                <i class="bi bi-wallet2"></i>
            </div>
            <h3 class="mb-3">Thanh toán MoMo</h3>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error_message) ?>
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
        </div>

        <!-- Tabs chọn phương thức -->
        <div class="method-tabs">
            <button class="method-tab <?= $payment_method === 'sandbox' ? 'active' : '' ?>">
                <i class="bi bi-credit-card me-2"></i>Sandbox Test
            </button>
            <button class="method-tab <?= $payment_method === 'qr' ? 'active' : '' ?>" 
                    onclick="window.location.href='?order_id=<?= $order_id ?>&method=qr'">
                <i class="bi bi-qr-code me-2"></i>QR Code
            </button>
            <button class="method-tab <?= $payment_method === 'web' ? 'active' : '' ?>" 
                    onclick="window.location.href='?order_id=<?= $order_id ?>&method=web'">
                <i class="bi bi-phone me-2"></i>Thanh toán Web
            </button>
        </div>

        <?php if ($payment_method === 'sandbox'): ?>
            <!-- SANDBOX TEST MODE -->
            <div class="sandbox-info">
                <h4 class="text-primary"><i class="bi bi-gear me-2"></i>Chế độ Sandbox Test</h4>
                <p class="mb-3">Click vào tài khoản test để mô phỏng thanh toán thành công.</p>
                
                <!-- Chỉ 1 tài khoản test thành công -->
                <div class="test-account-card" onclick="processSandboxPayment()">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="text-success mb-2"><?= $test_account['name'] ?></h5>
                            <p class="mb-2"><?= $test_account['description'] ?></p>
                            <div class="mt-3">
                                <div><strong><i class="bi bi-phone me-2"></i>SĐT:</strong> <?= $test_account['phone'] ?></div>
                                <div><strong><i class="bi bi-key me-2"></i>Password:</strong> <?= $test_account['password'] ?></div>
                                <div><strong><i class="bi bi-shield-lock me-2"></i>OTP:</strong> <?= $test_account['otp'] ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success fs-6">Thành công</span>
                            <div class="mt-2">
                                <i class="bi bi-mouse fs-1 text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="sandboxForm" method="POST" style="display: none;">
                    <input type="hidden" name="sandbox_payment" value="1">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <input type="hidden" name="result_type" value="success">
                </form>
            </div>

            <div class="alert alert-info mt-3">
                <h6><i class="bi bi-lightbulb me-2"></i>Hướng dẫn sử dụng Sandbox</h6>
                <small>
                    • <strong>Click vào thẻ test trên</strong> để mô phỏng thanh toán thành công<br>
                    • Hệ thống sẽ tự động xử lý và cập nhật trạng thái đơn hàng<br>
                    • Không cần nhập thông tin thực tế<br>
                    • Kết quả sẽ được ghi nhận vào database
                </small>
            </div>

        <?php elseif ($payment_method === 'qr' && $qrCodeUrl): ?>
            <!-- QR CODE MODE -->
            <div class="qr-container">
                <h5 class="mb-3">Quét QR Code để thanh toán</h5>
                <img src="<?= $qrCodeUrl ?>" alt="MoMo QR Code" class="img-fluid" style="max-width: 250px;">
                <p class="text-muted mt-3">
                    <small>Mở app MoMo và quét mã QR để hoàn tất thanh toán</small>
                </p>
            </div>

        <?php elseif ($payment_method === 'web' && $payUrl): ?>
            <!-- WEB PAYMENT MODE -->
            <div class="alert alert-info">
                <i class="bi bi-laptop me-2"></i>
                <strong>Thanh toán qua Web</strong>
                <p class="mb-0 mt-2">Bạn sẽ được chuyển đến trang thanh toán MoMo để nhập thông tin tài khoản.</p>
            </div>
            
            <div class="d-grid gap-2 mt-4">
                <a href="<?= $payUrl ?>" class="btn btn-momo btn-lg" target="_blank">
                    <i class="bi bi-arrow-right-circle me-2"></i>Đến trang thanh toán MoMo
                </a>
            </div>
        <?php endif; ?>

        <div class="d-grid gap-2 mt-4">
            <a href="<?= BASE_URL ?>pages/checkout/checkout.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại thanh toán
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function processSandboxPayment() {
        // Hiển thị loading
        const card = document.querySelector('.test-account-card');
        const originalContent = card.innerHTML;
        
        card.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success mb-2" role="status"></div>
                <div>Đang xử lý thanh toán...</div>
            </div>
        `;
        
        card.style.cursor = 'wait';
        
        // Submit form sau 2 giây (mô phỏng xử lý)
        setTimeout(function() {
            document.getElementById('sandboxForm').submit();
        }, 2000);
    }

    <?php if ($payment_method === 'web' && $payUrl): ?>
    // Tự động mở tab mới cho thanh toán web
    setTimeout(function() {
        window.open('<?= $payUrl ?>', '_blank');
    }, 1000);
    <?php endif; ?>
    </script>
</body>
</html>