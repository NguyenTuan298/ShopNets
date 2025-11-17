<?php
// functions.php - File chứa tất cả các hàm hỗ trợ cho ứng dụng Omnio

/**
 * Lấy danh mục sản phẩm
 */
function getCategories($db) {
    $sql = "SELECT * FROM categories ORDER BY name";
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi getCategories: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy sản phẩm nổi bật
 */
function getFeaturedProducts($db, $limit = 8) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy sản phẩm mới
 */
function getNewProducts($db, $limit = 8) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy thương hiệu
 */
// Brands table doesn't exist, return empty array
function getBrands($db) {
    return [];
}

/**
 * Lấy bài viết mới nhất
 */
/* COMMENTED - posts table doesn't exist
function getLatestPosts($db, $limit = 3) {
    $query = "SELECT p.*, u.full_name as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.author_id = u.id 
              WHERE p.status = 'published' 
              ORDER BY p.published_at DESC 
              LIMIT " . (int)$limit;
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/

// Temporary function - return empty array
function getLatestPosts($db, $limit = 3) {
    return [];
}

/**
 * Lấy đánh giá trung bình của sản phẩm
 */
/* COMMENTED - reviews table doesn't exist
function getProductRating($db, $product_id) {
    $query = "SELECT AVG(rating) as avg_rating FROM reviews 
              WHERE product_id = ? AND is_approved = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['avg_rating'] ? round($result['avg_rating']) : 0;
}
*/

// Temporary function - return 0
function getProductRating($db, $product_id) {
    return 0;
}

/**
 * Lấy số lượng đánh giá của sản phẩm
 */
/* COMMENTED - reviews table doesn't exist
function getReviewCount($db, $product_id) {
    $query = "SELECT COUNT(*) as count FROM reviews 
              WHERE product_id = ? AND is_approved = 1";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}
*/

// Temporary function - return 0
function getReviewCount($db, $product_id) {
    return 0;
}

/**
 * Lấy thông tin sản phẩm theo ID
 */
function getProductById($db, $product_id) {
    try {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category = c.name 
                  WHERE p.id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi getProductById: " . $e->getMessage());
        return null;
    }
}

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function addToCart($product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $quantity = max(1, (int)$quantity);
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

/**
 * Lấy tất cả sản phẩm trong giỏ hàng
 */
/**
 * LẤY GIỎ HÀNG - ĐÃ SỬA 100% LỖI IN()
 */
function getCartItems($db) {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }

    $cart_items = [];
    $product_ids = array_keys($_SESSION['cart']);
    $valid_ids = [];

    // Lọc ID hợp lệ
    foreach ($product_ids as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $valid_ids[] = $id;
        }
    }

    if (empty($valid_ids)) {
        return [];
    }

    // TẠO IN() CHÍNH XÁC
    $placeholders = implode(',', array_fill(0, count($valid_ids), '?'));

    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.id IN ($placeholders) 
              AND p.inventory > 0";

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($valid_ids);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as $product) {
            $pid = $product['id'];
            if (isset($_SESSION['cart'][$pid])) {
                $cart_items[] = [
                    'product' => $product,
                    'quantity' => (int)$_SESSION['cart'][$pid]
                ];
            }
        }

        return $cart_items;
    } catch (PDOException $e) {
        error_log("getCartItems SQL Error: " . $e->getMessage());
        error_log("Query: " . $query);
        error_log("IDs: " . implode(', ', $valid_ids));
        return [];
    }
}

/**
 * Tính tổng tiền giỏ hàng
 */
function getCartTotal($db) {
    $cart_items = getCartItems($db);
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['product']['price'] * $item['quantity'];
    }
    
    return $total;
}

/**
 * Lấy số lượng sản phẩm trong giỏ hàng
 */
function getCartCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Lấy hình ảnh sản phẩm
 */
/**
 * Trả về đường dẫn ảnh sản phẩm hợp lệ
 * @param string|null $image_path
 * @return string
 */
