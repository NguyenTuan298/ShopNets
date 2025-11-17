<?php
require_once __DIR__ . '/../includes/db_connect.php';

// Kiểm tra authentication cho AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $isAjax đã được định nghĩa ở trên

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
        exit;
    } else {
        exit();
    }
}

// Timeout 30 phút
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Session expired']);
        exit;
    } else {
        header('Location: ../login/login.php?error=Session+expired!');
        exit;
    }
}
$_SESSION['last_activity'] = time();

function generateSlug($string) {
    // Chuyển về chữ thường
    $string = mb_strtolower($string, 'UTF-8');
    
    // Chuyển đổi các ký tự tiếng Việt có dấu thành không dấu
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
    
    // Loại bỏ các ký tự đặc biệt, chỉ giữ lại chữ cái, số và dấu cách
    $string = preg_replace('/[^a-z0-9\s]/', '', $string);
    
    // Thay thế dấu cách bằng dấu gạch ngang
    $string = preg_replace('/\s+/', '-', $string);
    
    // Loại bỏ dấu gạch ngang ở đầu và cuối
    $string = trim($string, '-');
    
    return $string;
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $compare_price = $_POST['compare_price'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $imageName = '';

    // Tạo slug từ tên sản phẩm
    $slug = generateSlug($name);

    if ($name === '') {
        $errors[] = 'Tên sản phẩm là bắt buộc.';
    }
    if (!$category_id) {
        $errors[] = 'Danh mục là bắt buộc.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = :category_id LIMIT 1");
            $stmt->execute([':category_id' => $category_id]);
            if (!$stmt->fetch()) {
                $errors[] = 'Danh mục được chọn không tồn tại.';
            }
        } catch (Exception $e) {
            $errors[] = 'Lỗi xác thực danh mục.';
        }
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = 'Giá phải là số dương.';
    }
    if (!is_numeric($quantity) || $quantity < 0) {
        $errors[] = 'Số lượng phải là số không âm.';
    }
    if ($compare_price && (!is_numeric($compare_price) || $compare_price < 0)) {
        $errors[] = 'Giá so sánh phải là số dương.';
    }

    // Kiểm tra slug trùng lặp
    if ($slug) {
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
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'Invalid image type. Only JPG, PNG, and GIF are allowed.';
        }
        
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 5MB.';
        }
        
        if (empty($errors)) {
            $imageName = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $imageName;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $errors[] = 'Failed to upload image.';
                $imageName = '';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, compare_price, quantity, description, short_description, image, slug, featured, is_active) VALUES (:name, :category_id, :price, :compare_price, :quantity, :description, :short_description, :image, :slug, :featured, :is_active)");
            $stmt->execute([
                ':name' => $name,
                ':category_id' => $category_id,
                ':price' => $price,
                ':compare_price' => $compare_price,
                ':quantity' => $quantity,
                ':description' => $description,
                ':short_description' => $short_description,
                ':image' => $imageName,
                ':slug' => $slug,
                ':featured' => $featured,
                ':is_active' => $is_active
            ]);
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công']);
                exit;
            } else {
                header('Location: index.php?msg=' . urlencode('Thêm sản phẩm thành công'));
                exit;
            }
        } catch (Exception $e) {
            if ($imageName && file_exists($uploadDir . $imageName)) {
                unlink($uploadDir . $imageName);
            }
            $errors[] = 'Lỗi DB: ' . $e->getMessage();
        }
    } else {
        if ($imageName && file_exists($uploadDir . $imageName)) {
            unlink($uploadDir . $imageName);
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
