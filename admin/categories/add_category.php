<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $errors[] = 'Category name is required.';
    }

    // kiểm tra tên danh mục đã tồn tại chưa
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            if ($stmt->fetch()) {
                $errors[] = 'Category name already exists.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description
            ]);
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category added successfully']);
                exit;
            } else {
                header('Location: index.php?msg=' . urlencode('Category added successfully'));
                exit;
            }
        } catch (Exception $e) {
            $errors[] = 'DB error: ' . $e->getMessage();
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
}

if (!$isAjax) {
    header('Location: index.php');
    exit;
}
?>
