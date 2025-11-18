<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';

function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function sendError($message, $status = 400) {
    sendJsonResponse(['success' => false, 'error' => $message], $status);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $search = isset($_GET['q']) ? trim($_GET['q']) : '';
            
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE name LIKE :search OR description LIKE :search ORDER BY name ASC");
                $stmt->execute([':search' => "%$search%"]);
            } else {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
            }
            
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            

            foreach ($categories as &$category) {
                $countStmt = $pdo->prepare('SELECT COUNT(*) as count FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = :category_name');
                $countStmt->execute([':category_name' => $category['name']]);
                $category['product_count'] = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
            
            sendJsonResponse(['success' => true, 'data' => $categories]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (empty($input)) {
                $input = $_POST;
            }
            
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');

            $errors = [];
            if ($name === '') $errors[] = 'Category name is required';

            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            if ($stmt->fetch()) {
                $errors[] = 'Category name already exists';
            }

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description
            ]);

            $categoryId = $pdo->lastInsertId();
            sendJsonResponse(['success' => true, 'message' => 'Category added successfully', 'id' => $categoryId], 201);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                sendError('Category ID is required');
            }

            $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                sendError('Category not found', 404);
            }

            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');

            $errors = [];
            if ($name === '') $errors[] = 'Category name is required';

            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name AND id != :id LIMIT 1");
            $stmt->execute([':name' => $name, ':id' => $id]);
            if ($stmt->fetch()) {
                $errors[] = 'Category name already exists';
            }

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':id' => $id
            ]);

            sendJsonResponse(['success' => true, 'message' => 'Category updated successfully']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? 0;
            
            if (!$id) {
                sendError('Category ID is required');
            }

            $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $category = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$category) {
                sendError('Category not found', 404);
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = :category_name');
            $stmt->execute([':category_name' => $category['name']]);
            $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($productCount > 0) {
                sendError("Cannot delete category. It has {$productCount} products.");
            }

            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
            $stmt->execute([':id' => $id]);

            sendJsonResponse(['success' => true, 'message' => 'Category deleted successfully']);
            break;

        default:
            sendError('Method not allowed', 405);
    }

} catch (Exception $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>
