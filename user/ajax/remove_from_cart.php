<?php
session_start();
include '../includes/database.php';
include '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

if ($_POST) {
    $product_id = $_POST['product_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        
        $cart_total = getCartTotal($db);
        
        echo json_encode([
            'success' => true,
            'cart_count' => array_sum($_SESSION['cart']),
            'cart_total' => number_format($cart_total, 0, ',', '.')
        ]);
    }
}
?>