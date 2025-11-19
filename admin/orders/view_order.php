<?php 
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
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled'
    ];
    return $texts[$status] ?? 'Unknown';
}

function getPaymentMethodText($method) {
    $methods = [
        'cod' => 'Cash on Delivery',
        'bank_transfer' => 'Bank Transfer',
        'momo' => 'MoMo Wallet',
        'vnpay' => 'VNPay'
    ];
    return $methods[$method] ?? $method;
}

function getPaymentStatusText($status) {
    $statuses = [
        'pending' => 'Pending Payment',
        'paid' => 'Paid',
        'failed' => 'Payment Failed',
        'refunded' => 'Refunded'
    ];
    return $statuses[$status] ?? $status;
}
?>
<link rel="stylesheet" href="../assets/css/pages/orders.css">

<section class="content orders-page order-details-page">
    <div class="content-header">
        <div class="order-header-actions">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to All Orders
            </a>
            <div class="order-title">
                <h1>Order #<?= htmlspecialchars($order['order_number']) ?></h1>
                <span class="order-date">Placed on <?= date('M d, Y \a\t H:i', strtotime($order['created_at'])) ?></span>
            </div>
        </div>
        <div class="order-actions">
            <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                <button class="btn btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-times"></i> Cancel Order
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'pending'): ?>
                <button class="btn btn-success" onclick="confirmOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-check"></i> Confirm Order
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'confirmed'): ?>
                <button class="btn btn-primary" onclick="processOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-cogs"></i> Start Processing
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'processing'): ?>
                <button class="btn btn-info" onclick="shipOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-truck"></i> Ship Order
                </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'shipped'): ?>
                <button class="btn btn-success" onclick="deliverOrder(<?= $order['id'] ?>)">
                    <i class="fas fa-check-circle"></i> Mark Delivered
                </button>
            <?php endif; ?>
            
            <button class="btn btn-secondary" onclick="printOrder()">
                <i class="fas fa-print"></i> Print Order
            </button>
        </div>
    </div>

    <div class="order-details-container">
        <!-- Order Summary -->
        <div class="order-summary-card">
            <div class="card-header">
                <h3>Order Information</h3>
                <span class="orders-badge <?= getStatusBadgeClass($order['order_status']) ?>">
                    <?= getStatusText($order['order_status']) ?>
                </span>
            </div>
            <div class="order-summary-grid">
                <div class="summary-item">
                    <label>Order Number:</label>
                    <span><?= htmlspecialchars($order['order_number']) ?></span>
                </div>
                <div class="summary-item">
                    <label>Order Date:</label>
                    <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="summary-item">
                    <label>Payment Method:</label>
                    <span><?= getPaymentMethodText($order['payment_method']) ?></span>
                </div>
                <div class="summary-item">
                    <label>Payment Status:</label>
                    <span class="payment-status payment-<?= $order['payment_status'] ?>">
                        <?= getPaymentStatusText($order['payment_status']) ?>
                    </span>
                </div>
                <div class="summary-item">
                    <label>Shipping Method:</label>
                    <span><?= htmlspecialchars($order['shipping_method'] ?? 'Not specified') ?></span>
                </div>
                <?php if ($order['tracking_number']): ?>
                <div class="summary-item">
                    <label>Tracking Number:</label>
                    <span><?= htmlspecialchars($order['tracking_number']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info-card">
            <div class="card-header">
                <h3>Customer Information</h3>
            </div>
            <div class="customer-info-grid">
                <div class="info-section">
                    <h4>Contact Information</h4>
                    <div class="info-item">
                        <label>Full Name:</label>
                        <span><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($order['customer_email']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>Phone Number:</label>
                        <span><?= htmlspecialchars($order['customer_phone'] ?? 'Not provided') ?></span>
                    </div>
                </div>
                <div class="info-section">
                    <h4>Shipping Address</h4>
                    <div class="address">
                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                    </div>
                </div>
            </div>
            <?php if ($order['notes']): ?>
            <div class="order-notes">
                <h4>Order Notes:</h4>
                <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Order Items -->
        <div class="order-items-card">
            <div class="card-header">
                <h3>Order Items</h3>
            </div>
            <div class="order-items-table">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
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
                    <label>Subtotal:</label>
                    <span><?= number_format($order['subtotal'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="totals-row discount">
                    <label>Discount:</label>
                    <span>-<?= number_format($order['discount_amount'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php endif; ?>
                <div class="totals-row">
                    <label>Shipping Fee:</label>
                    <span><?= number_format($order['shipping_fee'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php if ($order['tax_amount'] > 0): ?>
                <div class="totals-row">
                    <label>Tax:</label>
                    <span><?= number_format($order['tax_amount'], 0, ',', '.') ?> ₫</span>
                </div>
                <?php endif; ?>
                <div class="totals-row total">
                    <label>Total:</label>
                    <span><?= number_format($order['total_amount'], 0, ',', '.') ?> ₫</span>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="order-history-card">
            <div class="card-header">
                <h3>Order History</h3>
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
            <h3><i class="fas fa-edit"></i> Update Order Status</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusUpdateForm">
                <input type="hidden" id="orderIdInput" value="<?= $order['id'] ?>">
                <input type="hidden" id="newStatusInput">
                
                <div class="form-group">
                    <label for="statusNote">Additional Notes (Optional):</label>
                    <textarea id="statusNote" name="note" rows="3" placeholder="Add any notes about this status change..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Status
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
    updateOrderStatus(id, 'confirmed', 'Order confirmed');
}

function processOrder(id) {
    updateOrderStatus(id, 'processing', 'Started processing order');
}

function shipOrder(id) {
    updateOrderStatus(id, 'shipped', 'Order shipped to carrier');
}

function deliverOrder(id) {
    updateOrderStatus(id, 'delivered', 'Order delivered successfully');
}

function cancelOrder(id) {
    if (confirm('Are you sure you want to cancel this order?')) {
        updateOrderStatus(id, 'cancelled', 'Order has been cancelled');
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
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
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
