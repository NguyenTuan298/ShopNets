<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db_connect.php';

$isApiRequest = (
    isset($_GET['api']) ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
);

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
                $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :search OR category LIKE :search ORDER BY id ASC");
                $stmt->execute([':search' => "%$search%"]);
            } else {
                $stmt = $pdo->query("SELECT * FROM products ORDER BY id ASC");
            }
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendJsonResponse(['success' => true, 'data' => $products]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            if (empty($input)) {
                $input = $_POST;
            }
            
            $name = trim($input['name'] ?? '');
            $category = trim($input['category'] ?? '');
            $price = $input['price'] ?? 0;
            $inventory = $input['inventory'] ?? 0;
            $description = trim($input['description'] ?? '');

            $errors = [];
            if ($name === '') $errors[] = 'Product name is required';
            if (!is_numeric($price)) $errors[] = 'Price must be a number';
            if (!is_numeric($inventory)) $errors[] = 'Inventory must be a number';

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, inventory, description) VALUES (:name, :category, :price, :inventory, :description)");
            $stmt->execute([
                ':name' => $name,
                ':category' => $category,
                ':price' => $price,
                ':inventory' => $inventory,
                ':description' => $description
            ]);

            $productId = $pdo->lastInsertId();
            sendJsonResponse(['success' => true, 'message' => 'Product added successfully', 'id' => $productId], 201);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                sendError('Product ID is required');
            }

            $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                sendError('Product not found', 404);
            }

            $name = trim($input['name'] ?? '');
            $category = trim($input['category'] ?? '');
            $price = $input['price'] ?? 0;
            $inventory = $input['inventory'] ?? 0;
            $description = trim($input['description'] ?? '');

            $errors = [];
            if ($name === '') $errors[] = 'Product name is required';
            if (!is_numeric($price)) $errors[] = 'Price must be a number';
            if (!is_numeric($inventory)) $errors[] = 'Inventory must be a number';

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare('UPDATE products SET name = :name, category = :category, price = :price, inventory = :inventory, description = :description WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':category' => $category,
                ':price' => $price,
                ':inventory' => $inventory,
                ':description' => $description,
                ':id' => $id
            ]);

            sendJsonResponse(['success' => true, 'message' => 'Product updated successfully']);
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? 0;
            
            if (!$id) {
                sendError('Product ID is required');
            }

            $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                sendError('Product not found', 404);
            }

            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute([':id' => $id]);

            sendJsonResponse(['success' => true, 'message' => 'Product deleted successfully']);
            break;

        default:
            sendError('Method not allowed', 405);
    }

} catch (Exception $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
?>