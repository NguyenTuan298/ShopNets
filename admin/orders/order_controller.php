<?php
require_once '../includes/db_connect.php';

class OrderController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all orders with pagination and filtering
     */
    public function getAllOrders($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT o.*, 
                       COUNT(oi.id) as total_items,
                       GROUP_CONCAT(oi.product_name SEPARATOR ', ') as product_names
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id";
        
        $whereConditions = [];
        $params = [];
        
        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereConditions[] = "o.order_status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(o.order_number LIKE :search OR o.customer_name LIKE :search OR o.customer_email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_range'])) {
            switch ($filters['date_range']) {
                case 'today':
                    $whereConditions[] = "DATE(o.created_at) = CURDATE()";
                    break;
                case 'week':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '3months':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
                    break;
            }
        }
        
        if ($whereConditions) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Count total orders (with optional filters)
     */
    public function countOrders($filters = []) {
        $sql = "SELECT COUNT(DISTINCT o.id) as total FROM orders o";
        
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $whereConditions[] = "o.order_status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(o.order_number LIKE :search OR o.customer_name LIKE :search OR o.customer_email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['date_range'])) {
            switch ($filters['date_range']) {
                case 'today':
                    $whereConditions[] = "DATE(o.created_at) = CURDATE()";
                    break;
                case 'week':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '3months':
                    $whereConditions[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
                    break;
            }
        }
        
        if ($whereConditions) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    /**
     * Get order details by ID
     */
    public function getOrderById($orderId) {
        $sql = "SELECT o.*, 
                       GROUP_CONCAT(oi.product_name SEPARATOR ', ') as product_names
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.id = :id
                GROUP BY o.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get products in order
     */
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.image as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id
                ORDER BY oi.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get order status history
     */
    public function getOrderStatusHistory($orderId) {
        $sql = "SELECT * FROM order_status_history 
                WHERE order_id = :order_id 
                ORDER BY created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $newStatus, $note = '') {
        try {
            $this->pdo->beginTransaction();
            
            // Update status in orders table
            $sql = "UPDATE orders SET order_status = :status, updated_at = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $newStatus);
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Add to status history
            $sql = "INSERT INTO order_status_history (order_id, status, note, created_at) 
                    VALUES (:order_id, :status, :note, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindValue(':status', $newStatus);
            $stmt->bindValue(':note', $note);
            $stmt->execute();
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    /**
     * Get order statistics
     */
    public function getOrderStats() {
        $stats = [];
        
        // Total orders by status
        $sql = "SELECT 
                    order_status,
                    COUNT(*) as count
                FROM orders 
                GROUP BY order_status";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stats['pending'] = $statusCounts['pending'] ?? 0;
        $stats['confirmed'] = $statusCounts['confirmed'] ?? 0;
        $stats['processing'] = $statusCounts['processing'] ?? 0;
        $stats['shipped'] = $statusCounts['shipped'] ?? 0;
        $stats['delivered'] = $statusCounts['delivered'] ?? 0;
        $stats['cancelled'] = $statusCounts['cancelled'] ?? 0;
        
        // New orders (last 24 hours)
        $sql = "SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['new_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total revenue
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE order_status IN ('delivered', 'shipped')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Delete order (only allowed for pending or cancelled orders)
     */
    public function deleteOrder($orderId) {
        $sql = "SELECT order_status FROM orders WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order || !in_array($order['order_status'], ['pending', 'cancelled'])) {
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Delete order items first
            $sql = "DELETE FROM order_items WHERE order_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Delete status history
            $sql = "DELETE FROM order_status_history WHERE order_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Delete order
            $sql = "DELETE FROM orders WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    /**
     * Get order by order number
     */
    public function getOrderByNumber($orderNumber) {
        $sql = "SELECT * FROM orders WHERE order_number = :order_number";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_number', $orderNumber);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Initialize controller
$orderController = new OrderController($pdo);
?>
