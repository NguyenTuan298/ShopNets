<?php 
session_start();
// Set admin session for testing (remove in production)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}
$pageTitle = 'Order Details - ShopNets';
$currentPage = 'orders';
$baseUrl = '../';
include '../includes/header.php';
include '../includes/sidebar.php';
require_once 'order_controller.php';

// Get Order ID from URL
$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Get order information
$order = $orderController->getOrderById($orderId);
if (!$order) {
    header('Location: index.php?error=Order not found');
    exit;
}

$orderItems = $orderController->getOrderItems($orderId);
$orderHistory = $orderController->getOrderStatusHistory($orderId);

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

function getPaymentMethodText($method) {
    $methods = [
        'cod' => 'Thanh Toán Khi Nhận Hàng',
        'bank_transfer' => 'Chuyển Khoản Ngân Hàng',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPay'
    ];
    return $methods[$method] ?? $method;
}

function getPaymentStatusText($status) {
    $statuses = [
        'pending' => 'Chờ Thanh Toán',
        'paid' => 'Đã Thanh Toán',
        'failed' => 'Thanh Toán Thất Bại',
        'refunded' => 'Đã Hoàn Tiền'
    ];
    return $statuses[$status] ?? $status;
}
?>
<link rel="stylesheet" href="../assets/css/pages/orders.css">

