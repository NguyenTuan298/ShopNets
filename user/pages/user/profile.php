<?php
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$user = getUserById($db, $user_id);

$update_success = false;
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if (empty($full_name) || empty($email)) {
            $update_error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        } else {
            if (isEmailExists($db, $email, $user_id)) {
                $update_error = 'Email này đã được sử dụng.';
            } else {
                updateUserProfile($db, $user_id, $full_name, $email, $phone, $address);
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $update_success = true;
                $user = getUserById($db, $user_id);
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (empty($current) || empty($new) || empty($confirm)) {
            $update_error = 'Vui lòng điền đầy đủ.';
        } elseif ($new !== $confirm) {
            $update_error = 'Mật khẩu xác nhận không khớp.';
        } elseif (!verifyPassword($current, $user['password'])) {
            $update_error = 'Mật khẩu hiện tại sai.';
        } else {
            changeUserPassword($db, $user_id, $new);
            $update_success = true;
        }
    }

    if (isset($_POST['cancel_order'])) {
        $order_id = $_POST['order_id'] ?? '';
        if ($order_id && cancelOrder($db, $order_id, $user_id)) {
            $update_success = true;
        } else {
            $update_error = 'Không thể hủy đơn hàng. Vui lòng thử lại.';
        }
    }
}

$orders = getUserOrders($db, $user_id);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - ShopNets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* === FONT TOÀN TRANG === */
        html { font-size: 70%; }
        
        /* FIX HEADER CHE CONTENT - SỬA LẠI CHO ĐÚNG */
        body {
            padding-top: 140px !important; /* Tăng lên để không che thanh danh mục */
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #2d3748;
            margin: 0;
            line-height: 1.7;
            min-height: 100vh;
        }

        .container {
            margin-top: 10px;
        }

        /* === PROFILE HEADER === */
        .profile-header {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.8rem;
            margin-bottom: 2.8rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5.2rem;
            font-weight: bold;
        }
        .profile-info h3 {
            margin: 0;
            font-size: 2.8rem;
            font-weight: 600;
            color: #1a202c;
        }
        .profile-info p {
            margin: 0.5rem 0 0;
            color: #718096;
            font-size: 2rem;
        }

        /* === TABS === */
        .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 2.8rem;
            gap: 2rem;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #4a5568;
            font-weight: 500;
            font-size: 1.9rem;
            padding: 1.2rem 0;
            position: relative;
        }
        .nav-tabs .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 3px;
            background: #3b82f6;
            transition: width 0.3s ease;
        }
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-link:hover {
            color: #3b82f6;
        }
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }

        /* === CONTENT CARD === */
        .profile-content {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.8rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* === FORM === */
        .form-label {
            font-weight: 600;
            color: #2d3748;
            font-size: 1.9rem;
            margin-bottom: 1rem;
        }
        .form-control {
            border: 1px solid #cbd5e0;
            border-radius: 12px;
            padding: 1.4rem 1.7rem;
            font-size: 1.9rem;
            height: 65px;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* === ALERT === */
        .alert {
            padding: 1.6rem 2rem;
            border-radius: 12px;
            font-size: 1.9rem;
            margin-bottom: 2.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 5px solid #10b981;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 5px solid #ef4444;
        }

        /* === ORDER CARD === */
        .order-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2.8rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .order-header {
            background: #f8fafc;
            padding: 2.4rem 2.8rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .order-info {
            display: flex;
            gap: 2.5rem;
            flex-wrap: wrap;
            font-size: 1.8rem;
        }
        .order-info-item span:first-child {
            color: #718096;
            font-weight: 500;
        }
        .order-info-item span:last-child {
            color: #1a202c;
            font-weight: 700;
            font-size: 2rem;
        }

        .order-items {
            padding: 2.8rem;
        }
        .order-item {
            display: flex;
            align-items: center;
            padding: 1.7rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 90px;
            height: 90px;
            background: #edf2f7;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.7rem;
        }
        .item-image i {
            font-size: 2.8rem;
            color: #4a5568;
        }
        .item-details h6 {
            margin: 0 0 0.7rem;
            font-size: 1.9rem;
            font-weight: 600;
            color: #1a202c;
        }
        .item-details h6 a {
            color: inherit;
            text-decoration: none;
        }
        .item-details h6 a:hover {
            color: #3b82f6;
        }
        .item-category {
            font-size: 1.7rem;
            color: #718096;
            margin-bottom: 0.7rem;
        }
        .item-price {
            color: #e53e3e;
            font-weight: 700;
            font-size: 2rem;
        }
        .item-quantity {
            color: #718096;
            font-size: 1.9rem;
        }

        .order-footer {
            background: #f8fafc;
            padding: 2.4rem 2.8rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .order-total {
            font-weight: 700;
            font-size: 2.1rem;
            color: #1a202c;
        }
        .order-status {
            padding: 0.7rem 1.4rem;
            border-radius: 50px;
            font-size: 1.6rem;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed, .status-processing { background: #dbeafe; color: #1e40af; }
        .status-shipped, .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* === BUTTONS === */
        .btn {
            border-radius: 12px;
            padding: 1.1rem 2.2rem;
            font-weight: 600;
            font-size: 1.9rem;
            min-height: 65px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-outline-primary, .btn-outline-danger {
            font-size: 1.7rem;
            padding: 0.9rem 1.8rem;
            min-height: 48px;
        }
        .btn-outline-primary {
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }
        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }
        .btn-outline-danger {
            color: #e53e3e;
            border: 1px solid #e53e3e;
        }
        .btn-outline-danger:hover {
            background: #e53e3e;
            color: white;
        }

        /* === EMPTY STATE === */
        .empty-state {
            text-align: center;
            padding: 6rem 2.4rem;
            background: #f8fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 16px;
        }
        .empty-state i {
            font-size: 5.5rem;
            color: #a0aec0;
            margin-bottom: 1.7rem;
        }
        .empty-state h3 {
            font-size: 2.8rem;
            color: #4a5568;
            margin-bottom: 1.2rem;
        }
        .empty-state p {
            color: #718096;
            font-size: 1.9rem;
            margin-bottom: 2.4rem;
        }

        /* === TIÊU ĐỀ TAB CONTENT === */
        .profile-content h2 {
            font-size: 2.8rem !important;
            margin-bottom: 1.8rem !important;
            color: #1a202c;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            body {
                padding-top: 160px !important; /* Tăng cho mobile */
            }
            
            .container {
                margin-top: 8px;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 4rem;
            }
            .order-info, .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            .order-actions {
                width: 100%;
                justify-content: center;
                gap: 1rem;
            }
            
            /* Responsive font sizes */
            .nav-tabs .nav-link { font-size: 1.7rem; }
            .form-label { font-size: 1.7rem; }
            .form-control { font-size: 1.7rem; }
            .alert { font-size: 1.7rem; }
            .order-info { font-size: 1.6rem; }
            .order-info-item span:last-child { font-size: 1.8rem; }
            .item-details h6 { font-size: 1.7rem; }
            .item-category { font-size: 1.5rem; }
            .item-price { font-size: 1.8rem; }
            .item-quantity { font-size: 1.7rem; }
            .order-total { font-size: 1.9rem; }
            .btn { font-size: 1.7rem; }
            .btn-outline-primary, .btn-outline-danger { font-size: 1.5rem; }
            .empty-state h3 { font-size: 2.4rem; }
            .empty-state p { font-size: 1.7rem; }
            .profile-content h2 { font-size: 2.4rem !important; }
        }

        @media (max-width: 576px) {
            body {
                padding-top: 180px !important; /* Tăng cho mobile nhỏ */
            }
            
            .container {
                margin-top: 5px;
            }
            
            .nav-tabs .nav-link { font-size: 1.6rem; }
            .form-label { font-size: 1.6rem; }
            .form-control { font-size: 1.6rem; }
            .alert { font-size: 1.6rem; }
            .empty-state h3 { font-size: 2.1rem; }
            .empty-state p { font-size: 1.6rem; }
            .profile-content h2 { font-size: 2.1rem !important; }
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- PROFILE HEADER -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h3>
                <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <!-- TABS -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
                    <i class="bi bi-person"></i> Thông tin
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders">
                    <i class="bi bi-bag"></i> Đơn hàng
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password">
                    <i class="bi bi-shield-lock"></i> Mật khẩu
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- THÔNG TIN -->
            <div class="tab-pane fade show active profile-content" id="info">
                <h2>Cập nhật thông tin</h2>

                <?php if ($update_success && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Cập nhật thành công!
                    </div>
                <?php endif; ?>
                <?php if ($update_error && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($update_error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" name="address" rows="4"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ĐƠN HÀNG -->
            <div class="tab-pane fade profile-content" id="orders">
                <h2>Đơn hàng của tôi</h2>

                <?php if ($update_success && isset($_POST['cancel_order'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Hủy đơn thành công!
                    </div>
                <?php endif; ?>
                <?php if ($update_error && isset($_POST['cancel_order'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($update_error) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php $items = getOrderItems($db, $order['id']); ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <div class="order-info-item">
                                        <span>Mã đơn</span>
                                        <span>#<?= $order['id'] ?></span>
                                    </div>
                                    <div class="order-info-item">
                                        <span>Ngày đặt</span>
                                        <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <div class="order-info-item">
                                        <span>Tổng tiền</span>
                                        <span><?= number_format($order['total_amount']) ?>₫</span>
                                    </div>
                                </div>
                                <div class="order-status status-<?= $order['order_status'] ?>">
                                    <?= ['pending'=>'Chờ xử lý','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipped'=>'Đang giao','delivered'=>'Hoàn thành','cancelled'=>'Đã hủy'][$order['order_status']] ?? 'Không xác định' ?>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php foreach ($items as $item): 
                                    $url = BASE_URL . 'pages/main/product_detail.php?id=' . $item['product_id'];
                                ?>
                                    <div class="order-item">
                                        <div class="item-image"><i class="bi bi-laptop"></i></div>
                                        <div class="item-details">
                                            <h6><a href="<?= $url ?>" target="_blank"><?= htmlspecialchars($item['product_name']) ?></a></h6>
                                            <p class="item-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></p>
                                            <div class="d-flex justify-content-between w-100">
                                                <span class="item-price"><?= number_format($item['price']) ?>₫</span>
                                                <span class="item-quantity">x<?= $item['quantity'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-footer">
                                <div class="order-total">Tổng: <?= number_format($order['total_amount']) ?>₫</div>
                                <div class="order-actions">
                                    <?php if (in_array($order['order_status'], ['pending', 'processing'])): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" name="cancel_order" class="btn btn-outline-danger"
                                                    onclick="return confirm('Hủy đơn #<?= $order['id'] ?>?')">
                                                <i class="bi bi-x-circle"></i> Hủy
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>pages/user/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-bag"></i>
                        <h3>Chưa có đơn hàng</h3>
                        <p>Bạn chưa đặt mua sản phẩm nào.</p>
                        <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">
                            <i class="bi bi-cart"></i> Mua sắm ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ĐỔI MẬT KHẨU -->
            <div class="tab-pane fade profile-content" id="password">
                <h2>Đổi mật khẩu</h2>

                <?php if ($update_success && isset($_POST['change_password'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Đổi mật khẩu thành công!
                    </div>
                <?php endif; ?>
                <?php if ($update_error && isset($_POST['change_password'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($update_error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu hiện tại *</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới *</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Xác nhận *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="bi bi-shield-lock"></i> Đổi mật khẩu
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
