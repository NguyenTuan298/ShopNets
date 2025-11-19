<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        exit;
    }
    header('Location: index.php?msg=' . urlencode('Invalid product id'));
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT name FROM products WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            exit;
        }
        header('Location: index.php?msg=' . urlencode('Product not found'));
        exit;
    }
    
    $productName = $product['name'];
    
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => "Product \"$productName\" has been deleted successfully"
        ]);
        exit;
    }
    
    header('Location: index.php?msg=' . urlencode("Product \"$productName\" deleted successfully"));
    exit;
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error deleting product: ' . $e->getMessage()]);
        exit;
    }
    header('Location: index.php?msg=' . urlencode('Error deleting product: ' . $e->getMessage()));
    exit;
}
