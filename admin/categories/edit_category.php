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
                    // Get current image
                    $stmt = $pdo->prepare('SELECT image FROM categories WHERE id = :id LIMIT 1');
                    $stmt->execute([':id' => $id]);
                    $currentCategory = $stmt->fetch(PDO::FETCH_ASSOC);
                    $currentImage = $currentCategory['image'] ?? null;
                    
                    // Handle image upload
                    $imagePath = $currentImage; // Keep current image by default
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../assets/images/uploads/categories/';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $fileType = $_FILES['image']['type'];
                        
                        if (!in_array($fileType, $allowedTypes)) {
                            $errors[] = 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.';
                        } else {
                            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $fileName = uniqid('cat_') . '.' . $fileExtension;
                            $targetPath = $uploadDir . $fileName;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                                // Delete old image if exists
                                if ($currentImage && file_exists($uploadDir . $currentImage)) {
                                    unlink($uploadDir . $currentImage);
                                }
                                $imagePath = $fileName;
                            } else {
                                $errors[] = 'Failed to upload image.';
                            }
                        }
                    }
                    
                    if (empty($errors)) {
                        $stmt = $pdo->prepare('UPDATE categories SET name = :name, description = :description, image = :image WHERE id = :id');
                        $stmt->execute([
                            ':name' => $name,
                            ':description' => $description,
                            ':image' => $imagePath,
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
