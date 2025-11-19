<?php
// pages/user/orders.php
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

// KHỞI TẠO CÁC BIẾN TRƯỚC KHI SỬ DỤNG
$success = '';
$error = '';
$orders = [];

// Lấy danh sách đơn hàng của user
$user_id = $_SESSION['user_id'];

// Sử dụng hàm an toàn để lấy đơn hàng
try {
    if (function_exists('getOrdersByUserId')) {
        $orders = getOrdersByUserId($db, $user_id);
    } else {
        // Fallback query nếu hàm không tồn tại
        $stmt = $db->prepare("
            SELECT o.* 
            FROM orders o 
            WHERE o.user_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = 'Lỗi khi tải danh sách đơn hàng: ' . $e->getMessage();
}

// Xử lý hủy đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    
    try {
        // Kiểm tra xem đơn hàng thuộc về user hiện tại và có thể hủy không
        $stmt = $db->prepare("SELECT order_status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && in_array($order['order_status'], ['pending', 'confirmed'])) {
            $update_stmt = $db->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ? AND user_id = ?");
            if ($update_stmt->execute([$order_id, $user_id])) {
                $success = 'Đã hủy đơn hàng thành công!';
                // Reload danh sách đơn hàng
                $orders = getOrdersByUserId($db, $user_id);
            } else {
                $error = 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại.';
            }
        } else {
            $error = 'Không thể hủy đơn hàng này.';
        }
    } catch (Exception $e) {
        $error = 'Lỗi hệ thống: ' . $e->getMessage();
    }
}

// Xử lý đánh giá sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $order_item_id = $_POST['order_item_id'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($order_item_id) || empty($product_id) || $rating == 0) {
        $error = 'Vui lòng chọn số sao đánh giá.';
    } else {
        try {
            // Kiểm tra xem user có quyền đánh giá sản phẩm này không
            $check_stmt = $db->prepare("
                SELECT oi.* 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.id = ? AND oi.product_id = ? AND o.user_id = ? AND o.order_status = 'delivered'
            ");
            $check_stmt->execute([$order_item_id, $product_id, $user_id]);
            
            if ($check_stmt->fetch()) {
                // Kiểm tra xem đã đánh giá chưa
                $review_check = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_item_id = ?");
                $review_check->execute([$user_id, $product_id, $order_item_id]);
                
                if (!$review_check->fetch()) {
                    // Thêm đánh giá
                    $insert_stmt = $db->prepare("
                        INSERT INTO reviews (user_id, product_id, order_item_id, rating, comment, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    if ($insert_stmt->execute([$user_id, $product_id, $order_item_id, $rating, $comment])) {
                        $success = 'Cảm ơn bạn đã đánh giá sản phẩm!';
                    } else {
                        $error = 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.';
                    }
                } else {
                    $error = 'Bạn đã đánh giá sản phẩm này rồi.';
                }
            } else {
                $error = 'Không tìm thấy sản phẩm để đánh giá.';
            }
        } catch (Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Hàng Của Tôi - SHOPNETS</title>
    
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html {
            font-size: 68%;
            line-height: 1.7rem;
            font-family: 'Inter', 'Roboto', sans-serif;
        }
        body {
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 80px;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --danger: #ef4444;
            --success: #10b981;
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
            margin-bottom: 3rem;
            border-radius: var(--radius-lg);
        }

        .page-title {
            font-size: 3.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.8rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* ORDER CARD */
        .order-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2.4rem;
            border: 2px solid var(--border);
            transition: var(--transition);
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem 2.4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .order-info {
            display: flex;
            gap: 3rem;
            flex-wrap: wrap;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .order-info-item span:first-child {
            font-size: 1.4rem;
            opacity: 0.9;
        }

        .order-info-item span:last-child {
            font-size: 1.6rem;
            font-weight: 600;
        }

        .order-status {
            padding: 0.8rem 1.6rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.4rem;
            background: rgba(255,255,255,0.2);
        }

        .status-pending { background: var(--warning); }
        .status-confirmed { background: #3b82f6; }
        .status-processing { background: #8b5cf6; }
        .status-shipped { background: #f59e0b; }
        .status-delivered { background: var(--success); }
        .status-cancelled { background: var(--danger); }

        .order-items {
            padding: 2.4rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px dashed var(--border);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            background: var(--light);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
            color: var(--primary);
            font-size: 2rem;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .item-category {
            color: var(--gray);
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.6rem;
        }

        .item-quantity {
            color: var(--gray);
            font-size: 1.4rem;
        }

        .order-footer {
            background: var(--light);
            padding: 1.8rem 2.4rem;
            border-top: 2px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .order-total {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* BUTTONS */
        .btn-sm {
            padding: 0.8rem 1.6rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1.4rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }

        .btn-outline-danger {
            border: 2px solid var(--danger);
            color: var(--danger);
            background: transparent;
        }

        .btn-outline-danger:hover {
            background: var(--danger);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
            border: none;
        }

        .btn-warning:hover {
            background: #e58e0b;
            color: white;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 5rem 3rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }

        .empty-state-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(148, 163, 184, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray);
            font-size: 3.5rem;
            margin: 0 auto 2.5rem;
            border: 3px solid var(--gray);
        }

        .empty-state-title {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
        }

        .empty-state-text {
            color: var(--gray);
            font-size: 1.8rem;
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        /* ALERT */
        .alert {
            border-radius: var(--radius);
            padding: 1.5rem 1.8rem;
            margin-bottom: 2.4rem;
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

        /* REVIEW MODAL */
        .review-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .review-modal.show {
            display: flex;
        }

        .review-modal .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            max-width: 500px;
            width: 90%;
            overflow: hidden;
        }

        .review-modal .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-bottom: none;
        }

        .review-modal .modal-body {
            padding: 2rem;
        }

        .review-product {
            background: var(--light);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border);
        }

        .rating-stars {
            display: flex;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .rating-star {
            font-size: 2rem;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
        }

        .rating-star.active,
        .rating-star:hover {
            color: var(--warning);
            transform: scale(1.1);
        }

        .review-modal textarea {
            width: 100%;
            padding: 1.2rem;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1.5rem;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .review-modal .modal-footer {
            padding: 1.5rem 2rem;
            background: var(--light);
            border-top: 1px solid var(--border);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }
            
            .order-info {
                gap: 1.5rem;
            }
            
            .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .item-meta {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 2.4rem;
            }
            
            .order-info {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php 
    if (file_exists('../../includes/header.php')) {
        include '../../includes/header.php'; 
    }
    ?>

    <!-- BREADCRUMB -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>pages/user/profile.php">Tài khoản</a></li>
                <li class="breadcrumb-item active">Đơn hàng của tôi</li>
            </ol>
        </nav>
    </div>

    <!-- PAGE HEADER -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Đơn Hàng Của Tôi</h1>
            <p class="page-subtitle">Theo dõi và quản lý đơn hàng của bạn</p>
        </div>
    </section>

    <!-- ORDERS SECTION -->
    <section class="orders-section">
        <div class="container">
            <?php if (isset($error) && !empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success) && !empty($success)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="order-info-item">
                                    <span>Mã đơn hàng</span>
                                    <span>#<?= htmlspecialchars($order['order_number'] ?? $order['id']) ?></span>
                                </div>
                                <div class="order-info-item">
                                    <span>Ngày đặt</span>
                                    <span><?= !empty($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'N/A' ?></span>
                                </div>
                                <div class="order-info-item">
                                    <span>Tổng tiền</span>
                                    <span><?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>đ</span>
                                </div>
                            </div>
                            <div class="order-status status-<?= strtolower($order['order_status'] ?? 'pending') ?>">
                                <?php
                                $status_text = [
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipped' => 'Đang giao hàng',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy'
                                ];
                                $status = strtolower($order['order_status'] ?? 'pending');
                                echo $status_text[$status] ?? 'Chờ xác nhận';
                                ?>
                            </div>
                        </div>

                        <div class="order-items">
                            <?php
                            // Lấy chi tiết đơn hàng
                            $order_items = [];
                            try {
                                $items_stmt = $db->prepare("
                                    SELECT oi.*, p.name as product_name, c.name as category_name 
                                    FROM order_items oi 
                                    LEFT JOIN products p ON oi.product_id = p.id 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE oi.order_id = ?
                                ");
                                $items_stmt->execute([$order['id']]);
                                $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Exception $e) {
                                // Log error but don't show to user
                            }
                            
                            if (!empty($order_items)):
                                foreach ($order_items as $item):
                            ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div class="item-details">
                                        <div class="item-name"><?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm') ?></div>
                                        <div class="item-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></div>
                                        <div class="item-meta">
                                            <span class="item-price"><?= number_format($item['product_price'] ?? 0, 0, ',', '.') ?>đ</span>
                                            <span class="item-quantity">Số lượng: <?= $item['quantity'] ?? 1 ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <div class="text-center py-3">
                                    <p class="text-muted">Không có sản phẩm trong đơn hàng</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">
                                Tổng cộng: <?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>đ
                            </div>
                            <div class="order-actions">
                                <?php if (in_array($order['order_status'] ?? '', ['pending', 'confirmed'])): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="cancel_order" class="btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                            <i class="bi bi-x-circle"></i>Hủy đơn
                                        </button>
                                    </form>
                                <?php elseif (($order['order_status'] ?? '') === 'delivered'): ?>
                                    <button type="button" class="btn-sm btn-warning" onclick="openReviewModal(<?= $order['id'] ?>)">
                                        <i class="bi bi-star"></i>Đánh giá
                                    </button>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>pages/user/order-detail.php?id=<?= $order['id'] ?>" 
                                   class="btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-bag-x"></i>
                    </div>
                    <h3 class="empty-state-title">Chưa có đơn hàng</h3>
                    <p class="empty-state-text">Bạn chưa đặt mua sản phẩm nào. Hãy khám phá ngay!</p>
                    <a href="<?= BASE_URL ?>pages/main/products.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-bag me-2"></i>Mua sắm ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- REVIEW MODAL -->
    <div class="review-modal" id="reviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đánh giá sản phẩm</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeReviewModal()"></button>
            </div>
            <form method="POST" id="reviewForm">
                <input type="hidden" name="order_item_id" id="order_item_id">
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="rating" id="rating_input">
                <div class="modal-body">
                    <div id="reviewProducts">
                        <p class="text-center">Đang tải sản phẩm...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Hủy</button>
                    <button type="submit" name="submit_review" class="btn btn-primary">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>

    <?php 
    if (file_exists('../../includes/footer.php')) {
        include '../../includes/footer.php'; 
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openReviewModal(orderId) {
            // Trong thực tế, bạn sẽ gọi API để lấy danh sách sản phẩm
            // Ở đây tôi sẽ tạo demo UI
            const container = document.getElementById('reviewProducts');
            container.innerHTML = `
                <div class="review-product">
                    <h6>Sản phẩm mẫu</h6>
                    <div class="rating-stars">
                        <i class="bi bi-star rating-star" data-rating="1" onclick="setRating(this, 1, 1)"></i>
                        <i class="bi bi-star rating-star" data-rating="2" onclick="setRating(this, 1, 1)"></i>
                        <i class="bi bi-star rating-star" data-rating="3" onclick="setRating(this, 1, 1)"></i>
                        <i class="bi bi-star rating-star" data-rating="4" onclick="setRating(this, 1, 1)"></i>
                        <i class="bi bi-star rating-star" data-rating="5" onclick="setRating(this, 1, 1)"></i>
                    </div>
                    <textarea name="comment" placeholder="Nhập đánh giá của bạn..." required></textarea>
                </div>
            `;
            document.getElementById('reviewModal').classList.add('show');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('show');
        }

        function setRating(element, orderItemId, productId) {
            const stars = element.parentElement.querySelectorAll('.rating-star');
            const rating = parseInt(element.getAttribute('data-rating'));
            
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill');
                } else {
                    star.classList.remove('active');
                    star.classList.remove('bi-star-fill');
                    star.classList.add('bi-star');
                }
            });
            
            document.getElementById('order_item_id').value = orderItemId;
            document.getElementById('product_id').value = productId;
            document.getElementById('rating_input').value = rating;
        }

        // Đóng modal khi click bên ngoài
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });
    </script>
</body>
</html>