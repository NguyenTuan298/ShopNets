/**
 * Order Management JavaScript
 * Handles all order-related frontend functionality
 */

class OrderManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.currentFilters = {};
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAutoRefresh();
    }

    setupEventListeners() {
        // Search functionality
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 500);
            });
        }

        // Filter dropdowns
        const statusSelect = document.querySelector('select[name="status"]');
        const dateRangeSelect = document.querySelector('select[name="date_range"]');
        
        if (statusSelect) {
            statusSelect.addEventListener('change', () => this.applyFilters());
        }
        
        if (dateRangeSelect) {
            dateRangeSelect.addEventListener('change', () => this.applyFilters());
        }

        // Bulk actions
        this.setupBulkActions();

        // Modal handlers
        this.setupModalHandlers();

        // Real-time updates
        this.setupRealTimeUpdates();
    }

    setupBulkActions() {
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const orderCheckboxes = document.querySelectorAll('.order-checkbox');
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                this.updateBulkActionButtons();
            });
        }

        // Individual order checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('order-checkbox')) {
                this.updateBulkActionButtons();
            }
        });

        // Bulk action buttons
        const bulkButtons = document.querySelectorAll('.bulk-action-btn');
        bulkButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                this.handleBulkAction(action);
            });
        });
    }

    setupModalHandlers() {
        // Close modal buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('close') || 
                e.target.classList.contains('modal-close')) {
                this.closeModals();
            }
        });

        // Click outside modal to close
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModals();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModals();
            }
        });
    }

    setupRealTimeUpdates() {
        // Check for new orders every 30 seconds
        setInterval(() => {
            this.checkForUpdates();
        }, 30000);
    }

    setupAutoRefresh() {
        // Auto-refresh page every 5 minutes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.refreshOrders();
            }
        }, 300000);
    }

    handleSearch(searchTerm) {
        this.currentFilters.search = searchTerm;
        this.currentPage = 1;
        this.loadOrders();
    }

    applyFilters() {
        const statusSelect = document.querySelector('select[name="status"]');
        const dateRangeSelect = document.querySelector('select[name="date_range"]');
        const searchInput = document.querySelector('input[name="search"]');

        this.currentFilters = {
            status: statusSelect ? statusSelect.value : '',
            date_range: dateRangeSelect ? dateRangeSelect.value : '',
            search: searchInput ? searchInput.value : ''
        };

        this.currentPage = 1;
        this.loadOrders();
    }

    async loadOrders() {
        try {
            this.showLoading();

            const params = new URLSearchParams({
                action: 'get_orders',
                page: this.currentPage,
                limit: this.itemsPerPage,
                ...this.currentFilters
            });

            const response = await fetch(`order_action.php?${params}`);
            const data = await response.json();

            if (data.success) {
                this.renderOrders(data.data);
                this.renderPagination(data.pagination);
                this.updateStats();
            } else {
                this.showError('Error loading orders list: ' + data.message);
            }
        } catch (error) {
            console.error('Error loading orders:', error);
            this.showError('An error occurred while loading orders list');
        } finally {
            this.hideLoading();
        }
    }

    renderOrders(orders) {
        const tbody = document.querySelector('.orders-data-table tbody');
        if (!tbody) return;

        if (orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="no-orders-row">
                        <div class="no-orders">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>No Orders Found</h3>
                            <p>No orders found matching the current search criteria.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = orders.map(order => `
            <tr data-order-id="${order.id}">
                <td>
                    <div class="order-number">
                        <input type="checkbox" class="order-checkbox" value="${order.id}">
                        <strong>${this.escapeHtml(order.order_number)}</strong>
                    </div>
                </td>
                <td>
                    <div class="orders-customer-info">
                        <strong>${this.escapeHtml(order.customer_name)}</strong>
                        <div class="orders-customer-email">${this.escapeHtml(order.customer_email)}</div>
                    </div>
                </td>
                <td>
                    <div class="orders-products">
                        <span class="product-count">${order.total_items} products</span>
                        ${order.product_names ? `
                            <div class="product-preview" title="${this.escapeHtml(order.product_names)}">
                                ${this.escapeHtml(this.truncateText(order.product_names, 30))}...
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td class="order-total">${this.formatCurrency(order.total_amount)} ₫</td>
                <td>
                    <span class="orders-badge ${this.getStatusBadgeClass(order.order_status)}">
                        ${this.getStatusText(order.order_status)}
                    </span>
                </td>
                <td>${this.formatDate(order.created_at)}</td>
                <td>
                    <div class="orders-actions-buttons">
                        <a href="view_order.php?id=${order.id}" 
                           class="orders-btn-icon orders-btn-view" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        ${this.canDeleteOrder(order.order_status) ? `
                            <button class="orders-btn-icon orders-btn-delete" 
                                    title="Delete Order"
                                    onclick="orderManager.deleteOrder(${order.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                        <button class="orders-btn-icon orders-btn-edit" 
                                title="Update Status"
                                onclick="orderManager.showStatusModal(${order.id}, '${order.order_status}')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination(pagination) {
        const paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer || pagination.total_pages <= 1) {
            if (paginationContainer) paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'block';
        
        const paginationHtml = this.generatePaginationHtml(pagination);
        paginationContainer.innerHTML = paginationHtml;

        // Update pagination info
        const paginationInfo = paginationContainer.querySelector('.pagination-info');
        if (paginationInfo) {
            paginationInfo.textContent = 
                `Trang ${pagination.current_page} / ${pagination.total_pages} (${pagination.total_orders} đơn hàng)`;
        }
    }

    generatePaginationHtml(pagination) {
        const { current_page, total_pages } = pagination;
        let html = '<div class="pagination">';

        // Previous button
        if (current_page > 1) {
            html += `<button class="page-btn" onclick="orderManager.goToPage(${current_page - 1})">&laquo; Trước</button>`;
        }

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(total_pages, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current_page ? 'active' : '';
            html += `<button class="page-btn ${activeClass}" onclick="orderManager.goToPage(${i})">${i}</button>`;
        }

        // Next button
        if (current_page < total_pages) {
            html += `<button class="page-btn" onclick="orderManager.goToPage(${current_page + 1})">Sau &raquo;</button>`;
        }

        html += '</div>';
        html += `<div class="pagination-info">Trang ${current_page} / ${total_pages} (${pagination.total_orders} đơn hàng)</div>`;

        return html;
    }

    async updateStats() {
        try {
            const response = await fetch('order_action.php?action=get_stats');
            const data = await response.json();

            if (data.success) {
                this.renderStats(data.data);
            }
        } catch (error) {
            console.error('Error updating stats:', error);
        }
    }

    renderStats(stats) {
        const statCards = document.querySelectorAll('.stats .card');
        const statMap = {
            'Chờ xác nhận': stats.pending || 0,
            'Đã giao vận': stats.shipped || 0,
            'Đã giao hàng': stats.delivered || 0,
            'Đã hủy': stats.cancelled || 0
        };

        statCards.forEach(card => {
            const titleElement = card.querySelector('.card-header span');
            if (titleElement) {
                const title = titleElement.textContent;
                const valueElement = card.querySelector('.card-value');
                if (valueElement && statMap.hasOwnProperty(title)) {
                    valueElement.textContent = statMap[title];
                }
            }
        });
    }

    async deleteOrder(orderId) {
        if (!confirm('Are you sure you want to delete this order?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'delete_order');
            formData.append('order_id', orderId);

            const response = await fetch('order_action.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Xóa đơn hàng thành công!');
                this.loadOrders();
            } else {
                this.showError('Lỗi: ' + data.message);
            }
        } catch (error) {
            console.error('Error deleting order:', error);
            this.showError('Có lỗi xảy ra khi xóa đơn hàng');
        }
    }

    async updateOrderStatus(orderId, newStatus, note = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('order_id', orderId);
            formData.append('status', newStatus);
            formData.append('note', note);

            const response = await fetch('order_action.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Cập nhật trạng thái thành công!');
                this.loadOrders();
                this.closeModals();
            } else {
                this.showError('Lỗi: ' + data.message);
            }
        } catch (error) {
            console.error('Error updating order status:', error);
            this.showError('Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }

    showStatusModal(orderId, currentStatus) {
        // Implementation for status update modal
        // This would show a modal with status options
        console.log('Show status modal for order', orderId, 'current status:', currentStatus);
    }

    async exportOrders() {
        try {
            const params = new URLSearchParams({
                action: 'export_orders',
                ...this.currentFilters
            });

            window.location.href = `order_action.php?${params}`;
        } catch (error) {
            console.error('Error exporting orders:', error);
            this.showError('Có lỗi xảy ra khi xuất dữ liệu');
        }
    }

    goToPage(page) {
        this.currentPage = page;
        this.loadOrders();
    }

    updateBulkActionButtons() {
        const selectedOrders = document.querySelectorAll('.order-checkbox:checked');
        const bulkActionContainer = document.querySelector('.bulk-actions');
        
        if (bulkActionContainer) {
            bulkActionContainer.style.display = selectedOrders.length > 0 ? 'block' : 'none';
            
            const countSpan = bulkActionContainer.querySelector('.selected-count');
            if (countSpan) {
                countSpan.textContent = selectedOrders.length;
            }
        }
    }

    async handleBulkAction(action) {
        const selectedOrders = Array.from(document.querySelectorAll('.order-checkbox:checked'))
            .map(checkbox => parseInt(checkbox.value));

        if (selectedOrders.length === 0) {
            this.showError('Vui lòng chọn ít nhất một đơn hàng');
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn ${action} ${selectedOrders.length} đơn hàng đã chọn?`)) {
            return;
        }

        // Implementation for bulk actions
        console.log('Bulk action:', action, 'Orders:', selectedOrders);
    }

    async checkForUpdates() {
        // Check for new orders or status changes
        try {
            const response = await fetch('order_action.php?action=get_stats');
            const data = await response.json();

            if (data.success) {
                // Compare with previous stats and show notification if there are new orders
                this.checkForNewOrders(data.data);
            }
        } catch (error) {
            console.error('Error checking for updates:', error);
        }
    }

    checkForNewOrders(currentStats) {
        // Implementation for checking new orders
        // This would compare current stats with previous stats
        // and show notification if there are new orders
    }

    async refreshOrders() {
        await this.loadOrders();
    }

    // Utility functions
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('vi-VN');
    }

    truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) : text;
    }

    getStatusBadgeClass(status) {
        const classes = {
            'pending': 'orders-badge-pending',
            'confirmed': 'orders-badge-confirmed',
            'processing': 'orders-badge-processing',
            'shipped': 'orders-badge-shipped',
            'delivered': 'orders-badge-delivered',
            'cancelled': 'orders-badge-cancelled'
        };
        return classes[status] || 'orders-badge-pending';
    }

    getStatusText(status) {
        const texts = {
            'pending': 'Pending',
            'confirmed': 'Confirmed',
            'processing': 'Processing',
            'shipped': 'Shipped',
            'delivered': 'Delivered',
            'cancelled': 'Cancelled'
        };
        return texts[status] || 'Unknown';
    }

    canDeleteOrder(status) {
        return ['pending', 'cancelled'].includes(status);
    }

    // UI feedback functions
    showLoading() {
        const loadingElement = document.querySelector('.loading-overlay');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
    }

    hideLoading() {
        const loadingElement = document.querySelector('.loading-overlay');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        // Create or update notification element
        let notification = document.querySelector('.notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.className = 'notification';
            document.body.appendChild(notification);
        }

        notification.className = `notification ${type} show`;
        notification.textContent = message;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }

    closeModals() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
}

// Initialize order manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.orderManager = new OrderManager();
});

// Global functions for backward compatibility
function exportOrders() {
    if (window.orderManager) {
        window.orderManager.exportOrders();
    }
}

function deleteOrder(orderId) {
    if (window.orderManager) {
        window.orderManager.deleteOrder(orderId);
    }
}