function getProductImage($image_path) {
    if (empty($image_path)) {
        return 'https://via.placeholder.com/300x200/64748b/ffffff?text=No+Image';
    }

    // Check if it's an admin uploaded image
    if (strpos($image_path, 'admin/assets/images/uploads/') === 0) {
        if (file_exists('../' . $image_path)) {
            return '../' . $image_path;
        }
    } else {
        // Check user assets folder
        $full_path = 'user/assets/images/products/' . $image_path;
        if (file_exists($full_path)) {
            return $full_path;
        }
    }

    return 'https://via.placeholder.com/300x200/64748b/ffffff?text=No+Image';
}

// Flash sale products - use random products with discounted prices
function getFlashSaleProducts($db, $limit = 8) {
    $limit = (int)$limit; // Ensure integer
    $query = "
        SELECT 
            p.id, p.name, p.price, p.image
        FROM products p
        WHERE p.inventory > 0
          AND p.price > 100000
        ORDER BY RAND()
        LIMIT $limit
    ";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add flash sale discount (20-50% off)
        foreach ($products as &$product) {
            $discount = rand(20, 50);
            $product['compare_price'] = $product['price'];
            $product['price'] = $product['price'] * (100 - $discount) / 100;
        }
        
        return $products;
    } catch (PDOException $e) {
        error_log("getFlashSaleProducts Error: " . $e->getMessage());
        return [];
    }
}

/**
 * LẤY TẤT CẢ ẢNH CỦA SẢN PHẨM
 * @param PDO $db
 * @param int $product_id
 * @return array
 */
