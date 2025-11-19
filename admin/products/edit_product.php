<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connect.php';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Authentication check - enable khi đã test xong
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
        exit;
    } else {
        header('Location: ../login/login.php');
        exit;
    }
}

function generateSlug($string) {
    $string = mb_strtolower($string, 'UTF-8');
    
    $vietnameseMap = [
        'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
        'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
        'đ' => 'd'
    ];
    
    $string = strtr($string, $vietnameseMap);
    $string = preg_replace('/[^a-z0-9\s]/', '', $string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}

// Handle GET request - Load product data for edit modal
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $isAjax) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID sản phẩm không hợp lệ']);
        exit;
    }

    try {
        // Lấy thông tin sản phẩm
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Nếu có category_id, lấy tên category
            if ($product['category_id']) {
                $categoryStmt = $pdo->prepare('SELECT name FROM categories WHERE id = :id LIMIT 1');
                $categoryStmt->execute([':id' => $product['category_id']]);
                $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);
                if ($category) {
                    $product['category_name'] = $category['name'];
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $product]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Không tìm thấy sản phẩm']);
            exit;
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
        exit;
    }
}

// Handle POST request - Update product
$errors = [];
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($id <= 0) {
        $errors[] = 'ID sản phẩm không hợp lệ';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM products WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                $errors[] = 'Không tìm thấy sản phẩm';
            }
        } catch (Exception $e) {
            $errors[] = 'Lỗi cơ sở dữ liệu';
        }

        if (empty($errors)) {
            $name = trim($_POST['name'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $price = $_POST['price'] ?? 0;
            $compare_price = $_POST['compare_price'] ?? null;
            $quantity = $_POST['quantity'] ?? 0;
            $description = trim($_POST['description'] ?? '');
            $short_description = trim($_POST['short_description'] ?? '');
            $specifications = $_POST['specifications'] ?? '';
            $featured = isset($_POST['featured']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $imageName = '';
            $oldImage = '';

            // Tạo slug từ tên sản phẩm
            $slug = generateSlug($name);
            
            // Kiểm tra slug đã tồn tại (trừ sản phẩm hiện tại)
            $checkSlugStmt = $pdo->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1");
            $checkSlugStmt->execute([':slug' => $slug, ':id' => $id]);
            if ($checkSlugStmt->fetch()) {
                $slug = $slug . '-' . time();
            }

            try {
                $stmt = $pdo->prepare('SELECT image FROM products WHERE id = :id LIMIT 1');
                $stmt->execute([':id' => $id]);
                $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
                $oldImage = $currentProduct['image'] ?? '';
            } catch (Exception $e) {
                $errors[] = 'Không thể lấy dữ liệu sản phẩm hiện tại';
            }

            if ($name === '') $errors[] = 'Tên sản phẩm là bắt buộc';
            if (!$category_id) {
                $errors[] = 'Danh mục là bắt buộc.';
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = :category_id LIMIT 1");
                    $stmt->execute([':category_id' => $category_id]);
                    if (!$stmt->fetch()) {
                        $errors[] = 'Danh mục đã chọn không tồn tại.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Lỗi xác thực danh mục.';
                }
            }
            if (!is_numeric($price)) $errors[] = 'Giá phải là số';
            if ($compare_price && !is_numeric($compare_price)) $errors[] = 'Giá so sánh phải là số';
            if (!is_numeric($quantity)) $errors[] = 'Số lượng phải là số';

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/images/uploads/';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = pathinfo($_FILES['image']['name']);
                $extension = strtolower($fileInfo['extension']);
                
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowedTypes)) {
                    $errors[] = 'Không đúng định dạng hình ảnh. Chỉ chấp nhận JPG, PNG, GIF.';
                }
                
                if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                    $errors[] = 'Kích thước hình ảnh phải nhỏ hơn 5MB.';
                }
                
                if (empty($errors)) {
                    $imageName = 'product_' . time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $imageName;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                        $errors[] = 'Tải lên hình ảnh thất bại.';
                        $imageName = '';
                    }
                }
            } else {
                $imageName = $oldImage;
            }

            if (empty($errors)) {
                // Validate specifications JSON
                $specs_json = null;
                if (!empty($specifications)) {
                    $decoded = json_decode($specifications, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $specs_json = $specifications;
                    } else {
                        $errors[] = 'Dữ liệu thông số kỹ thuật không hợp lệ.';
                    }
                }
                
                if (empty($errors)) {
                    try {
                        $stmt = $pdo->prepare('UPDATE products SET name = :name, category_id = :category_id, price = :price, compare_price = :compare_price, quantity = :quantity, description = :description, short_description = :short_description, specifications = :specifications, image = :image, slug = :slug, featured = :featured, is_active = :is_active WHERE id = :id');
                        $stmt->execute([
                            ':name' => $name,
                            ':category_id' => $category_id,
                            ':price' => $price,
                            ':compare_price' => $compare_price,
                            ':quantity' => $quantity,
                            ':description' => $description,
                            ':short_description' => $short_description,
                            ':specifications' => $specs_json,
                            ':image' => $imageName,
                            ':slug' => $slug,
                            ':featured' => $featured,
                            ':is_active' => $is_active,
                            ':id' => $id
                        ]);
                        
                        if ($imageName !== $oldImage && $oldImage && file_exists($uploadDir . $oldImage)) {
                            unlink($uploadDir . $oldImage);
                        }
                        
                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công']);
                            exit;
                        } else {
                            header('Location: index.php?msg=' . urlencode('Cập nhật sản phẩm thành công'));
                            exit;
                        }
                    } catch (Exception $e) {
                        if ($imageName !== $oldImage && $imageName && file_exists($uploadDir . $imageName)) {
                            unlink($uploadDir . $imageName);
                        }
                        $errors[] = 'Lỗi DB: ' . $e->getMessage();
                    }
                }
            } else {
                if ($imageName !== $oldImage && $imageName && file_exists($uploadDir . $imageName)) {
                    unlink($uploadDir . $imageName);
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
