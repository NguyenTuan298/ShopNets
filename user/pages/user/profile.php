<?php
// pages/user/profile.php - ĐÃ CHỈNH GIỐNG ORDERS.PHP
session_start();

// Define BASE_URL before including any files that use it
if (!defined('BASE_URL')) {
    // Determine base URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))); // Go up to root from user/pages/user/
    $base_url = rtrim($protocol . '://' . $host . $script_path, '/') . '/';
    define('BASE_URL', $base_url);
}

// Now include the database and other files
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

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
    
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html {
            font-size: 62.5%;
            line-height: 1.6rem;
            font-family: 'Inter', 'Roboto', sans-serif;
        }
        body {
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

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

        .container { max-width: 1200px; }

        /* BREADCRUMB - GIỐNG ORDERS */
        .breadcrumb {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-sm);
            font-size: 1.4rem !important;
            margin-bottom: 2.4rem;
        }
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.4rem !important;
        }
        .breadcrumb-item.active { 
            color: var(--dark); 
            font-weight: 500;
            font-size: 1.4rem !important;
        }

        /* PAGE HEADER - GIỐNG ORDERS */
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
            font-size: 1.6rem;
            opacity: 0.9;
            font-weight: 400;
        }

        /* PROFILE HEADER */
        .profile-header {
            background: white;
            border-radius: var(--radius-lg);
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 2.5rem;
            border: 1px solid var(--border);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: bold;
            flex-shrink: 0;
        }
        .profile-info h3 {
            margin: 0;
            font-size: 2.8rem;
            font-weight: 600;
            color: var(--dark);
        }
        .profile-info p {
            margin: 0.5rem 0 0;
            color: var(--gray);
            font-size: 1.6rem;
        }

        /* TABS - GIỐNG ORDERS STYLE */
        .nav-tabs {
            border-bottom: 2px solid var(--border);
            margin-bottom: 3rem;
            gap: 2rem;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #4a5568;
            font-weight: 500;
            font-size: 1.6rem;
            padding: 1.2rem 0;
            position: relative;
            background: none;
        }
        .nav-tabs .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary);
            transition: var(--transition);
        }
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-link:hover {
            color: var(--primary);
            background: none;
            border: none;
        }
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }

        /* CONTENT CARD - GIỐNG ORDERS */
        .profile-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 3rem;
            box-shadow: var(--shadow);
            margin-bottom: 3rem;
            border: 1px solid var(--border);
        }

        /* FORM - GIỐNG ORDERS STYLE */
        .form-label {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        .form-control {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.2rem 1.5rem;
            font-size: 1.6rem;
            height: 50px;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        textarea.form-control {
            height: auto;
            min-height: 120px;
            resize: vertical;
        }

        /* ALERT - GIỐNG ORDERS */
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

        /* ORDER CARD - GIỐNG ORDERS */
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
            font-size: 1.4rem;
        }

        .order-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .order-info-item span:first-child {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .order-info-item span:last-child {
            font-size: 1.4rem;
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
        .status-shipped { background: var(--warning); }
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

        /* BUTTONS - GIỐNG ORDERS */
        .btn {
            border-radius: var(--radius);
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.4rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
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

        /* EMPTY STATE - GIỐNG ORDERS */
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
            font-size: 1.6rem;
            margin-bottom: 3rem;
            line-height: 1.6;
        }

        /* SECTION TITLES - GIỐNG ORDERS */
        .profile-content h2 {
            font-size: 2.4rem !important;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 2rem !important;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }

        /* RESPONSIVE - GIỐNG ORDERS */
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 3rem;
            }
            
            .profile-info h3 {
                font-size: 2.4rem;
            }
            
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

            .nav-tabs .nav-link {
                font-size: 1.4rem;
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
            
            .profile-content {
                padding: 2rem;
            }
            
            .profile-content h2 {
                font-size: 2rem !important;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Include header using the dynamically determined path
    $header_path = dirname(dirname(dirname(__FILE__))) . '/includes/header.php';
    if (file_exists($header_path)) {
        include $header_path;
    }
    ?>

    <!-- BREADCRUMB - GIỐNG ORDERS -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>pages/user/profile.php">Tài khoản</a></li>
                <li class="breadcrumb-item active">Hồ sơ cá nhân</li>
            </ol>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- PROFILE HEADER -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h3>
                <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <?php if (!empty($user['phone'])): ?>
                    <p><i class="bi bi-phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- TABS -->
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
                    <i class="bi bi-person"></i> Thông tin cá nhân
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders">
                    <i class="bi bi-bag"></i> Đơn hàng của tôi
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#password">
                    <i class="bi bi-shield-lock"></i> Đổi mật khẩu
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- THÔNG TIN CÁ NHÂN -->
            <div class="tab-pane fade show active profile-content" id="info">
                <h2>Thông tin cá nhân</h2>

                <?php if ($update_success && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Cập nhật thông tin thành công!
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
                            <textarea class="form-control" name="address" rows="4" placeholder="Nhập địa chỉ của bạn..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save"></i> Cập nhật thông tin
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
                        <i class="bi bi-check-circle"></i> Hủy đơn hàng thành công!
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
                                        <span>Mã đơn hàng</span>
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
                                    <?= [
                                        'pending' => 'Chờ xác nhận',
                                        'confirmed' => 'Đã xác nhận', 
                                        'processing' => 'Đang xử lý',
                                        'shipped' => 'Đang giao hàng',
                                        'delivered' => 'Đã giao',
                                        'cancelled' => 'Đã hủy'
                                    ][$order['order_status']] ?? 'Chờ xác nhận' ?>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php foreach ($items as $item): 
                                    $url = BASE_URL . 'pages/main/product-detail.php?id=' . $item['product_id'];
                                ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <i class="bi bi-laptop"></i>
                                        </div>
                                        <div class="item-details">
                                            <div class="item-name">
                                                <a href="<?= $url ?>" target="_blank"><?= htmlspecialchars($item['product_name']) ?></a>
                                            </div>
                                            <div class="item-category"><?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></div>
                                            <div class="item-meta">
                                                <span class="item-price"><?= number_format($item['price']) ?>₫</span>
                                                <span class="item-quantity">Số lượng: <?= $item['quantity'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-footer">
                                <div class="order-total">
                                    Tổng cộng: <?= number_format($order['total_amount']) ?>₫
                                </div>
                                <div class="order-actions">
                                    <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" name="cancel_order" class="btn btn-outline-danger"
                                                    onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #<?= $order['id'] ?>?')">
                                                <i class="bi bi-x-circle"></i> Hủy đơn
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
                        <div class="empty-state-icon">
                            <i class="bi bi-bag-x"></i>
                        </div>
                        <h3 class="empty-state-title">Chưa có đơn hàng</h3>
                        <p class="empty-state-text">Bạn chưa đặt mua sản phẩm nào. Hãy khám phá ngay!</p>
                        <a href="<?= BASE_URL ?>pages/main/products.php" class="btn btn-primary">
                            <i class="bi bi-bag me-2"></i> Mua sắm ngay
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
                            <label class="form-label">Xác nhận mật khẩu *</label>
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

    <?php 
    // Include footer using the dynamically determined path
    $footer_path = dirname(dirname(dirname(__FILE__))) . '/includes/footer.php';
    if (file_exists($footer_path)) {
        include $footer_path;
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>