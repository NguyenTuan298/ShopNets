<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
            exit;
        } else {
            header('Location: index.php?error=' . urlencode('Invalid category ID'));
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Category not found']);
                exit;
            } else {
                header('Location: index.php?error=' . urlencode('Category not found'));
                exit;
            }
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = :category_name');
        $stmt->execute([':category_name' => $category['name']]);
        $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($productCount > 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => "Cannot delete category. It has {$productCount} products."]);
                exit;
            } else {
                header('Location: index.php?error=' . urlencode("Cannot delete category. It has {$productCount} products."));
                exit;
            }
        }

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Category "' . $category['name'] . '" deleted successfully']);
            exit;
        } else {
            header('Location: index.php?msg=' . urlencode('Category deleted successfully'));
            exit;
        }
    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            exit;
        } else {
            header('Location: index.php?error=' . urlencode('Database error'));
            exit;
        }
    }
}

if (!$isAjax) {
    header('Location: index.php');
    exit;
}
?>
