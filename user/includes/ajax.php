<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['action']) && $_GET['action'] === 'get_order_items') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $order_id = $_GET['order_id'] ?? 0;
    $order_items = getOrderItems($db, $order_id);
    
    // Thêm thông tin đã đánh giá chưa
    foreach ($order_items as &$item) {
        $item['has_reviewed'] = hasReviewed($db, $_SESSION['user_id'], $item['product_id'], $item['id']);
    }
    
    echo json_encode(['success' => true, 'items' => $order_items]);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'get_order_items') {
    $order_id = $_GET['order_id'] ?? 0;
    
    if ($order_id) {
        $items = getOrderItemsForReview($db, $order_id, $user_id);
        echo json_encode(['items' => $items]);
    } else {
        echo json_encode(['error' => 'Invalid order ID']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);