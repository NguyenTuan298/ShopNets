<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// KHỞI TẠO CÁC BIẾN TRƯỚC KHI SỬ DỤNG
$order_success = false;
$order_error = '';
$cart_items = [];
$subtotal = 0;
$shipping_fee = 0;
$total = 0;
$user = [];

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
if (function_exists('getUserById')) {
    $user = getUserById($db, $user_id);
}

// Xử lý "Mua ngay"
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    if (function_exists('getProductById')) {
        $product = getProductById($db, $product_id);
        
        if ($product) {
            $_SESSION['cart'] = [
                $product_id => [
                    'product_id' => $product_id,
                    'quantity' => 1
                ]
            ];
            header('Location: checkout.php');
            exit;
        }
    }
}

// Lấy giỏ hàng từ session
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $cart_item) {
        if (function_exists('getProductById')) {
            $product = getProductById($db, $product_id);
            if ($product) {
                $quantity = $cart_item['quantity'] ?? 1;
                $cart_items[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $price = $product['price'];
                $subtotal += $price * $quantity;
            }
        }
    }
}

// Kiểm tra giỏ hàng trống
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$shipping_fee = $subtotal > 2000000 ? 0 : 30000;
$total = $subtotal + $shipping_fee;

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($payment_method)) {
        $order_error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } else {
        try {
            $db->beginTransaction();
            
            $order_number = 'ORD' . date('YmdHis') . rand(1000, 9999);
            
            $check_stmt = $db->prepare("SELECT id FROM orders WHERE order_number = ?");
            $check_stmt->execute([$order_number]);
            if ($check_stmt->fetch()) {
                $order_number = 'ORD' . date('YmdHis') . rand(1000, 9999);
            }
            
            $query = "INSERT INTO orders (order_number, user_id, customer_name, customer_email, customer_phone, shipping_address, payment_method, notes, subtotal, shipping_fee, total_amount) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $order_number, 
                $user_id, 
                $full_name, 
                $email, 
                $phone, 
                $address, 
                $payment_method, 
                $notes, 
                $subtotal, 
                $shipping_fee, 
                $total
            ]);
            
            $order_id = $db->lastInsertId();
            
            foreach ($cart_items as $item) {
                $product = $item['product'];
                $product_id = $product['id'];
                $product_name = $product['name'];
                $price = $product['price'];
                $quantity = $item['quantity'];
                $total_price = $price * $quantity;
                
                $query = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total_price) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$order_id, $product_id, $product_name, $price, $quantity, $total_price]);
            }
            
            unset($_SESSION['cart']);
            $db->commit();
            $order_success = true;
            
        } catch (PDOException $e) {
            $db->rollBack();
            $order_error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Thanh Toán - ShopNets</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

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

  /* CHECKOUT SECTION */
  .checkout-section { padding: 2.4rem 0; flex: 1; }

  .checkout-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 2.4rem;
  }

  .checkout-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2rem 2.4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .checkout-title {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
  }

  .checkout-steps {
    display: flex;
    align-items: center;
    gap: 3rem;
    font-size: 1.6rem;
  }

  .step {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: rgba(255,255,255,0.7);
  }
  .step.active { color: white; font-weight: 600; }

  .step-number {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 600;
  }
  .step.active .step-number { background: white; color: var(--primary); }

  .checkout-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
    padding: 2.4rem;
  }

  /* FORM STYLES */
  .form-section { margin-bottom: 3rem; }

  .section-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid var(--primary);
    display: inline-block;
  }

  .form-group { margin-bottom: 2rem; }

  .form-label {
    font-weight: 600;
    margin-bottom: 0.8rem;
    color: var(--dark);
    font-size: 1.6rem;
    display: block;
  }

  .checkout-input {
    width: 100%;
    padding: 1.4rem 1.8rem;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    font-size: 1.6rem;
    transition: var(--transition);
    background: var(--light);
  }
  .checkout-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    outline: none;
    background: white;
  }

  .checkout-textarea {
    width: 100%;
    padding: 1.4rem 1.8rem;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    font-size: 1.6rem;
    transition: var(--transition);
    background: var(--light);
    resize: vertical;
    min-height: 120px;
    font-family: inherit;
  }

  /* PAYMENT METHODS */
  .payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .payment-method {
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 1.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    background: var(--light);
  }
  .payment-method:hover { border-color: var(--primary); transform: translateY(-2px); }
  .payment-method.selected { border-color: var(--primary); background: rgba(37, 99, 235, 0.05); }

  .payment-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--gray);
  }
  .payment-method.selected .payment-icon { color: var(--primary); }

  .payment-name {
    font-weight: 600;
    font-size: 1.6rem;
    color: var(--dark);
  }

  /* ORDER SUMMARY */
  .order-summary {
    background: var(--light);
    border-radius: var(--radius-lg);
    padding: 2.4rem;
    border: 2px solid var(--border);
    position: sticky;
    top: 120px;
  }

  .order-items { margin-bottom: 2rem; }

  .order-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px dashed var(--border);
  }
  .order-item:last-child { border-bottom: none; }

  .item-image {
    width: 70px;
    height: 70px;
    background: white;
    border-radius: var(--radius);
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

  .item-price {
    color: var(--primary);
    font-weight: 700;
    font-size: 1.7rem;
  }

  .item-quantity {
    color: var(--gray);
    font-size: 1.5rem;
  }

  .order-totals {
    border-top: 2px solid var(--border);
    padding-top: 1.5rem;
    margin-top: 1.5rem;
  }

  .total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1.2rem;
    font-size: 1.6rem;
  }

  .total-label { color: var(--gray); }
  .total-value { font-weight: 600; color: var(--dark); }

  .total-final {
    border-top: 1.5px solid var(--border);
    padding-top: 1.2rem;
    margin-top: 1.2rem;
    font-size: 1.9rem;
    font-weight: 700;
    color: var(--primary);
  }

  /* BUTTONS */
  .checkout-btn {
    width: 100%;
    padding: 1.7rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    border-radius: var(--radius);
    font-weight: 700;
    font-size: 1.8rem;
    transition: var(--transition);
    box-shadow: var(--shadow);
    margin-top: 2rem;
    cursor: pointer;
  }
  .checkout-btn:hover { background: var(--primary-dark); transform: translateY(-3px); box-shadow: var(--shadow-lg); }

  .back-btn {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.6rem;
    transition: var(--transition);
  }
  .back-btn:hover { color: var(--primary-dark); text-decoration: underline; }

  /* ALERT */
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

  /* SUCCESS PAGE */
  .success-container {
    text-align: center;
    padding: 5rem 3rem;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin: 3rem 0;
  }

  .success-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--success);
    font-size: 3.5rem;
    margin: 0 auto 2.5rem;
    border: 3px solid var(--success);
  }

  .success-title {
    font-size: 2.8rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1.5rem;
  }

  .success-text {
    color: var(--gray);
    font-size: 1.8rem;
    margin-bottom: 3rem;
    line-height: 1.6;
  }

  .success-actions {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
  }

  .btn-success-custom {
    padding: 1.4rem 2.8rem;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1.6rem;
    text-decoration: none;
    transition: var(--transition);
    border: 2px solid transparent;
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

  /* BUY NOW NOTICE */
  .buy-now-notice {
    background: rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.3);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin: 0 2.4rem 2.4rem;
    display: flex;
    align-items: center;
    gap: 1.2rem;
    font-size: 1.6rem;
  }

  .buy-now-notice i {
    color: var(--primary);
    font-size: 2.2rem;
    flex-shrink: 0;
  }

  .buy-now-content h4 {
    color: var(--primary);
    margin-bottom: 0.5rem;
    font-size: 1.7rem;
  }

  .buy-now-content p {
    color: var(--dark);
    margin: 0;
    font-size: 1.5rem;
  }

  /* RESPONSIVE */
  @media (max-width: 992px) {
    .checkout-content { grid-template-columns: 1fr; gap: 2rem; }
    .order-summary { position: static; }
    .checkout-steps { gap: 1.5rem; flex-wrap: wrap; justify-content: center; }
  }

  @media (max-width: 768px) {
    .checkout-header { flex-direction: column; gap: 1.5rem; text-align: center; padding: 1.8rem; }
    .checkout-title { font-size: 2rem; }
    .payment-methods { grid-template-columns: 1fr 1fr; }
    .success-actions { flex-direction: column; align-items: center; }
    .btn-success-custom { width: 100%; max-width: 250px; }
  }

  @media (max-width: 576px) {
    .payment-methods { grid-template-columns: 1fr; }
    .checkout-steps { flex-direction: column; gap: 1rem; }
    .step { justify-content: center; }
    .order-item { flex-direction: column; text-align: center; gap: 1rem; }
    .item-details { text-align: center; }
  }
