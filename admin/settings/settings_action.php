<?php
// Start output buffering to prevent any HTML output
ob_start();

session_start();

// Disable error display to prevent HTML output before JSON
error_reporting(0);
ini_set('display_errors', 0);

// Include database connection
try {
    include '../includes/db_connect.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit;
}

// Get admin ID from session  
$adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
        
        switch ($action) {
            case 'website_info':
                $response = handleWebsiteInfo($pdo, $_POST);
                break;
                
            case 'contact_info':
                $response = handleContactInfo($pdo, $_POST);
                break;
                
            case 'admin_profile':
                $response = handleAdminProfile($pdo, $_POST, $_FILES, $adminId);
                break;
                
            case 'system_settings':
                $response = handleSystemSettings($pdo, $_POST);
                break;
                
            default:
                $response = ['success' => false, 'error' => 'Invalid action'];
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

// Return JSON response for AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Clear any output buffer to prevent HTML before JSON
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Redirect back with message for regular form submissions
    $message = $response['success'] ? 'success=' . urlencode($response['message']) : 'error=' . urlencode($response['error'] ?? 'Unknown error');
    header("Location: index.php?" . $message);
}
exit;

function handleWebsiteInfo($pdo, $data) {
    try {
        // Validate required fields
        $required = ['site_name', 'meta_description'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Trường {$field} là bắt buộc"];
            }
        }
        
        $siteName = trim($data['site_name']);
        $siteTagline = trim($data['site_tagline'] ?? '');
        $metaDescription = trim($data['meta_description']);
        $siteKeywords = trim($data['site_keywords'] ?? '');
        
        // Save to database or config file
        $settings = [
            'site_name' => $siteName,
            'site_tagline' => $siteTagline,
            'meta_description' => $metaDescription,
            'site_keywords' => $siteKeywords
        ];
        
        if (saveSettings($pdo, 'website_info', $settings)) {
            return ['success' => true, 'message' => 'Thông tin website đã được cập nhật thành công!'];
        } else {
            return ['success' => false, 'error' => 'Không thể lưu thông tin website'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()];
    }
}

function handleContactInfo($pdo, $data) {
    try {
        $required = ['contact_email', 'contact_phone', 'contact_address'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Trường {$field} là bắt buộc"];
            }
        }
        
        // Validate email format
        if (!filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email không hợp lệ'];
        }
        
        $settings = [
            'contact_email' => trim($data['contact_email']),
            'support_email' => trim($data['support_email'] ?? ''),
            'contact_phone' => trim($data['contact_phone']),
            'contact_hotline' => trim($data['contact_hotline'] ?? ''),
            'contact_address' => trim($data['contact_address']),
            'working_hours' => trim($data['working_hours'] ?? ''),
            'website' => trim($data['website'] ?? '')
        ];
        
        if (saveSettings($pdo, 'contact_info', $settings)) {
            return ['success' => true, 'message' => 'Thông tin liên hệ đã được cập nhật thành công!'];
        } else {
            return ['success' => false, 'error' => 'Không thể lưu thông tin liên hệ'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()];
    }
}

function handleAdminProfile($pdo, $data, $files, $userId) {
    try {
        $required = ['admin_email'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'error' => "Trường {$field} là bắt buộc"];
            }
        }
        
        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email không hợp lệ'];
        }
        
        $adminEmail = trim($data['admin_email']);
        $adminPhone = trim($data['admin_phone'] ?? '');
        
        // Handle avatar upload
        $avatarPath = null;
        if (!empty($files['admin_avatar']['name'])) {
            $avatarPath = handleFileUpload($files['admin_avatar'], 'avatar', ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
            if (!$avatarPath) {
                return ['success' => false, 'error' => 'Lỗi upload avatar'];
            }
        }
        
        // Update admin profile in database - using 'admin' table with PDO (no 'name' column)
        $sql = "UPDATE admin SET email = ?, phone = ?";
        $params = [$adminEmail, $adminPhone];
        
        if ($avatarPath) {
            $sql .= ", avatar = ?";
            $params[] = $avatarPath;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $userId;
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute($params)) {
            // Update session data
            $_SESSION['admin_name'] = $adminName;
            $_SESSION['admin_email'] = $adminEmail;
            
            return ['success' => true, 'message' => 'Thông tin quản trị viên đã được cập nhật thành công!'];
        } else {
            return ['success' => false, 'error' => 'Không thể cập nhật thông tin quản trị viên'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()];
    }
}

function handleSystemSettings($pdo, $data) {
    try {
        $settings = [
            'currency' => $data['currency'] ?? 'VND',
            'timezone' => $data['timezone'] ?? 'Asia/Ho_Chi_Minh',
            'language' => $data['language'] ?? 'vi',
            'date_format' => $data['date_format'] ?? 'd/m/Y'
        ];
        
        if (saveSettings($pdo, 'system_settings', $settings)) {
            return ['success' => true, 'message' => 'Cài đặt hệ thống đã được lưu thành công!'];
        } else {
            return ['success' => false, 'error' => 'Không thể lưu cài đặt hệ thống'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()];
    }
}

function handleFileUpload($file, $type, $allowedTypes, $maxSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        return false;
    }
    
    $uploadDir = "../assets/images/uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = $type . '_' . time() . '_' . uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return 'assets/images/uploads/' . $fileName;
    }
    
    return false;
}

function saveSettings($pdo, $category, $settings) {
    try {
        // First, create table if not exists
        $createTable = "
        CREATE TABLE IF NOT EXISTS `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `category` varchar(50) NOT NULL,
          `setting_key` varchar(100) NOT NULL,
          `setting_value` text,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_setting` (`category`, `setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        $pdo->exec($createTable);
        
        // Then save settings
        foreach ($settings as $key => $value) {
            $sql = "INSERT INTO settings (category, setting_key, setting_value) VALUES (?, ?, ?) 
                   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $stmt = $pdo->prepare($sql);
            if (!$stmt->execute([$category, $key, $value])) {
                throw new Exception("Execute failed for key: $key");
            }
        }
        return true;
    } catch (Exception $e) {
        error_log("Error saving settings: " . $e->getMessage());
        throw $e;
    }
}
?>