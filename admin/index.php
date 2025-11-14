<?php 
$pageTitle = 'ShopNets';
$currentPage = 'dashboard';
$baseUrl = '';
require_once 'login/auth_check.php';
require_once 'includes/db_connect.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Get statistics from database
try {
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Revenue (from delivered orders)
    $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE order_status = 'delivered'");
    $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
    
} catch (Exception $e) {
    // Set default values if database query fails
    $totalProducts = 0;
    $totalOrders = 0;
    $totalUsers = 0;
    $revenue = 0;
}

// Format revenue to VND
function formatVND($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}
?>

    <section class="stats">
      <div class="card">
        <div class="card-header">
          <span>Total Products</span>
          <span class="icon">
            <img src="assets/images/icons/Total_Products.png" alt="Total Products">
          </span>
        </div>
        <div class="card-value"><?= number_format($totalProducts) ?></div>
      </div>

      <div class="card">
        <div class="card-header">
          <span>Total Orders</span>
          <span class="icon">
            <img src="assets/images/icons/Total_Orders.png" alt="Total Orders">
          </span>
        </div>
        <div class="card-value"><?= number_format($totalOrders) ?></div>
      </div>

      <div class="card">
        <div class="card-header">
          <span>Total Users</span>
          <span class="icon">
            <img src="assets/images/icons/Total_Users.png" alt="Total Users">
          </span>
        </div>
        <div class="card-value"><?= number_format($totalUsers) ?></div>
      </div>

      <div class="card">
        <div class="card-header">
          <span>Revenue</span>
          <span class="icon">
            <img src="assets/images/icons/Revenue.png" alt="Revenue">
          </span>
        </div>
        <div class="card-value"><?= formatVND($revenue) ?></div>
      </div>
    </section>
  </main>

<?php include 'includes/footer.php'; ?>
