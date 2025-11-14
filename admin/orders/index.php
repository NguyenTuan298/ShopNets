<?php 
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
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing', 
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    return $texts[$status] ?? 'Unknown';
}
?>
<link rel="stylesheet" href="../assets/css/pages/orders.css">

  <section class="content orders-page">
      <div class="content-header">
        <h1>Order Management</h1>
        <p class="content-description">Manage and track all customer orders</p>
      </div>

      <section class="stats">
        <div class="orders-card <?= ($filters['status'] === 'pending') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Pending Orders</span>
            <span class="icon">
              <i class="fas fa-clock"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Awaiting Confirmation</div>
          <div class="orders-card-value blue"><?= $stats['pending'] ?></div>
        </div>

        <div class="orders-card <?= ($filters['status'] === 'shipped') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Shipped Orders</span>
            <span class="icon">
              <i class="fas fa-truck"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">On Delivery</div>
          <div class="orders-card-value orange"><?= $stats['shipped'] ?></div>
        </div>

        <div class="orders-card <?= ($filters['status'] === 'delivered') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Completed Orders</span>
            <span class="icon">
              <i class="fas fa-check-circle"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Successfully Delivered</div>
          <div class="orders-card-value green"><?= $stats['delivered'] ?></div>
        </div>

        <div class="orders-card <?= ($filters['status'] === 'cancelled') ? 'active' : '' ?>">
          <div class="orders-card-header">
            <span>Cancelled Orders</span>
            <span class="icon">
              <i class="fas fa-times-circle"></i>
            </span>
          </div>
          <div class="orders-card-subtitle">Order Cancellations</div>
          <div class="orders-card-value red"><?= $stats['cancelled'] ?></div>
        </div>
      </section>

      <div class="orders-filter-card">
        <div class="orders-filter-header">
          <i class="fas fa-filter"></i> Search & Filter Orders
        </div>
        <form method="GET" class="filters-form">
          <div class="filters-row">
            <div class="search-box">
              <input type="text" name="search" placeholder="Search orders by number, customer name, or email..." 
                     value="<?= htmlspecialchars($filters['search']) ?>" 
                     onkeypress="if(event.key==='Enter') this.form.submit()">
            </div>
            <div class="select">
              <select name="status" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= ($filters['status'] === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                <option value="processing" <?= ($filters['status'] === 'processing') ? 'selected' : '' ?>>Processing</option>
                <option value="shipped" <?= ($filters['status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= ($filters['status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= ($filters['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
              </select>
            </div>
            <div class="select">
              <select name="date_range" onchange="this.form.submit()">
                <option value="">All Time</option>
                <option value="today" <?= ($filters['date_range'] === 'today') ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= ($filters['date_range'] === 'week') ? 'selected' : '' ?>>Last 7 Days</option>
                <option value="month" <?= ($filters['date_range'] === 'month') ? 'selected' : '' ?>>Last 30 Days</option>
                <option value="3months" <?= ($filters['date_range'] === '3months') ? 'selected' : '' ?>>Last 90 Days</option>
              </select>
            </div>

          </div>
        </form>
      </div>

      <!-- Orders Table -->
      <div class="orders-table-card">
        <div class="orders-table-header">
          <div class="table-title">
            <h2><i class="fas fa-list"></i> All Orders</h2>
            <span class="orders-count">
              Showing <?= count($orders) ?> of <?= $totalOrders ?> total orders
            </span>
          </div>
        </div>
        
        <?php if (empty($orders)): ?>
          <div class="no-orders">
            <i class="fas fa-shopping-cart"></i>
            <h3>No Orders Found</h3>
            <p>No orders found matching the current search criteria.</p>
          </div>
        <?php else: ?>
        <div class="orders-table-container">
          <table class="orders-data-table">
            <thead>
              <tr>
                <th>Order Number</th>
                <th>Customer</th>
                <th>Products</th>
                <th>Total</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Actions</th>
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
                    <span class="product-count"><?= $order['total_items'] ?> products</span>
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
                 class="page-btn">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" 
                 class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
              <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" 
                 class="page-btn">Next &raquo;</a>
            <?php endif; ?>
          </div>
          <div class="pagination-info">
            Page <?= $page ?> / <?= $totalPages ?> (<?= $totalOrders ?> orders)
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
    if (confirm('Are you sure you want to delete this order?')) {
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
                alert('Order deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the order');
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
document.querySelectorAll('.stats .card').forEach(card => {
    card.addEventListener('click', function() {
        const statusMap = {
            'Pending Orders': 'pending',
            'Shipped Orders': 'shipped', 
            'Completed Orders': 'delivered',
            'Cancelled Orders': 'cancelled'
        };
        
        const statusText = this.querySelector('.card-header span').textContent;
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