</style>

<body>
  <?php 
  // Kiểm tra xem header.php có tồn tại không
  if (file_exists('../../includes/header.php')) {
      include '../../includes/header.php'; 
  }
  ?>

  <!-- BREADCRUMB -->
  <div class="container mt-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>index.php">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="cart.php">Giỏ hàng</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
      </ol>
    </nav>
  </div>

  <!-- CHECKOUT SECTION -->
  <section class="checkout-section">
    <div class="container">
      <?php if (isset($order_success) && $order_success): ?>
        <!-- Success Message -->
        <div class="success-container">
          <div class="success-icon">
            <i class="bi bi-check-lg"></i>
          </div>
          <h2 class="success-title">Đặt hàng thành công!</h2>
          <p class="success-text">
            Cảm ơn bạn đã mua hàng tại SHOPNETS. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.<br>
            Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.
          </p>
          <div class="success-actions">
            <a href="<?= BASE_URL ?>index.php" class="btn-success-custom btn-primary-custom">
              <i class="bi bi-house-fill me-2"></i>Tiếp tục mua sắm
            </a>
            <a href="<?= BASE_URL ?>pages/user/orders.php" class="btn-success-custom btn-outline-custom">
              <i class="bi bi-bag-fill me-2"></i>Xem đơn hàng
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Checkout Header -->
        <div class="checkout-card">
          <div class="checkout-header">
            <h3 class="checkout-title">Thanh toán đơn hàng</h3>
            <div class="checkout-steps">
              <div class="step">
                <div class="step-number">1</div>
                <span>Giỏ hàng</span>
              </div>
              <div class="step active">
                <div class="step-number">2</div>
                <span>Thanh toán</span>
              </div>
              <div class="step">
                <div class="step-number">3</div>
                <span>Hoàn tất</span>
              </div>
            </div>
          </div>

          <?php if (isset($order_error) && !empty($order_error)): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <?= htmlspecialchars($order_error) ?>
            </div>
          <?php endif; ?>

          <!-- Thông báo "Mua ngay" -->
          <?php if (isset($_GET['product_id']) || (isset($cart_items) && is_array($cart_items) && count($cart_items) === 1)): ?>
            <div class="buy-now-notice">
              <i class="bi bi-lightning-charge-fill"></i>
              <div class="buy-now-content">
                <h4>Đang mua hàng nhanh</h4>
                <p>Sản phẩm này sẽ được giao ngay cho bạn sau khi đặt hàng thành công.</p>
              </div>
            </div>
          <?php endif; ?>

          <div class="checkout-content">
            <!-- Checkout Form -->
            <div class="checkout-form">
              <form method="POST" action="">
                <!-- Thông tin giao hàng -->
                <div class="form-section">
                  <h3 class="section-title">Thông tin giao hàng</h3>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="full_name" class="form-label">Họ và tên *</label>
                        <input type="text" class="checkout-input" id="full_name" name="full_name" 
                               value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="checkout-input" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="phone" class="form-label">Số điện thoại *</label>
                        <input type="tel" class="checkout-input" id="phone" name="phone" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="address" class="form-label">Địa chỉ *</label>
                        <input type="text" class="checkout-input" id="address" name="address" 
                               value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label for="notes" class="form-label">Ghi chú đơn hàng (tùy chọn)</label>
                    <textarea class="checkout-textarea" id="notes" name="notes" 
                              placeholder="Ví dụ: Giao hàng giờ hành chính, gọi điện trước khi giao..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                  </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="form-section">
                  <h3 class="section-title">Phương thức thanh toán</h3>
                  
                  <div class="payment-methods">
                    <div class="payment-method" data-method="cod">
                      <div class="payment-icon">
                        <i class="bi bi-cash-coin"></i>
                      </div>
                      <div class="payment-name">Thanh toán khi nhận hàng</div>
                    </div>
                    <div class="payment-method" data-method="bank_transfer">
                      <div class="payment-icon">
                        <i class="bi bi-bank"></i>
                      </div>
                      <div class="payment-name">Chuyển khoản ngân hàng</div>
                    </div>
                    <div class="payment-method" data-method="momo">
                      <div class="payment-icon">
                        <i class="bi bi-wallet2"></i>
                      </div>
                      <div class="payment-name">Ví MoMo</div>
                    </div>
                    <div class="payment-method" data-method="vnpay">
                      <div class="payment-icon">
                        <i class="bi bi-qr-code-scan"></i>
                      </div>
                      <div class="payment-name">VNPay</div>
                    </div>
                  </div>
                  
                  <input type="hidden" id="payment_method" name="payment_method" value="cod" required>
                  
                  <button type="submit" class="checkout-btn">
                    <i class="bi bi-bag-check-fill me-2"></i>Đặt hàng ngay
                  </button>
                  
                  <a href="cart.php" class="back-btn">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại giỏ hàng
                  </a>
                </div>
              </form>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
              <h3 class="section-title">Đơn hàng của bạn</h3>
              
              <div class="order-items">
                <?php if (isset($cart_items) && is_array($cart_items) && count($cart_items) > 0): ?>
                  <?php foreach ($cart_items as $item): 
                    $product = $item['product'];
                  ?>
                  <div class="order-item">
                    <div class="item-image">
                      <i class="bi bi-laptop"></i>
                    </div>
                    <div class="item-details">
                      <div class="item-name"><?= htmlspecialchars($product['name']) ?></div>
                      <div class="item-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                      <div class="item-quantity">Số lượng: <?= $item['quantity'] ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <p>Không có sản phẩm trong giỏ hàng</p>
                <?php endif; ?>
              </div>
              
              <div class="order-totals">
                <div class="total-row">
                  <span class="total-label">Tạm tính:</span>
                  <span class="total-value"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>
                <div class="total-row">
                  <span class="total-label">Phí vận chuyển:</span>
                  <span class="total-value">
                    <?php if ($shipping_fee == 0): ?>
                      <span class="text-success">Miễn phí</span>
                    <?php else: ?>
                      <?= number_format($shipping_fee, 0, ',', '.') ?>đ
                    <?php endif; ?>
                  </span>
                </div>
                <div class="total-row total-final">
                  <span class="total-label">Tổng cộng:</span>
                  <span class="total-value"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php 
  // Kiểm tra xem footer.php có tồn tại không
  if (file_exists('../../includes/footer.php')) {
      include '../../includes/footer.php'; 
  }
  ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Xử lý chọn phương thức thanh toán
    document.querySelectorAll('.payment-method').forEach(method => {
      method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(item => {
          item.classList.remove('selected');
        });
        this.classList.add('selected');
        document.getElementById('payment_method').value = this.getAttribute('data-method');
      });
    });

    // Chọn mặc định phương thức COD
    document.querySelector('.payment-method[data-method="cod"]').classList.add('selected');
  </script>
</body>
</html>