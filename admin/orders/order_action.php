<?php
session_start();
require_once '../includes/db_connect.php';
require_once 'order_controller.php';

// Check admin permissions
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$orderController = new OrderController($pdo);

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_orders':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $filters = [
                'status' => $_GET['status'] ?? '',
                'search' => $_GET['search'] ?? '',
                'date_range' => $_GET['date_range'] ?? ''
            ];
            
            $orders = $orderController->getAllOrders($page, $limit, $filters);
            $totalOrders = $orderController->countOrders($filters);
            $totalPages = ceil($totalOrders / $limit);
            
            echo json_encode([
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_orders' => $totalOrders,
                    'per_page' => $limit
                ]
            ]);
            break;
            
        case 'get_order_details':
            $orderId = (int)($_GET['id'] ?? 0);
            if (!$orderId) {
                throw new Exception('Order ID is required');
            }
            
            $order = $orderController->getOrderById($orderId);
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            $items = $orderController->getOrderItems($orderId);
            $history = $orderController->getOrderStatusHistory($orderId);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'items' => $items,
                    'history' => $history
                ]
            ]);
            break;
            
        case 'update_status':
            $orderId = (int)($_POST['order_id'] ?? 0);
            $newStatus = $_POST['status'] ?? '';
            $note = $_POST['note'] ?? '';
            
            if (!$orderId || !$newStatus) {
                throw new Exception('Order ID and status are required');
            }
            
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception('Invalid status');
            }
            
            $success = $orderController->updateOrderStatus($orderId, $newStatus, $note);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update order status');
            }
            break;
            
        case 'delete_order':
            $orderId = (int)($_POST['order_id'] ?? 0);
            if (!$orderId) {
                throw new Exception('Order ID is required');
            }
            
            $success = $orderController->deleteOrder($orderId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);
            } else {
                throw new Exception('Cannot delete order. Only pending or cancelled orders can be deleted.');
            }
            break;
            
        case 'get_stats':
            $stats = $orderController->getOrderStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'search_orders':
            $searchTerm = $_GET['q'] ?? '';
            if (strlen($searchTerm) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }
            
            $filters = ['search' => $searchTerm];
            $orders = $orderController->getAllOrders(1, 10, $filters);
            
            // Format data for autocomplete
            $results = array_map(function($order) {
                return [
                    'id' => $order['id'],
                    'label' => $order['order_number'] . ' - ' . $order['customer_name'],
                    'value' => $order['order_number'],
                    'customer' => $order['customer_name'],
                    'total' => $order['total_amount'],
                    'status' => $order['order_status']
                ];
            }, $orders);
            
            echo json_encode([
                'success' => true,
                'data' => $results
            ]);
            break;
            
        case 'export_orders':
            // Basic CSV export functionality
            $filters = [
                'status' => $_GET['status'] ?? '',
                'date_range' => $_GET['date_range'] ?? ''
            ];
            
            $orders = $orderController->getAllOrders(1, 1000, $filters); // Get more orders for export
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="orders_export_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($output, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Total Amount',
                'Status',
                'Payment Method',
                'Payment Status',
                'Created At'
            ]);
            
            // CSV Data
            foreach ($orders as $order) {
                fputcsv($output, [
                    $order['order_number'],
                    $order['customer_name'],
                    $order['customer_email'],
                    number_format($order['total_amount'], 0, ',', '.') . ' ₫',
                    ucfirst($order['order_status']),
                    ucfirst(str_replace('_', ' ', $order['payment_method'])),
                    ucfirst($order['payment_status']),
                    date('Y-m-d H:i:s', strtotime($order['created_at']))
                ]);
            }
            
            fclose($output);
            exit;
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
