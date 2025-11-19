<?php
// pages/main/cart.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

$database = new Database();
$db = $database->getConnection();

// KHỞI TẠO CÁC BIẾN TRƯỚC KHI SỬ DỤNG
$success = '';
$error = '';
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

// Xử lý cập nhật giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
        }
        $success = 'Giỏ hàng đã được cập nhật!';
    } elseif (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $success = 'Sản phẩm đã được xóa khỏi giỏ hàng!';
        }
    } elseif (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        $success = 'Giỏ hàng đã được xóa!';
    } elseif (isset($_POST['add_to_cart'])) {
        $product_id = $_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if (function_exists('checkProductStock') && !checkProductStock($db, $product_id, $quantity)) {
            $error = 'Sản phẩm không đủ số lượng trong kho!';
        } else {
            // Sửa lại hàm addToCart để phù hợp với cấu trúc mới
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity
                ];
            }
            $success = 'Sản phẩm đã được thêm vào giỏ hàng!';
        }
    }
}

// Lấy thông tin giỏ hàng theo cấu trúc mới
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
                $cart_total += $price * $quantity;
            }
        }
    }
    $cart_count = count($cart_items);
}

// Xử lý "Mua ngay"
if (isset($_GET['buy_now']) && is_numeric($_GET['buy_now'])) {
    $product_id = $_GET['buy_now'];
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Giỏ Hàng - ShopNets</title>

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

  /* CART SECTION */
  .cart-section { padding: 2.4rem 0; flex: 1; }

  .cart-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    margin-bottom: 2.4rem;
  }

  .cart-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2rem 2.4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .cart-title {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
  }

  .cart-items { padding: 2.4rem; }

  .cart-item {
    display: flex;
    align-items: center;
    padding: 2rem 0;
    border-bottom: 1px dashed var(--border);
  }
  .cart-item:last-child { border-bottom: none; }

  .item-image {
    width: 110px;
    height: 110px;
    background: white;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 2.2rem;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
    margin-right: 2rem;
  }

  .item-details { flex: 1; }
  .item-name {
    font-weight: 600;
    font-size: 1.7rem;
    color: var(--dark);
    margin-bottom: .6rem;
  }
  .item-category {
    color: var(--gray);
    font-size: 1.4rem;
    margin-bottom: .8rem;
  }
  .item-price {
    font-weight: 700;
    color: var(--primary);
    font-size: 1.8rem;
  }

  .item-actions {
    display: flex;
    align-items: center;
    gap: 1.4rem;
  }

  .quantity-control {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--light);
  }
  .quantity-btn {
    width: 44px;
    height: 44px;
    background: transparent;
    border: none;
    color: var(--dark);
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
  }
  .quantity-btn:hover {
    background: var(--primary);
    color: white;
  }
  .quantity-input {
    width: 60px;
    height: 44px;
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 600;
    font-size: 1.5rem;
    color: var(--dark);
  }

  .remove-btn {
    width: 44px;
    height: 44px;
    background: transparent;
    border: 1.5px solid var(--danger);
    color: var(--danger);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
  }
  .remove-btn:hover {
    background: var(--danger);
    color: white;
  }

  /* CART SUMMARY */
  .cart-summary {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    padding: 2.4rem;
    position: sticky;
    top: 120px;
  }
  .summary-title {
    font-size: 1.9rem;
    font-weight: 700;
    margin-bottom: 1.8rem;
    padding-bottom: 1.2rem;
    border-bottom: 1px solid var(--border);
    color: var(--dark);
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
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    border-top: 1.5px solid var(--border);
    padding-top: 1.4rem;
    margin-top: 1.4rem;
  }

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
    text-decoration: none;
    display: block;
    text-align: center;
  }
  .checkout-btn:hover { 
    background: var(--primary-dark); 
    transform: translateY(-3px); 
    box-shadow: var(--shadow-lg); 
    color: white;
  }

  .continue-shopping,
  .clear-cart-btn {
    display: block;
    text-align: center;
    margin-top: 1.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.6rem;
    transition: var(--transition);
    background: none;
    border: none;
    cursor: pointer;
  }
  .continue-shopping:hover,
  .clear-cart-btn:hover {
    color: var(--primary-dark);
    text-decoration: underline;
  }

  /* EMPTY CART */
  .empty-cart {
    text-align: center;
    padding: 5rem 3rem;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    margin: 3rem 0;
  }
  .empty-cart-icon {
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
  .empty-cart-title {
    font-size: 2.4rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 1.5rem;
  }
  .empty-cart-text {
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

  /* BUTTONS */
  .btn-light {
    background: white;
    color: var(--primary);
    border: none;
    padding: 1rem 2rem;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1.6rem;
    transition: var(--transition);
  }
  .btn-light:hover {
    background: var(--light);
    color: var(--primary-dark);
  }

  .btn-primary {
    background: var(--primary);
    color: white;
    border: none;
    padding: 1.4rem 2.8rem;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: 1.6rem;
    transition: var(--transition);
    text-decoration: none;
    display: inline-block;
  }
  .btn-primary:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-2px);
  }

  /* RESPONSIVE */
  @media (max-width: 992px) {
    .cart-item {
      flex-direction: column;
      align-items: flex-start;
      gap: 1.6rem;
    }
    .item-image { margin-right: 0; margin-bottom: 1.2rem; }
    .item-actions { width: 100%; justify-content: space-between; }
    .cart-summary { position: static; }
  }
  @media (max-width: 768px) {
    .cart-header { flex-direction: column; gap: 1.5rem; text-align: center; padding: 1.8rem; }
    .cart-title { font-size: 2rem; }
  }
  @media (max-width: 576px) {
    .quantity-control { width: 100%; justify-content: center; }
    .quantity-input { width: 70px; }
    .item-actions { flex-direction: column; gap: 1rem; }
    .remove-btn { width: 100%; }
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
        <li class="breadcrumb-item active" aria-current="page">Giỏ hàng</li>
      </ol>
    </nav>
  </div>

  <!-- CART SECTION -->
  <section class="cart-section">
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

      <div class="row">
        <div class="col-lg-8">
          <?php if (!empty($cart_items)): ?>
            <form method="POST" id="cart-form">
              <div class="cart-card">
                <div class="cart-header">
                  <h3 class="cart-title">Sản phẩm trong giỏ (<?= $cart_count ?>)</h3>
                  <button type="submit" name="update_cart" class="btn-light">
                    <i class="bi bi-arrow-repeat me-2"></i>Cập nhật giỏ hàng
                  </button>
                </div>
                <div class="cart-items">
                  <?php foreach ($cart_items as $item): 
                    $product = $item['product'];
                  ?>
                  <div class="cart-item">
                    <div class="item-image">
                      <i class="bi bi-laptop"></i>
                    </div>
                    <div class="item-details">
                      <h5 class="item-name"><?= htmlspecialchars($product['name']) ?></h5>
                      <p class="item-category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
                      <div class="item-price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                    </div>
                    <div class="item-actions">
                      <div class="quantity-control">
                        <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?= $product['id'] ?>)">
                          <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" 
                               name="quantity[<?= $product['id'] ?>]" 
                               value="<?= $item['quantity'] ?>" 
                               min="1" 
                               max="<?= $product['quantity'] ?? 999 ?>"
                               class="quantity-input"
                               id="quantity-<?= $product['id'] ?>">
                        <button type="button" class="quantity-btn" onclick="increaseQuantity(<?= $product['id'] ?>, <?= $product['quantity'] ?? 999 ?>)">
                          <i class="bi bi-plus-lg"></i>
                        </button>
                      </div>
                      <button type="submit" name="remove_item" class="remove-btn" title="Xóa sản phẩm">
                        <i class="bi bi-trash-fill"></i>
                      </button>
                      <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </form>

            <div class="d-flex justify-content-between flex-wrap gap-3 mt-4">
              <a href="<?= BASE_URL ?>index.php" class="continue-shopping">
                <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
              </a>
              <form method="POST" class="d-inline">
                <button type="submit" name="clear_cart" class="clear-cart-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa toàn bộ giỏ hàng?')">
                  <i class="bi bi-trash me-2"></i>Xóa toàn bộ
                </button>
              </form>
            </div>
          <?php else: ?>
            <div class="empty-cart">
              <div class="empty-cart-icon">
                <i class="bi bi-cart-x"></i>
              </div>
              <h3 class="empty-cart-title">Giỏ hàng trống</h3>
              <p class="empty-cart-text">Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
              <a href="<?= BASE_URL ?>index.php" class="btn-primary">
                <i class="bi bi-bag-fill me-2"></i>Mua sắm ngay
              </a>
            </div>
          <?php endif; ?>
        </div>

        <?php if (!empty($cart_items)): ?>
        <div class="col-lg-4">
          <div class="cart-summary">
            <h4 class="summary-title">Tóm tắt đơn hàng</h4>
            
            <?php
            $shipping_fee = $cart_total > 2000000 ? 0 : 30000;
            $total = $cart_total + $shipping_fee;
            ?>
            
            <div class="summary-row">
              <span class="summary-label">Tạm tính:</span>
              <span class="summary-value"><?= number_format($cart_total, 0, ',', '.') ?>đ</span>
            </div>
            
            <div class="summary-row">
              <span class="summary-label">Phí vận chuyển:</span>
              <span class="summary-value">
                <?php if ($shipping_fee == 0): ?>
                  <span class="text-success">Miễn phí</span>
                <?php else: ?>
                  <?= number_format($shipping_fee, 0, ',', '.') ?>đ
                <?php endif; ?>
              </span>
            </div>
            
            <div class="summary-row">
              <span class="summary-label">Giảm giá:</span>
              <span class="summary-value">0đ</span>
            </div>
            
            <div class="summary-row summary-total">
              <span>Tổng cộng:</span>
              <span><?= number_format($total, 0, ',', '.') ?>đ</span>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="checkout.php" class="checkout-btn">
                <i class="bi bi-credit-card me-2"></i>Tiến hành thanh toán
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>auth/login.php?redirect=checkout" class="checkout-btn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để thanh toán
              </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>index.php" class="continue-shopping">
              <i class="bi bi-arrow-left me-2"></i>Tiếp tục mua sắm
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php 
  // Kiểm tra xem footer.php có tồn tại không
  if (file_exists('../../includes/footer.php')) {
      include '../../includes/footer.php'; 
  }
  ?>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function increaseQuantity(productId, maxQuantity) {
      const input = document.getElementById('quantity-' + productId);
      let value = parseInt(input.value);
      if (value < maxQuantity) {
        input.value = value + 1;
      } else {
        alert('Số lượng vượt quá tồn kho!');
      }
    }

    function decreaseQuantity(productId) {
      const input = document.getElementById('quantity-' + productId);
      let value = parseInt(input.value);
      if (value > 1) {
        input.value = value - 1;
      }
    }

    document.querySelectorAll('.quantity-input').forEach(input => {
      input.addEventListener('change', () => {
        if (input.value >= 1) {
          document.getElementById('cart-form').submit();
        }
      });
    });

    document.querySelectorAll('button[name="remove_item"]').forEach(btn => {
      btn.addEventListener('click', e => {
        if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
          e.preventDefault();
        }
      });
    });

    // Tự động submit form khi thay đổi số lượng
    document.querySelectorAll('.quantity-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        setTimeout(() => {
          document.getElementById('cart-form').submit();
        }, 100);
      });
    });
  </script>
</body>
</html>