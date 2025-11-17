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
                $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE :search ORDER BY p.id ASC");
                $stmt->execute([':search' => "%$search%"]);
            } else {
                $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id ASC");
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
            $category_id = $input['category_id'] ?? null;
            $price = $input['price'] ?? 0;
            $compare_price = $input['compare_price'] ?? null;
            $quantity = $input['quantity'] ?? 0;
            $description = trim($input['description'] ?? '');
            $short_description = trim($input['short_description'] ?? '');
            $featured = isset($input['featured']) ? 1 : 0;
            $is_active = isset($input['is_active']) ? 1 : 0;

            // Tạo slug từ tên sản phẩm
            $slug = generateSlug($name);
            
            // Kiểm tra slug trùng lặp
            $counter = 1;
            $originalSlug = $slug;
            while (true) {
                $checkStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
                $checkStmt->execute([$slug]);
                if (!$checkStmt->fetch()) {
                    break;
                }
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $errors = [];
            if ($name === '') $errors[] = 'Tên sản phẩm là bắt buộc';
            if (!is_numeric($price) || $price < 0) $errors[] = 'Giá phải là số dương';
            if (!is_numeric($quantity) || $quantity < 0) $errors[] = 'Số lượng phải là số không âm';
            if ($compare_price && (!is_numeric($compare_price) || $compare_price < 0)) $errors[] = 'Giá so sánh phải là số dương';

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, compare_price, quantity, description, short_description, slug, featured, is_active) VALUES (:name, :category_id, :price, :compare_price, :quantity, :description, :short_description, :slug, :featured, :is_active)");
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':price' => $price,
                ':compare_price' => $compare_price,
                ':quantity' => $quantity,
                ':description' => $description,
                ':short_description' => $short_description,
                ':slug' => $slug,
                ':featured' => $featured,
                ':is_active' => $is_active
            ]);

            $productId = $pdo->lastInsertId();
            sendJsonResponse(['success' => true, 'message' => 'Thêm sản phẩm thành công', 'id' => $productId], 201);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            $id = $input['id'] ?? $_GET['id'] ?? 0;
            if (!$id) {
                sendError('Yêu cầu ID sản phẩm');
            }

            $stmt = $pdo->prepare('SELECT name, slug FROM products WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $currentProduct = $stmt->fetch();
            if (!$currentProduct) {
                sendError('Không tìm thấy sản phẩm', 404);
            }

            $name = trim($input['name'] ?? '');
            $category_id = $input['category_id'] ?? null;
            $price = $input['price'] ?? 0;
            $compare_price = $input['compare_price'] ?? null;
            $quantity = $input['quantity'] ?? 0;
            $description = trim($input['description'] ?? '');
            $short_description = trim($input['short_description'] ?? '');
            $featured = isset($input['featured']) ? 1 : 0;
            $is_active = isset($input['is_active']) ? 1 : 0;

            // Tạo slug mới nếu tên đã thay đổi
            $slug = $currentProduct['slug'];
            if ($name !== $currentProduct['name']) {
                $slug = generateSlug($name);
                
                // Kiểm tra slug trùng lặp
                $counter = 1;
                $originalSlug = $slug;
                while (true) {
                    $checkStmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
                    $checkStmt->execute([$slug, $id]);
                    if (!$checkStmt->fetch()) {
                        break;
                    }
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $errors = [];
            if ($name === '') $errors[] = 'Tên sản phẩm là bắt buộc';
            if (!is_numeric($price) || $price < 0) $errors[] = 'Giá phải là số dương';
            if (!is_numeric($quantity) || $quantity < 0) $errors[] = 'Số lượng phải là số không âm';
            if ($compare_price && (!is_numeric($compare_price) || $compare_price < 0)) $errors[] = 'Giá so sánh phải là số dương';

            if (!empty($errors)) {
                sendError(implode(', ', $errors));
            }

            $stmt = $pdo->prepare('UPDATE products SET name = :name, category_id = :category_id, price = :price, compare_price = :compare_price, quantity = :quantity, description = :description, short_description = :short_description, slug = :slug, featured = :featured, is_active = :is_active WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':price' => $price,
                ':compare_price' => $compare_price,
                ':quantity' => $quantity,
                ':description' => $description,
                ':short_description' => $short_description,
                ':slug' => $slug,
                ':featured' => $featured,
                ':is_active' => $is_active,
                ':id' => $id
            ]);

            sendJsonResponse(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
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

            sendJsonResponse(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
            break;

        default:
            sendError('Phương thức không được hỗ trợ', 405);
    }

} catch (Exception $e) {
    sendError('Lỗi cơ sở dữ liệu: ' . $e->getMessage(), 500);
}

/**
 * Tạo slug từ tên sản phẩm
 */
function generateSlug($text) {
    // Chuyển về chữ thường
    $text = strtolower($text);
    
    // Loại bỏ dấu tiếng Việt
    $text = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $text);
    $text = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $text);
    $text = preg_replace('/[ìíịỉĩ]/u', 'i', $text);
    $text = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $text);
    $text = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $text);
    $text = preg_replace('/[ỳýỵỷỹ]/u', 'y', $text);
    $text = preg_replace('/đ/u', 'd', $text);
    
    // Loại bỏ ký tự đặc biệt, chỉ giữ lại chữ cái, số và khoảng trắng
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Thay thế khoảng trắng và nhiều dấu gạch ngang liên tiếp bằng một dấu gạch ngang
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Loại bỏ dấu gạch ngang ở đầu và cuối
    $text = trim($text, '-');
    
    return $text;
}
?>