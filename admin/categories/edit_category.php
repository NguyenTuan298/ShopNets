<?php
require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $isAjax) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $category]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Category not found']);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$errors = [];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        $errors[] = 'Invalid category ID';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                $errors[] = 'Category not found';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error';
        }

        if (empty($errors)) {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if ($name === '') $errors[] = 'Category name is required';

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name AND id != :id LIMIT 1");
                    $stmt->execute([':name' => $name, ':id' => $id]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Category name already exists.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
                    $stmt->execute([
                        ':name' => $name,
                        ':description' => $description,
                        ':id' => $id
                    ]);
                    
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
                        exit;
                    } else {
                        header('Location: index.php?msg=' . urlencode('Category updated successfully'));
                        exit;
                    }
                } catch (Exception $e) {
                    $errors[] = 'DB error: ' . $e->getMessage();
                }
            }
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
