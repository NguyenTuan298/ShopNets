<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/config.php';

$database = new Database();
$db = $database->getConnection();

$order_id = $_GET['order_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công - ShopNets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #28a745;
            font-size: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        
        <h2 class="text-success mb-3">Thanh toán thành công!</h2>
        
        <div class="alert alert-success">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Chế độ test:</strong> Thanh toán MoMo đã được giả lập thành công
        </div>
        
        <?php if ($order_id): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Thông tin đơn hàng</h5>
                <p class="card-text">
                    <strong>Mã đơn hàng:</strong> <?= htmlspecialchars($order_id) ?><br>
                    <strong>Trạng thái:</strong> <span class="badge bg-success">Đã thanh toán</span>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <p class="text-muted">
                Cảm ơn bạn đã mua hàng tại ShopNets. Đơn hàng sẽ được xử lý và giao đến bạn trong thời gian sớm nhất.
            </p>
        </div>
        
        <div class="d-grid gap-2 mt-4">
            <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">
                <i class="bi bi-house me-2"></i>Tiếp tục mua sắm
            </a>
            <a href="<?= BASE_URL ?>pages/user/orders.php" class="btn btn-outline-primary">
                <i class="bi bi-bag me-2"></i>Xem đơn hàng
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="bi bi-lightbulb me-1"></i>
                Đây là chế độ test. Khi triển khai thật, hãy set <code>$test_mode = false</code>
            </small>
        </div>
    </div>
</body>
</html>