<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'cart_count' => 0];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.';
    echo json_encode($response);
    exit;
}

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    $response['message'] = 'Dữ liệu không hợp lệ.';
    echo json_encode($response);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if ($product_id <= 0 || $quantity <= 0) {
    $response['message'] = 'Sản phẩm hoặc số lượng không hợp lệ.';
    echo json_encode($response);
    exit;
}

// Kiểm tra sản phẩm tồn tại
$database = new Database();
$db = $database->getConnection();
$query = "SELECT id FROM products WHERE id = :product_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $response['message'] = 'Sản phẩm không tồn tại.';
    echo json_encode($response);
    exit;
}

// Thêm vào giỏ hàng (lưu trong session)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Tính tổng số lượng trong giỏ hàng
$cart_count = array_sum($_SESSION['cart']);
$response['success'] = true;
$response['message'] = 'Đã thêm sản phẩm vào giỏ hàng!';
$response['cart_count'] = $cart_count;

echo json_encode($response);
?>