<?php 
session_start();
// Set admin session for testing (remove in production)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}
$pageTitle = 'Order Management';
$currentPage = 'orders';
$baseUrl = '../';
include '../includes/header.php';
include '../includes/sidebar.php';
require_once 'order_controller.php';

// Get order statistics
$stats = $orderController->getOrderStats();

// Get filters from URL
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'date_range' => $_GET['date_range'] ?? ''
];

// Get order list
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$orders = $orderController->getAllOrders($page, $limit, $filters);
$totalOrders = $orderController->countOrders($filters);
$totalPages = ceil($totalOrders / $limit);

// Helper functions
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'orders-badge-pending',
        'confirmed' => 'orders-badge-confirmed', 
        'processing' => 'orders-badge-processing',
        'shipped' => 'orders-badge-shipped',
        'delivered' => 'orders-badge-delivered',
        'cancelled' => 'orders-badge-cancelled'
    ];
    return $classes[$status] ?? 'orders-badge-pending';
}

function getStatusText($status) {
    $texts = [
        'pending' => 'Chờ Duyệt',
        'confirmed' => 'Đã Xác Nhận',
        'processing' => 'Đang Xử Lý', 
        'shipped' => 'Đã Giao',
        'delivered' => 'Hoàn Thành',
        'cancelled' => 'Đã Hủy'
    ];
    return $texts[$status] ?? 'Không Xác Định';
}
?>
<link rel="stylesheet" href="../assets/css/pages/orders.css">

  <section class="content orders-page">
      <div class="content-header">
        <h1>Quản Lý Đơn Hàng</h1>
        <p class="content-description">Quản lý và theo dõi tất cả đơn hàng của khách hàng</p>
      </div>

      <section class="stats">
        <div class="orders-card <?= ($filters['status'] === 'pending') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Đơn Hàng Chờ Duyệt</span>
            <span class="icon">
              <i class="fas fa-clock"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Chờ Xác Nhận</div>
          <div class="orders-card-value blue"><?= $stats['pending'] ?></div>
        </div>


        <div class="orders-card <?= ($filters['status'] === 'shipped') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Đơn Hàng Đã Giao</span>
            <span class="icon">
              <i class="fas fa-check-circle"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Giao Hàng Thành Công</div>
          <div class="orders-card-value green"><?= $stats['shipped'] ?></div>
        </div>

        <div class="orders-card <?= ($filters['status'] === 'cancelled') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Đơn Hàng Đã Hủy</span>
            <span class="icon">
              <i class="fas fa-times-circle"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Hủy Đơn Hàng</div>
          <div class="orders-card-value red"><?= $stats['cancelled'] ?></div>
        </div>
      </section>

      <div class="orders-filter-card">
        <div class="orders-filter-header">
          <i class="fas fa-filter"></i> Tìm Kiếm & Lọc Đơn Hàng
        </div>
        <form method="GET" class="filters-form">
          <div class="filters-row">
            <div class="search-box">
              <input type="text" name="search" placeholder="Tìm kiếm theo số đơn hàng, tên khách hàng hoặc email..." 
                     value="<?= htmlspecialchars($filters['search']) ?>" 
                     onkeypress="if(event.key==='Enter') this.form.submit()">
            </div>
            <div class="select">
              <select name="status" onchange="this.form.submit()">
                <option value="">Tất Cả Trạng Thái</option>
                <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Chờ Duyệt</option>
                <option value="confirmed" <?= ($filters['status'] === 'confirmed') ? 'selected' : '' ?>>Đã Xác Nhận</option>
                <option value="processing" <?= ($filters['status'] === 'processing') ? 'selected' : '' ?>>Đang Xử Lý</option>
                <option value="shipped" <?= ($filters['status'] === 'shipped') ? 'selected' : '' ?>>Đã Giao</option>
                <option value="delivered" <?= ($filters['status'] === 'delivered') ? 'selected' : '' ?>>Hoàn Thành</option>
                <option value="cancelled" <?= ($filters['status'] === 'cancelled') ? 'selected' : '' ?>>Đã Hủy</option>
              </select>
            </div>
            <div class="select">
              <select name="date_range" onchange="this.form.submit()">
                <option value="">Tất Cả Thời Gian</option>
                <option value="today" <?= ($filters['date_range'] === 'today') ? 'selected' : '' ?>>Hôm Nay</option>
                <option value="week" <?= ($filters['date_range'] === 'week') ? 'selected' : '' ?>>7 Ngày Qua</option>
                <option value="month" <?= ($filters['date_range'] === 'month') ? 'selected' : '' ?>>30 Ngày Qua</option>
                <option value="3months" <?= ($filters['date_range'] === '3months') ? 'selected' : '' ?>>90 Ngày Qua</option>
              </select>
            </div>

          </div>
        </form>
      </div>

      <!-- Orders Table -->
      <div class="orders-table-card">
        <div class="orders-table-header">
          <div class="table-title">
            <h2><i class="fas fa-list"></i> Tất Cả Đơn Hàng</h2>
            <span class="orders-count">
              Hiện thị <?= count($orders) ?> trong tổng số <?= $totalOrders ?> đơn hàng
            </span>
          </div>
        </div>
        
        <?php if (empty($orders)): ?>
          <div class="no-orders">
            <i class="fas fa-shopping-cart"></i>
            <h3>Không Tìm Thấy Đơn Hàng</h3>
            <p>Không tìm thấy đơn hàng nào phù hợp với tiêu chí tìm kiếm hiện tại.</p>
          </div>
        <?php else: ?>
        <div class="orders-table-container">
          <table class="orders-data-table">
            <thead>
              <tr>
                <th>Số Đơn Hàng</th>
                <th>Khách Hàng</th>
                <th>Sản Phẩm</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th>Ngày Đặt</th>
                <th>Hành Động</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td>
                  <div class="order-number">
                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                  </div>
                </td>
                <td>
                  <div class="orders-customer-info">
                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                    <div class="orders-customer-email"><?= htmlspecialchars($order['customer_email']) ?></div>
                  </div>
                </td>
                <td>
                  <div class="orders-products">
                    <span class="product-count"><?= $order['total_items'] ?> sản phẩm</span>
                    <?php if ($order['product_names']): ?>
                      <div class="product-preview" title="<?= htmlspecialchars($order['product_names']) ?>">
                        <?= htmlspecialchars(substr($order['product_names'], 0, 30)) ?>...
                      </div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="order-total"><?= number_format($order['total_amount'], 0, ',', '.') ?> ₫</td>
                <td>
                  <span class="orders-badge <?= getStatusBadgeClass($order['order_status']) ?>">
                    <?= getStatusText($order['order_status']) ?>
                  </span>
                </td>
                <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                <td>
                  <div class="orders-actions-buttons">
                    <a href="view_order.php?id=<?= $order['id'] ?>" 
                       class="orders-btn-icon orders-btn-view" title="View Details">
                      <img src="../assets/images/icons/view.png" alt="View" width="16" height="16">
                    </a>
                    <?php if (in_array($order['order_status'], ['pending', 'cancelled'])): ?>
                      <button class="orders-btn-icon orders-btn-delete" 
                              title="Delete Order"
                              onclick="deleteOrder(<?= $order['id'] ?>)">
                        <img src="../assets/images/icons/delete.png" alt="Delete" width="16" height="16">
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
          <div class="pagination">
            <?php if ($page > 1): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" 
                 class="page-btn">&laquo; Trước</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" 
                 class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" 
                 class="page-btn">Tiếp &raquo;</a>
            <?php endif; ?>
          </div>
          <div class="pagination-info">
            Trang <?= $page ?> / <?= $totalPages ?> (<?= $totalOrders ?> đơn hàng)
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>

<script src="../assets/js/order.js"></script>
<script>


// Delete order functionality
function deleteOrder(orderId) {
    if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
        const formData = new FormData();
        formData.append('action', 'delete_order');
        formData.append('order_id', orderId);
        
        fetch('order_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Xóa đơn hàng thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa đơn hàng');
        });
    }
}

// Auto-submit search form on Enter
document.querySelector('input[name="search"]').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.form.submit();
    }
});

// Status card click to filter
document.querySelectorAll('.stats .orders-card').forEach(card => {
    card.addEventListener('click', function() {
        const statusMap = {
            'Đơn Hàng Chờ Duyệt': 'pending',
            'Đơn Hàng Đã Giao': 'shipped',
            'Đơn Hàng Đã Hủy': 'cancelled'
        };
        
        const statusText = this.querySelector('.orders-card-header span').textContent.trim();
        const status = statusMap[statusText];
        
        if (status) {
            const url = new URL(window.location);
            url.searchParams.set('status', status);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