function getProductImages($db, $product_id) {
    try {
        $query = "SELECT * FROM product_images 
                  WHERE product_id = ? 
                  ORDER BY is_primary DESC, id ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi getProductImages: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy thuộc tính sản phẩm
 */
function getProductAttributes($db, $product_id) {
    $query = "SELECT pa.*, a.name as attribute_name, av.value as attribute_value, a.type 
              FROM product_attributes pa 
              LEFT JOIN attributes a ON pa.attribute_id = a.id 
              LEFT JOIN attribute_values av ON pa.attribute_value_id = av.id 
              WHERE pa.product_id = ? 
              ORDER BY a.sort_order, av.sort_order";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy đánh giá sản phẩm
 */
function getProductReviews($db, $product_id) {
    $query = "SELECT r.*, u.full_name 
              FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.product_id = ? AND r.is_approved = 1 
              ORDER BY r.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy sản phẩm liên quan
 */
function getRelatedProducts($db, $product_id, $category_id, $limit = 4) {
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = :category_id 
              AND p.id != :product_id 
              AND p.is_active = 1 
              ORDER BY RAND() 
              LIMIT " . (int)$limit;
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tăng lượt xem sản phẩm
 */
function incrementProductViews($db, $product_id) {
    $query = "UPDATE products SET view_count = view_count + 1 WHERE id = ?";
    $stmt = $db->prepare($query);
    return $stmt->execute([$product_id]);
}

/**
 * Lấy thông tin người dùng theo ID
 */
function getUserById($db, $id) {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Lấy thông tin người dùng theo email
 */
function getUserByEmail($db, $email) {
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Tạo người dùng mới
 */
function createUser($db, $username, $email, $password, $full_name, $phone = null, $address = null) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (username, email, password, full_name, phone, address) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    return $stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address]);
}

/**
 * Xác minh mật khẩu
 */
function verifyPassword($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

/**
 * Lấy cài đặt hệ thống
 */
function getSettings($db) {
    $query = "SELECT key_name, key_value FROM settings";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    foreach ($settings as $setting) {
        $result[$setting['key_name']] = $setting['key_value'];
    }
    
    return $result;
}

/**
 * Định dạng giá tiền
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

/**
 * Lấy đơn hàng theo số đơn hàng
 */
function getOrderByNumber($db, $order_number) {
    $query = "SELECT o.*, u.full_name, u.email, u.phone 
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              WHERE o.order_number = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Tạo số đơn hàng
 */
function generateOrderNumber() {
    return 'OMN' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * Kiểm tra tồn kho sản phẩm
 */
function checkProductStock($db, $product_id, $quantity = 1) {
    $query = "SELECT inventory FROM products WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $product && $product['inventory'] >= $quantity;
}

/**
 * Cập nhật tồn kho sản phẩm
 */
function updateProductStock($db, $product_id, $quantity) {
    $query = "UPDATE products SET inventory = inventory - ? WHERE id = ?";
    $stmt = $db->prepare($query);
    return $stmt->execute([$quantity, $product_id]);
}

// Lấy thông tin chi tiết đơn hàng theo ID và user_id
function getOrderById($db, $order_id, $user_id) {
    try {
        $sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$order_id, $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting order by ID: " . $e->getMessage());
        return false;
    }
}

// Lấy lịch sử trạng thái đơn hàng
function getOrderStatusHistory($db, $order_id) {
    try {
        $sql = "SELECT status, created_at FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting order status history: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy danh sách sản phẩm trong đơn hàng với thông tin chi tiết
 */
function getOrderItemsWithDetails($db, $order_id) {
    try {
        $query = "SELECT oi.*, p.name as product_name, p.sku, c.name as category_name 
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE oi.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting order items with details: " . $e->getMessage());
        return [];
    }
}

/**
 * Ghi log lượt xem sản phẩm
 */
function logProductView($db, $product_id, $user_id = null, $session_id = null, $ip_address = null, $user_agent = null) {
    $query = "INSERT INTO product_views (product_id, user_id, session_id, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    return $stmt->execute([$product_id, $user_id, $session_id, $ip_address, $user_agent]);
}

/**
 * Lấy sản phẩm phổ biến
 */
function getPopularProducts($db, $limit = 6) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy danh sách đơn hàng của người dùng 
 */
function getUserOrders($db, $user_id) {
    try {
        $query = "SELECT o.*, 
                         o.order_status as status,
                         COUNT(oi.id) as item_count,
                         COALESCE(SUM(oi.total_price), 0) as total_amount
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.user_id = ? 
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getUserOrders error: " . $e->getMessage());
        return [];
    }
}

/**
 * Lấy chi tiết items của đơn hàng 
 */
function getOrderItems($db, $order_id) {
    try {
        $query = "SELECT oi.*, 
                         oi.product_price as price,
                         p.name as product_name
                  FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getOrderItems error: " . $e->getMessage());
        return [];
    }
}

/**
 * Hủy đơn hàng - ĐÃ SỬA
 */
function cancelOrder($db, $order_id, $user_id) {
    try {
        $sql = "SELECT id, order_status FROM orders WHERE id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || !in_array($order['order_status'], ['pending', 'processing'])) {
            return false;
        }

        $sql = "UPDATE orders SET order_status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$order_id]);

        if ($result) {
            $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
            $items_stmt = $db->prepare($items_sql);
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $update_stock = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
                $stock_stmt = $db->prepare($update_stock);
                $stock_stmt->execute([$item['quantity'], $item['product_id']]);
            }
        }

        return $result;
    } catch (Exception $e) {
        error_log("Cancel Order Error: " . $e->getMessage());
        return false;
    }
}

function getProductSpecifications($db, $product_id) {
    $stmt = $db->prepare("
        SELECT 
            a.name as attribute_name,
            COALESCE(av.value, pa.value_text) as attribute_value
        FROM product_attributes pa
        JOIN attributes a ON pa.attribute_id = a.id
        LEFT JOIN attribute_values av ON pa.attribute_value_id = av.id
        WHERE pa.product_id = ?
        ORDER BY a.sort_order, a.id
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Kiểm tra xem đơn hàng có thể hủy không 
 */
function canCancelOrder($order) {
    $status = $order['order_status'] ?? $order['status'] ?? 'pending';
    return in_array($status, ['pending', 'processing']);
}

/**
 * Lấy order items cho modal đánh giá (AJAX)
 */
function getOrderItemsForReview($db, $order_id, $user_id) {
    try {
        $query = "SELECT oi.id as order_item_id, oi.product_id, oi.product_name,
                         (SELECT COUNT(*) FROM reviews r WHERE r.order_item_id = oi.id AND r.user_id = ?) as has_reviewed
                  FROM order_items oi 
                  WHERE oi.order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting order items for review: " . $e->getMessage());
        return [];
    }
}

/**
 * Kiểm tra xem sản phẩm đã được đánh giá chưa
 */
function hasReviewed($db, $user_id, $product_id, $order_item_id) {
    try {
        $query = "SELECT id FROM reviews WHERE user_id = ? AND product_id = ? AND order_item_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id, $product_id, $order_item_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Gửi đánh giá sản phẩm
 */
function submitReview($db, $user_id, $product_id, $order_item_id, $rating, $comment) {
    try {
        $query = "INSERT INTO reviews (user_id, product_id, order_item_id, rating, comment, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        return $stmt->execute([$user_id, $product_id, $order_item_id, $rating, $comment]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Lấy chi tiết đơn hàng (cho trang profile)
 */
function getOrderItemsForProfile($db, $order_id) {
    $query = "SELECT oi.*, p.name as product_name 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Cập nhật thông tin người dùng
 */
function updateUserProfile($db, $user_id, $username, $email, $phone = null) {
    $query = "UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    return $stmt->execute([$username, $email, $phone, $user_id]);
}

/**
 * Đổi mật khẩu người dùng
 */
function changeUserPassword($db, $user_id, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $query = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    return $stmt->execute([$hashed_password, $user_id]);
}

/**
 * Kiểm tra email đã tồn tại (trừ user hiện tại)
 */
function isEmailExists($db, $email, $exclude_user_id = null) {
    $query = "SELECT id FROM users WHERE email = ?";
    $params = [$email];
    
    if ($exclude_user_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_user_id;
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

/**
 * Lấy sản phẩm theo danh mục
 */
function getProductsByCategory($db, $category_id, $limit = 12) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.category = ? AND p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy sản phẩm theo thương hiệu
 */
function getProductsByBrand($db, $brand_id, $limit = 12) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Tìm kiếm sản phẩm
 */
function searchProducts($db, $keyword, $limit = 12) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE (p.name LIKE ? OR p.description LIKE ?) 
              AND p.inventory > 0 
              ORDER BY p.created_at DESC 
              LIMIT $limit";
    $stmt = $db->prepare($query);
    $search_term = "%$keyword%";
    $stmt->execute([$search_term, $search_term]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy sản phẩm theo bộ lọc
 */
function getProducts($db, $filters = []) {
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category = c.name 
            WHERE p.inventory > 0";

    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category = ?";
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search = "%" . $filters['search'] . "%";
        $params[] = $search;
        $params[] = $search;
    }

    $sql .= " ORDER BY p.created_at DESC";

    if (!empty($filters['limit'])) {
        $limit = (int)$filters['limit'];
        $sql .= " LIMIT $limit";
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getProducts error: " . $e->getMessage());
        return [];
    }
}

// Lấy sản phẩm mới nhất theo danh mục
function getLatestProductsByBrand($db, $limit_per_brand = 2) {
    $limit = (int)($limit_per_brand * 4); // Calculate limit as integer
    $query = "
        SELECT 
            p.id, p.name, p.price, p.created_at, p.image,
            p.category as category_name
        FROM products p
        WHERE p.price > 0 AND p.inventory > 0
        ORDER BY p.created_at DESC
        LIMIT $limit
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy sản phẩm khuyến mãi (tạo giá so sánh giả)
function getDiscountProductsByBrand($db, $limit_per_brand = 2) {
    $limit = (int)($limit_per_brand * 4); // Calculate limit as integer
    $query = "
        SELECT 
            p.id, p.name, p.price, p.created_at, p.image,
            p.category as category_name
        FROM products p
        WHERE p.price > 0 AND p.inventory > 0
        ORDER BY p.created_at DESC
        LIMIT $limit
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add fake compare_price for discount display
    foreach ($products as &$product) {
        $product['compare_price'] = $product['price'] * 1.3; // 30% higher
    }
    
    return $products;
}

function getProductsByBrandBalanced($db) {
    $query = "
        SELECT 
            p.id, p.name, p.price, p.compare_price, p.created_at, p.brand_id,
            b.name as brand_name,
            c.name as category_name,
            (p.compare_price > p.price) as has_discount,
            ROW_NUMBER() OVER (PARTITION BY p.brand_id ORDER BY p.created_at DESC) as rn_new,
            ROW_NUMBER() OVER (PARTITION BY p.brand_id ORDER BY (p.compare_price - p.price) DESC, p.compare_price DESC) as rn_discount
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1 AND p.quantity > 0  // Đã sửa từ p.status thành p.is_active
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($all_products as $product) {
        $brand_id = $product['brand_id'];
        if (!isset($result[$brand_id])) {
            $result[$brand_id] = [
                'brand_name' => $product['brand_name'],
                'new' => [],
                'discount' => []
            ];
        }

        // Lấy 2 sản phẩm mới nhất
        if ($product['rn_new'] <= 2) {
            $result[$brand_id]['new'][] = $product;
        }

        // Lấy 2 sản phẩm giảm giá nhiều nhất
        if ($product['has_discount'] && $product['rn_discount'] <= 2) {
            $result[$brand_id]['discount'][] = $product;
        }
    }

    // Giới hạn mỗi brand chỉ 4 sản phẩm: 2 mới + 2 giảm
    foreach ($result as $brand_id => $data) {
        // Nếu không đủ 2 giảm giá → bổ sung từ sản phẩm mới
        while (count($data['discount']) < 2 && count($data['new']) > 0) {
            $data['discount'][] = array_shift($data['new']);
        }
        // Nếu vẫn thiếu → bỏ qua brand này
        if (count($data['new']) + count($data['discount']) < 4) {
            unset($result[$brand_id]);
            continue;
        }
        // Cắt bớt nếu thừa
        $result[$brand_id]['new'] = array_slice($data['new'], 0, 2);
        $result[$brand_id]['discount'] = array_slice($data['discount'], 0, 2);
    }

    return $result;
}

/**
 * Lọc sản phẩm
 */
function filterProducts($db, $filters = [], $limit = 12) {
    $limit = (int)$limit;
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category = c.name 
              WHERE p.inventory > 0";
    
    $params = [];
    
    if (!empty($filters['category_id'])) {
        $query .= " AND p.category = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['min_price'])) {
        $query .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $query .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
    }
    
    $query .= " ORDER BY p.created_at DESC LIMIT $limit";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lấy số lượng sản phẩm theo danh mục
 */
function getProductCountByCategory($db, $category_id) {
    $query = "SELECT COUNT(*) as count FROM products WHERE category = ? AND inventory > 0";
    $stmt = $db->prepare($query);
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}

/**
 * Lấy thông tin danh mục theo slug
 * @param PDO $db
 * @param string $slug
 * @return array|false
 */
function getCategoryBySlug($db, $slug) {
    try {
        $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1 LIMIT 1");  // Sửa từ 'status = active' thành 'is_active = 1'
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getCategoryBySlug error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy thương hiệu theo slug
 */
function getBrandBySlug($db, $slug) {
    try {
        $stmt = $db->prepare("SELECT * FROM brands WHERE slug = ? AND is_active = 1 LIMIT 1");  // Sửa từ 'status = active' thành 'is_active = 1'
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getBrandBySlug error: " . $e->getMessage());
        return false;
    }
}

/**
 * Lấy số lượng sản phẩm theo thương hiệu
 */
function getProductCountByBrand($db, $brand_id) {
    $query = "SELECT COUNT(*) as count FROM products WHERE inventory > 0";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'];
}


/**
 * Tạo slug từ chuỗi tiếng Việt
 */
function createSlug($str) {
    $str = trim(mb_strtolower($str));
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
    $str = preg_replace('/(đ)/', 'd', $str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}



/**
 * Lấy danh sách đơn hàng theo user_id
 */
function getOrdersByUserId($db, $user_id) {
    try {
        $query = "SELECT o.*, 
                         COUNT(oi.id) as item_count,
                         SUM(oi.total_price) as total_amount
                  FROM orders o 
                  LEFT JOIN order_items oi ON o.id = oi.order_id 
                  WHERE o.user_id = ? 
                  GROUP BY o.id 
                  ORDER BY o.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting orders: " . $e->getMessage());
        return [];
    }
}
?>