<section class="content orders-page order-details-page">
    <div class="content-header">
        <div class="order-header-actions">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
            </a>
            <div class="order-title">
                <h1>Đơn Hàng #<?= htmlspecialchars($order['order_number']) ?></h1>
                <span class="order-date">Đặt lúc <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
        </div>
        <div class="order-actions">
            <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                <button class="btn btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-times"></i> Hủy Đơn
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'pending'): ?>
                <button class="btn btn-success" onclick="confirmOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-check"></i> Xác Nhận Đơn
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'confirmed'): ?>
                <button class="btn btn-primary" onclick="processOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-cogs"></i> Bắt Đầu Xử Lý
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'processing'): ?>
                <button class="btn btn-info" onclick="shipOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-truck"></i> Giao Hàng
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'shipped'): ?>
                <button class="btn btn-success" onclick="deliverOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-check-circle"></i> Đã Giao Hàng
                </button>
            <?php endif; ?>
            
            <button class="btn btn-secondary" onclick="printOrder()">
                <i class="fas fa-print"></i> In Đơn Hàng
            </button>
        </div>
    </div>

    <div class="order-details-container">
        <!-- Order Summary -->
        <div class="order-summary-card">
            <div class="card-header">
                <h3>Thông Tin Đơn Hàng</h3>
                <span class="orders-badge <?= getStatusBadgeClass($order['order_status']) ?>">
                    <?= getStatusText($order['order_status']) ?>
                </span>
            </div>
            <div class="order-summary-grid">
                <div class="summary-item">
                    <label>Số Đơn Hàng:</label>
                    <span><?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="summary-item">
                    <label>Ngày Đặt:</label>
                    <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="summary-item">
                    <label>Phương Thức Thanh Toán:</label>
                    <span><?= getPaymentMethodText($order['payment_method']) ?></span>
                </div>
                <div class="summary-item">
                    <label>Trạng Thái Thanh Toán:</label>
                    <span class="payment-status payment-<?= $order['payment_status'] ?>">
                        <?= getPaymentStatusText($order['payment_status']) ?>
                    </span>
                </div>
                <div class="summary-item">
                    <label>Phương Thức Vận Chuyển:</label>
                    <span><?= htmlspecialchars($order['shipping_method'] ?? 'Chưa xác định') ?></span>
                </div>
                <?php if ($order['tracking_number']): ?>
                <div class="summary-item">
                    <label>Mã Vận Đơn:</label>
                    <span><?= htmlspecialchars($order['tracking_number']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info-card">
            <div class="card-header">
                <h3>Thông Tin Khách Hàng</h3>
            </div>
            <div class="customer-info-grid">
                <div class="info-section">
                    <h4>Thông Tin Liên Hệ</h4>
                    <div class="info-item">
                        <label>Họ Tên:</label>
                        <span><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($order['customer_email']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Số Điện Thoại:</label>
                        <span><?= htmlspecialchars($order['customer_phone'] ?? 'Chưa cung cấp') ?></span>
                    </div>
                </div>
                <div class="info-section">
                    <h4>Địa Chỉ Giao Hàng</h4>
                    <div class="address">
                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                    </div>
                </div>
            </div>
            <?php if ($order['notes']): ?>
            <div class="order-notes">
                <h4>Ghi Chú Đơn Hàng:</h4>
                <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Order Items -->
        <div class="order-items-card">
            <div class="card-header">
                <h3>Sản Phẩm Trong Đơn</h3>
            </div>
            <div class="order-items-table">
                <table>
                    <thead>
                        <tr>
                            <th>Sản Phẩm</th>
                            <th>Đơn Giá</th>
                            <th>Số Lượng</th>
                            <th>Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php if ($item['product_image']): ?>
                                        <img src="../assets/images/<?= htmlspecialchars($item['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="product-thumbnail">
                                    <?php endif; ?>
                                    <div class="product-details">
                                        <div class="product-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <?php if (isset($item['attributes']) && $item['attributes']): ?>
                                            <div class="product-attributes">
                                                <?= htmlspecialchars($item['attributes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="price"><?= number_format($item['product_price'], 0, ',', '.') ?> ₫</td>
                            <td class="quantity"><?= $item['quantity'] ?></td>
                            <td class="total-price"><?= number_format($item['total_price'], 0, ',', '.') ?> ₫</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Order Totals -->
            <div class="order-totals">
                <div class="totals-row">
                    <label>Tạm Tính:</label>
                    <span><?= number_format($order['subtotal'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="totals-row discount">
                    <label>Giảm Giá:</label>
                    <span>-<?= number_format($order['discount_amount'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php endif; ?>
                <div class="totals-row">
                    <label>Phí Vận Chuyển:</label>
                    <span><?= number_format($order['shipping_fee'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php if ($order['tax_amount'] > 0): ?>
                <div class="totals-row">
                    <label>Thuế:</label>
                    <span><?= number_format($order['tax_amount'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php endif; ?>
                <div class="totals-row total">
                    <label>Tổng Cộng:</label>
                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?> ₫</span>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="order-history-card">
            <div class="card-header">
                <h3>Lịch Sử Đơn Hàng</h3>
            </div>
            <div class="order-timeline">
                <?php foreach ($orderHistory as $history): ?>
                <div class="timeline-item status-<?= $history['status'] ?>">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <div class="timeline-title"><?= getStatusText($history['status']) ?></div>
                        <div class="timeline-time"><?= date('d/m/Y H:i', strtotime($history['created_at'])) ?></div>
                        <?php if ($history['note']): ?>
                            <div class="timeline-note"><?= htmlspecialchars($history['note']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Cập Nhật Trạng Thái Đơn Hàng</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusUpdateForm">
                <input type="hidden" id="orderIdInput" value="<?= $order['id'] ?>">
                <input type="hidden" id="newStatusInput">
                
                <div class="form-group">
                    <label for="statusNote">Ghi Chú Thêm (Tùy Chọn):</label>
                    <textarea id="statusNote" name="note" rows="3" placeholder="Thêm ghi chú về thay đổi trạng thái..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/order.js"></script>
<script>
// Order-specific JavaScript
const orderId = <?= $order['id'] ?>;

function confirmOrder(id) {
    updateOrderStatus(id, 'confirmed', 'Đơn hàng đã được xác nhận');
}

function processOrder(id) {
    updateOrderStatus(id, 'processing', 'Bắt đầu xử lý đơn hàng');
}

function shipOrder(id) {
    updateOrderStatus(id, 'shipped', 'Đơn hàng đã được giao cho đơn vị vận chuyển');
}

function deliverOrder(id) {
    updateOrderStatus(id, 'delivered', 'Đơn hàng đã được giao thành công');
}

function cancelOrder(id) {
    if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        updateOrderStatus(id, 'cancelled', 'Đơn hàng đã bị hủy');
    }
}

function updateOrderStatus(id, status, defaultNote) {
    document.getElementById('orderIdInput').value = id;
    document.getElementById('newStatusInput').value = status;
    document.getElementById('statusNote').placeholder = defaultNote;
    document.getElementById('statusModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function printOrder() {
    window.print();
}

// Handle status update form submission
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', document.getElementById('orderIdInput').value);
    formData.append('status', document.getElementById('newStatusInput').value);
    formData.append('note', document.getElementById('statusNote').value);
    
    fetch('order_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật trạng thái thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
    });
    
    closeModal();
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
