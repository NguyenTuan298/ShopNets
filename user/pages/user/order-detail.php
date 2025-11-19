<?php
// pages/user/order-detail.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Kiểm tra có order_id không
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . 'pages/user/orders.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// Lấy thông tin chi tiết đơn hàng
$order = getOrderById($db, $order_id, $user_id);

// Kiểm tra đơn hàng có tồn tại và thuộc về user không
if (!$order) {
    header('Location: ' . BASE_URL . 'pages/user/orders.php');
    exit;
}

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $result = cancelOrder($db, $order_id, $user_id);
    
    if ($result) {
        $success = 'Đã hủy đơn hàng thành công!';
        // Refresh thông tin đơn hàng
        $order = getOrderById($db, $order_id, $user_id);
    } else {
        $error = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.';
    }
}

// Xử lý đánh giá sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $order_item_id = $_POST['order_item_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if (submitReview($db, $user_id, $product_id, $order_item_id, $rating, $comment)) {
        $success = 'Cảm ơn bạn đã đánh giá sản phẩm!';
    } else {
        $error = 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.';
    }
}

// Lấy danh sách sản phẩm trong đơn hàng
$order_items = getOrderItemsWithDetails($db, $order_id);

// Lấy lịch sử trạng thái đơn hàng
$order_status_history = getOrderStatusHistory($db, $order_id);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng - SHOPNETS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow: 0 6px 12px rgba(0,0,0,0.12);
            --shadow-lg: 0 14px 32px rgba(0,0,0,0.18);
            --radius-sm: 10px;
            --radius: 14px;
            --radius-lg: 20px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { font-size: 68%; line-height: 1.7rem; font-family: 'Inter', 'Roboto', sans-serif; }
        body { 
            background: #f1f5f9; 
            color: #1e293b; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            padding-top: 80px;
        }

        .container { max-width: 1200px; }

        /* BREADCRUMB */
        .breadcrumb {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-sm);
            font-size: 1.7rem !important;
            margin-bottom: 2.4rem;
        }
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.7rem !important;
        }
        .breadcrumb-item.active { 
            color: var(--dark); 
            font-weight: 500;
            font-size: 1.7rem !important;
        }

        /* PAGE HEADER */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            font-size: 1.6rem;
            opacity: 0.9;
        }

        /* ORDER DETAIL SECTION */
        .order-detail-section { padding: 2.4rem 0; flex: 1; }

        .order-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2.4rem;
        }

        .order-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem 2.4rem;
        }

        .order-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 1.5rem;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
        }

        .order-info-label {
            font-size: 1.4rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .order-info-value {
            font-weight: 600;
            font-size: 1.6rem;
        }

        .order-status {
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.4rem;
            display: inline-block;
            margin-top: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-processing { background: #f0f9ff; color: #0369a1; }
        .status-shipped { background: #f0f9ff; color: #0369a1; }
        .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .order-content {
            padding: 2.4rem;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary);
            display: inline-block;
        }

        /* ORDER ITEMS */
        .order-items { margin-bottom: 3rem; }

        .order-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }
        .order-item:hover { border-color: var(--primary); box-shadow: var(--shadow-sm); }
        .order-item:last-child { margin-bottom: 0; }

        .item-image {
            width: 80px;
            height: 80px;
            background: var(--light);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 2.2rem;
            box-shadow: var(--shadow-sm);
            flex-shrink: 0;
        }

        .item-details { flex: 1; }

        .item-name {
            font-weight: 600;
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .item-category {
            color: var(--gray);
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-weight: 700;
            font-size: 1.7rem;
            color: var(--primary);
        }

        .item-quantity {
            color: var(--gray);
            font-size: 1.4rem;
        }

        /* ORDER SUMMARY */
        .order-summary {
            background: var(--light);
            border-radius: var(--radius);
            padding: 2rem;
            border: 2px solid var(--border);
            margin-bottom: 2rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            font-size: 1.6rem;
        }

        .summary-label { color: var(--gray); }
        .summary-value { font-weight: 600; color: var(--dark); }

        .summary-total {
            border-top: 2px solid var(--border);
            padding-top: 1.2rem;
            margin-top: 1.2rem;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* ORDER ACTIONS */
        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 1.2rem 2rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1.5rem;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .btn-primary-custom {
            background: var(--primary);
            color: white;
        }
        .btn-primary-custom:hover { background: var(--primary-dark); transform: translateY(-2px); color: white; }

        .btn-outline-custom {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-custom:hover { background: var(--primary); color: white; transform: translateY(-2px); }

        .btn-danger-custom {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }
        .btn-danger-custom:hover { background: #dc2626; transform: translateY(-2px); color: white; }

        /* ORDER TIMELINE */
        .order-timeline {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            border: 2px solid var(--border);
            margin-bottom: 2rem;
        }

        .timeline-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.8rem;
        }

        .timeline {
            position: relative;
            padding-left: 3rem;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-item:last-child { margin-bottom: 0; }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.3rem;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid white;
            z-index: 1;
            box-shadow: var(--shadow-sm);
        }

        .timeline-item.active:before { background: var(--success); }

        .timeline-date {
            font-size: 1.4rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .timeline-content {
            font-weight: 500;
            color: var(--dark);
            font-size: 1.5rem;
        }

        /* REVIEW MODAL */
        .review-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(12px);
            padding: 1rem;
        }

        .review-modal.show { display: flex; }

        .review-modal .modal-content {
            background: white;
            padding: 2.4rem;
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .review-modal .modal-header {
            border-bottom: 2px solid var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .review-modal .modal-title {
            color: var(--dark);
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
        }

        .review-modal .modal-body { padding: 0; }

        .rating-stars {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 2rem;
        }

        .rating-star {
            font-size: 2.5rem;
            color: #d1d5db;
            cursor: pointer;
            transition: var(--transition);
        }

        .rating-star.active { color: var(--warning); }
        .rating-star:hover { color: var(--warning); transform: scale(1.1); }

        .form-group { margin-bottom: 2rem; }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: var(--dark);
            font-size: 1.6rem;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 1.4rem 1.8rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1.6rem;
            transition: var(--transition);
            background: var(--light);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
            background: white;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        /* ALERT STYLING */
        .alert {
            border-radius: var(--radius);
            padding: 1.5rem 1.8rem;
            margin: 2.4rem;
            display: flex;
            align-items: center;
            font-size: 1.6rem;
            border: none;
        }
        .alert-danger { 
            background: rgba(239,68,68,.12); 
            color: var(--danger); 
            border-left: 4px solid var(--danger);
        }
        .alert-success { 
            background: rgba(16,185,129,.12); 
            color: var(--success); 
            border-left: 4px solid var(--success);
        }
        .alert i { margin-right: 1.2rem; font-size: 1.8rem; }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .order-info-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .order-header { padding: 1.8rem; }
            .order-title { font-size: 2rem; }
            .order-info-grid { grid-template-columns: 1fr; }
            .order-item { flex-direction: column; text-align: center; gap: 1rem; }
            .item-details { text-align: center; }
            .order-actions { flex-direction: column; }
            .btn-custom { justify-content: center; }
        }

        @media (max-width: 576px) {
            .page-title { font-size: 2.3rem; }
            .order-content { padding: 1.5rem; }
            .modal-footer { flex-direction: column; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../../includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>pages/user/profile.php">Tài khoản</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>pages/user/orders.php">Đơn hàng của tôi</a></li>
                <li class="breadcrumb-item active">Chi tiết đơn hàng</li>
            </ol>
        </nav>
    </div>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Chi Tiết Đơn Hàng</h1>
            <p class="page-subtitle">Mã đơn hàng: #<?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>
    </section>

    <!-- Order Detail Section -->
    <section class="order-detail-section">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="order-card">
                <div class="order-header">
                    <h3 class="order-title">Đơn Hàng #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                    
                    <div class="order-info-grid">
                        <div class="order-info-item">
                            <span class="order-info-label">Ngày đặt</span>
                            <span class="order-info-value">
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </span>
                        </div>
                        <div class="order-info-item">
                            <span class="order-info-label">Phương thức thanh toán</span>
                            <span class="order-info-value">
                                <?php 
                                $payment_methods = [
                                    'cod' => 'Thanh toán khi nhận hàng',
                                    'bank_transfer' => 'Chuyển khoản ngân hàng',
                                    'momo' => 'Ví MoMo',
                                    'vnpay' => 'VNPay'
                                ];
                                echo $payment_methods[$order['payment_method']] ?? 'Không xác định';
                                ?>
                            </span>
                        </div>
                        <div class="order-info-item">
                            <span class="order-info-label">Trạng thái thanh toán</span>
                            <span class="order-info-value">
                                <?php 
                                $payment_statuses = [
                                    'pending' => 'Chờ thanh toán',
                                    'paid' => 'Đã thanh toán',
                                    'failed' => 'Thanh toán thất bại',
                                    'refunded' => 'Đã hoàn tiền'
                                ];
                                echo $payment_statuses[$order['payment_status']] ?? 'Không xác định';
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <span class="order-info-label">Trạng thái đơn hàng</span>
                        <div class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                            <?php 
                            $status_text = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'processing' => 'Đang xử lý',
                                'shipped' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy'
                            ];
                            echo $status_text[$order['order_status']] ?? 'Chờ xác nhận';
                            ?>
                        </div>
                    </div>
                </div>

                <div class="order-content">
                    <!-- Order Items -->
                    <div class="order-items">
                        <h3 class="section-title">Sản phẩm đã đặt</h3>
                        <?php if (!empty($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <i class="fas fa-laptop"></i>
                                </div>
                                <div class="item-details">
                                    <h6 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <p class="item-category">SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="item-price"><?php echo number_format($item['product_price'], 0, ',', '.'); ?>₫</span>
                                        <span class="item-quantity">Số lượng: <?php echo $item['quantity']; ?></span>
                                    </div>
                                    <?php if ($order['order_status'] === 'delivered' && !hasReviewed($db, $user_id, $item['product_id'], $item['id'])): ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn-custom btn-outline-custom" onclick="openReviewModal(<?php echo $item['id']; ?>, <?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars($item['product_name']); ?>')">
                                                <i class="fas fa-star"></i>Đánh giá sản phẩm
                                            </button>
                                        </div>
                                    <?php elseif ($order['order_status'] === 'delivered'): ?>
                                        <div class="mt-2">
                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Đã đánh giá</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <p class="text-muted">Không có sản phẩm trong đơn hàng này</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Summary -->
                    <div class="order-summary">
                        <h3 class="section-title">Tổng quan đơn hàng</h3>
                        <div class="summary-row">
                            <span class="summary-label">Tạm tính:</span>
                            <span class="summary-value"><?php echo number_format($order['subtotal'], 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="summary-row">
                            <span class="summary-label">Giảm giá:</span>
                            <span class="summary-value">-<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row">
                            <span class="summary-label">Phí vận chuyển:</span>
                            <span class="summary-value"><?php echo number_format($order['shipping_fee'], 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php if ($order['tax_amount'] > 0): ?>
                        <div class="summary-row">
                            <span class="summary-label">Thuế (VAT):</span>
                            <span class="summary-value"><?php echo number_format($order['tax_amount'], 0, ',', '.'); ?>₫</span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-row summary-total">
                            <span>Tổng cộng:</span>
                            <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="order-actions">
                        <?php if ($order['order_status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="cancel_order" class="btn-custom btn-danger-custom" 
                                        onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                    <i class="fas fa-times"></i>Hủy đơn hàng
                                </button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>pages/user/orders.php" class="btn-custom btn-outline-custom">
                            <i class="fas fa-arrow-left"></i>Quay lại
                        </a>
                        <a href="<?php echo BASE_URL; ?>index.php" class="btn-custom btn-primary-custom">
                            <i class="fas fa-shopping-bag"></i>Tiếp tục mua sắm
                        </a>
                    </div>

                    <!-- Shipping Information -->
                    <?php if (!empty($order['shipping_address'])): ?>
                    <div class="order-timeline">
                        <h3 class="section-title">Thông tin giao hàng</h3>
                        <div class="mb-3">
                            <strong>Địa chỉ giao hàng:</strong>
                            <p class="mb-1"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <?php if (!empty($order['tracking_number'])): ?>
                        <div>
                            <strong>Mã theo dõi:</strong> <?php echo htmlspecialchars($order['tracking_number']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['shipping_method'])): ?>
                        <div>
                            <strong>Phương thức vận chuyển:</strong> <?php echo htmlspecialchars($order['shipping_method']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Order Timeline -->
                    <?php if (!empty($order_status_history)): ?>
                    <div class="order-timeline">
                        <h3 class="section-title">Lịch sử đơn hàng</h3>
                        <div class="timeline">
                            <?php foreach ($order_status_history as $history): ?>
                            <div class="timeline-item <?php echo $history['status'] === $order['order_status'] ? 'active' : ''; ?>">
                                <div class="timeline-date">
                                    <?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <?php 
                                    $status_messages = [
                                        'pending' => 'Đơn hàng đã được đặt',
                                        'confirmed' => 'Đơn hàng đã được xác nhận',
                                        'processing' => 'Đơn hàng đang được xử lý',
                                        'shipped' => 'Đơn hàng đang được giao',
                                        'delivered' => 'Đơn hàng đã được giao thành công',
                                        'cancelled' => 'Đơn hàng đã bị hủy'
                                    ];
                                    echo $status_messages[$history['status']] ?? 'Trạng thái cập nhật';
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Review Modal -->
    <div class="review-modal" id="reviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đánh giá sản phẩm</h5>
                <button type="button" class="btn-close" onclick="closeReviewModal()">×</button>
            </div>
            <form method="POST" id="reviewForm">
                <input type="hidden" name="order_item_id" id="order_item_id">
                <input type="hidden" name="product_id" id="product_id">
                <div class="modal-body">
                    <div id="reviewProductInfo" class="mb-3" style="font-size: 1.6rem; font-weight: 600; color: var(--dark);"></div>
                    <div class="form-group">
                        <label class="form-label">Đánh giá của bạn</label>
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star rating-star" data-rating="<?php echo $i; ?>" onclick="setRating(this)"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating" value="0">
                    </div>
                    <div class="form-group">
                        <label for="comment" class="form-label">Nhận xét</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Hãy chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom btn-outline-custom" onclick="closeReviewModal()">Hủy</button>
                    <button type="submit" name="submit_review" class="btn-custom btn-primary-custom">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Review Modal Functions
        let currentRating = 0;

        function openReviewModal(orderItemId, productId, productName) {
            document.getElementById('order_item_id').value = orderItemId;
            document.getElementById('product_id').value = productId;
            document.getElementById('reviewProductInfo').innerHTML = `<strong>Sản phẩm:</strong> ${productName}`;
            document.getElementById('reviewModal').classList.add('show');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('show');
            currentRating = 0;
            resetStars();
        }

        function setRating(starElement) {
            const rating = parseInt(starElement.getAttribute('data-rating'));
            currentRating = rating;
            document.getElementById('rating').value = rating;
            
            const stars = document.querySelectorAll('.rating-star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }

        function resetStars() {
            const stars = document.querySelectorAll('.rating-star');
            stars.forEach(star => {
                star.classList.remove('active');
            });
            document.getElementById('rating').value = 0;
            document.getElementById('comment').value = '';
        }

        // Close modal when clicking outside
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });

        // Validate review form
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            if (currentRating === 0) {
                e.preventDefault();
                alert('Vui lòng chọn số sao đánh giá!');
                return false;
            }
        });
    </script>
</body>
</html>