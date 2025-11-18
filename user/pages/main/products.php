<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

$db = (new Database())->getConnection();

// === THAM SỐ LỌC ===
$cat    = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$min    = $_GET['min_price'] ?? '';
$max    = $_GET['max_price'] ?? '';
$sort   = $_GET['sort'] ?? 'newest';
$page   = max(1, (int)($_GET['page'] ?? 1));

$limit_per_page = 12;
$offset = ($page - 1) * $limit_per_page;

// === LẤY DỮ LIỆU ===
$categories = getCategories($db);

// Lấy sản phẩm theo bộ lọc
$products = getFilteredProducts($db, $cat, $search, $min, $max, $sort, $offset, $limit_per_page);
$total_products = getFilteredProductCount($db, $cat, $search, $min, $max);
$pages = ceil($total_products / $limit_per_page);

// === HÀM LẤY SẢN PHẨM CÓ LỌC ===
function getFilteredProducts($db, $cat, $search, $min, $max, $sort, $offset, $limit) {
    $sql = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.quantity > 0
    ";
    $params = [];

    if ($cat) { $sql .= " AND c.name = ?"; $params[] = $cat; }
    if ($search) {
        $s = "%$search%";
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = $s; $params[] = $s;
    }
    if ($min !== '') { $sql .= " AND p.price >= ?"; $params[] = (float)$min; }
    if ($max !== '') { $sql .= " AND p.price <= ?"; $params[] = (float)$max; }

    $order = match($sort) {
        'price_asc' => " ORDER BY p.price ASC",
        'price_desc' => " ORDER BY p.price DESC",
        'name_asc' => " ORDER BY p.name ASC",
        'name_desc' => " ORDER BY p.name DESC",
        default => " ORDER BY p.created_at DESC"
    };
    $sql .= $order;
    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thêm fake compare_price để hiển thị giảm giá
    foreach ($products as &$product) {
        if (!isset($product['compare_price']) || empty($product['compare_price'])) {
            // Tạo giá so sánh giả cho một số sản phẩm (30% chance)
            if (rand(1, 100) <= 30) {
                $product['compare_price'] = $product['price'] * 1.2; // Tăng 20%
            } else {
                $product['compare_price'] = 0;
            }
        }
    }
    
    return $products;
}

function getFilteredProductCount($db, $cat, $search, $min, $max) {
    $sql = "SELECT COUNT(*) FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.quantity > 0";
    $params = [];

    if ($cat) { $sql .= " AND c.name = ?"; $params[] = $cat; }
    if ($search) {
        $s = "%$search%";
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = $s; $params[] = $s;
    }
    if ($min !== '') { $sql .= " AND p.price >= ?"; $params[] = (float)$min; }
    if ($max !== '') { $sql .= " AND p.price <= ?"; $params[] = (float)$max; }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function buildUrl($overrides = []) {
    $params = $_GET;
    // Loại bỏ tham số brand không còn sử dụng
    unset($params['brand']);
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($params[$k]);
        else $params[$k] = $v;
    }
    return http_build_query($params);
}

// Giả sử BASE_URL được định nghĩa ở đâu đó (nếu chưa có, thêm dòng này vào config)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/techshop/'); // Thay bằng đường dẫn thực tế
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - TechShop</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-color: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 12px 30px rgba(0, 0, 0, 0.2);
            --glow: 0 0 20px rgba(37, 99, 235, 0.4);
            --border-color: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg-card: rgba(255, 255, 255, 0.08);
            --border: #e2e8f0;
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Roboto', sans-serif; }
        body { 
            background: #f1f5f9; 
            color: #1e293b; 
            line-height: 1.7; 
            padding-top: 136px; 
            font-size: 1.5rem;
        }

        .container { max-width: 1200px; }

        /* HEADER */
        .products-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 3.5rem 0;
            text-align: center;
            margin-bottom: 2rem;
        }
        .products-header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .products-header p {
            font-size: 1.6rem;
            opacity: 0.9;
        }

        /* STICKY SIDEBAR */
        .filter-sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 150px);
            overflow-y: auto;
        }

        .filter-title {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .filter-item {
            display: block;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .filter-item:hover,
        .filter-item.active {
            background: #eff6ff;
            color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            transform: translateX(4px);
        }

        .price-filter input {
            font-size: 1.4rem;
            padding: 0.75rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .price-filter button {
            font-size: 1.4rem;
            padding: 0.75rem;
            border-radius: 12px;
            font-weight: 600;
        }

        /* BRAND TITLE */
        .brand-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin: 3rem 0 2rem;
            position: relative;
        }
        .brand-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background: var(--primary-color);
            border-radius: 3px;
        }

        /* PRODUCT CARD */
        .product-card {
            background: #fff;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
            border: 1px solid var(--border);
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 2;
        }
        .product-card:hover .product-overlay {
            opacity: 1;
            visibility: visible;
        }
        .btn-view-detail {
            background: white;
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.4rem;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .btn-view-detail:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            z-index: 3;
            box-shadow: var(--shadow);
        }
        .new-badge { background: var(--success); left: 12px; }
        .discount-badge { background: var(--danger); right: 12px; }

        .product-info {
            padding: 16px;
        }
        .product-title {
            font-size: 1.4rem;
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 8px;
            height: 44px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .old-price {
            color: var(--text-muted);
            text-decoration: line-through;
            font-size: 1.3rem;
        }
        .current-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--danger);
        }

        .product-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            flex: 1;
            border-radius: 8px;
            padding: 10px;
            font-size: 1.35rem;
            font-weight: 500;
            transition: var(--transition);
            text-align: center;
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        .btn-outline-primary {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* PAGINATION */
        .pagination .page-link {
            font-size: 1.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            margin: 0 4px;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* MOBILE FILTER */
        .mobile-filter-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.8rem;
            box-shadow: var(--shadow-lg);
            z-index: 999;
            display: none;
        }
        .mobile-filter-btn.show { display: flex; }

        /* AUTH MODAL */
        .auth-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(12px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-modal.show { display: flex; }
        .auth-modal .modal-content {
            background: white;
            padding: 32px;
            border-radius: 20px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        .auth-modal h5 {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 16px;
        }
        .auth-modal p {
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 24px;
        }
        .auth-modal .btn-close {
            position: absolute;
            top: 16px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.8rem;
            color: #666;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-modal .btn-close:hover {
            color: #333;
            background: #f1f5f9;
            border-radius: 50%;
        }
        .auth-modal .btn {
            background: var(--primary-color);
            color: white;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.5rem;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .auth-modal .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .filter-sidebar {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                background: white;
                z-index: 9999;
                padding: 2rem;
                overflow-y: auto;
                display: none;
                border-radius: 0;
            }
            .filter-sidebar.show { display: block; }
            .mobile-filter-btn { display: flex; }
            .products-header h1 { font-size: 2.3rem; }
        }

        @media (max-width: 768px) {
            body { font-size: 1.4rem; }
            .brand-title { font-size: 1.9rem; }
            .product-title { font-size: 1.4rem; height: 44px; }
            .current-price { font-size: 1.5rem; }
            .product-image { height: 180px; }
        }

        @media (max-width: 576px) {
            .product-title { font-size: 1.3rem; }
            .current-price { font-size: 1.4rem; }
            .product-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<!-- Header -->
<section class="products-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="display-5 fw-bold mb-0">Sản Phẩm</h1>
            </div>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-grid-3x3-gap"></i> Danh mục
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item <?php echo !$cat ? 'active' : ''; ?>" href="?<?php echo buildUrl(['category'=>null]); ?>">Tất cả</a></li>
                    <?php foreach ($categories as $c): ?>
                        <li><a class="dropdown-item <?php echo $cat===$c['name']?'active':''; ?>" href="?<?php echo buildUrl(['category'=>$c['name']]); ?>">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<div class="container pb-5">
    <div class="row">
        <!-- BỘ LỌC -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="filter-sidebar">
                <h5 class="filter-title">Bộ lọc</h5>

                <div class="mb-4">
                    <strong class="d-block mb-2">Danh mục</strong>
                    <a href="?<?php echo buildUrl(['category'=>null]); ?>" class="filter-item <?php echo !$cat?'active':''; ?>">Tất cả</a>
                    <?php foreach ($categories as $c): ?>
                        <a href="?<?php echo buildUrl(['category'=>$c['name']]); ?>" class="filter-item <?php echo $cat===$c['name']?'active':''; ?>">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <form method="GET" class="price-filter">
                    <?php foreach ($_GET as $k=>$v) if (!in_array($k, ['min_price','max_price','page'])): ?>
                        <input type="hidden" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars($v); ?>">
                    <?php endif; ?>
                    <strong class="d-block mb-2">Khoảng giá</strong>
                    <div class="d-flex gap-2 mb-2">
                        <input type="number" name="min_price" class="form-control" placeholder="Từ" value="<?php echo $min; ?>">
                        <input type="number" name="max_price" class="form-control" placeholder="Đến" value="<?php echo $max; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                    <?php if ($min || $max): ?>
                        <a href="?<?php echo buildUrl(['min_price'=>null,'max_price'=>null]); ?>" class="btn btn-outline-secondary w-100 mt-2">Xóa</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- SẢN PHẨM -->
        <div class="col-lg-9">
            <?php if (!empty($products)): ?>
                <div class="mb-4">
                    <h2 class="section-title">Sản phẩm 
                        <?php if ($cat): ?>
                            - <?= htmlspecialchars(ucfirst($cat)) ?>
                        <?php endif; ?>
                        (<?= $total_products ?> sản phẩm)
                    </h2>
                </div>
                <div class="row g-4">
                    <?php foreach ($products as $p): 
                        $price = $p['price'] ?? 0;
                        $compare_price = $p['compare_price'] ?? 0;
                        $discount = ($compare_price > $price && $compare_price > 0) 
                            ? round((($compare_price - $price) / $compare_price) * 100) : 0;
                        
                        // Xử lý hình ảnh
                        $img_src = 'https://via.placeholder.com/300x200?text=No+Image';
                        if (!empty($p['image'])) {
                            if (file_exists('../../../admin/assets/images/uploads/' . $p['image'])) {
                                $img_src = '../../../admin/assets/images/uploads/' . $p['image'];
                            } elseif (file_exists('../../assets/images/products/' . $p['image'])) {
                                $img_src = '../../assets/images/products/' . $p['image'];
                            }
                        }
                    ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                    <?php if ($discount > 0): ?>
                                        <div class="product-badge discount-badge">-<?= $discount ?>%</div>
                                    <?php else: ?>
                                        <div class="product-badge new-badge">Mới</div>
                                    <?php endif; ?>
                                    <div class="product-overlay">
                                        <a href="product-detail.php?id=<?= $p['id'] ?>" class="btn-view-detail">
                                            Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title"><?= htmlspecialchars($p['name']) ?></h3>
                                    <p class="product-category"><?= htmlspecialchars($p['category_name'] ?? $p['category'] ?? '') ?></p>
                                    <div class="product-price">
                                        <?php if ($compare_price > $price && $compare_price > 0): ?>
                                            <span class="old-price"><?= number_format($compare_price) ?>đ</span>
                                            <span class="current-price"><?= number_format($price) ?>đ</span>
                                        <?php else: ?>
                                            <span class="current-price"><?= number_format($price) ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-actions">
                                        <button class="btn btn-outline-primary action-btn" data-id="<?= $p['id'] ?>" data-action="buy">
                                            Mua ngay
                                        </button>
                                        <button class="btn btn-primary action-btn" data-id="<?= $p['id'] ?>" data-action="cart">
                                            Thêm vào giỏ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($pages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?<?php echo buildUrl(['page'=>$page-1]); ?>">Trước</a></li>
                            <?php endif; ?>
                            <?php for ($i = max(1, $page-2); $i <= min($pages, $page+2); $i++): ?>
                                <li class="page-item <?php echo $i==$page?'active':''; ?>">
                                    <a class="page-link" href="?<?php echo buildUrl(['page'=>$i]); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $pages): ?>
                                <li class="page-item"><a class="page-link" href="?<?php echo buildUrl(['page'=>$page+1]); ?>">Sau</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                    <h4>Không có sản phẩm phù hợp</h4>
                    <p class="text-muted">Thử thay đổi bộ lọc hoặc tìm kiếm khác.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Mobile Filter Button -->
<button class="mobile-filter-btn d-lg-none" id="mobileFilterBtn">
    <i class="bi bi-funnel"></i>
</button>

<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/back-to-top.php'; ?>

<!-- AUTH MODAL -->
<div class="auth-modal" id="authModal">
    <div class="modal-content">
        <button type="button" class="btn-close" onclick="hideAuthModal()">x</button>
        <h5>Đăng nhập để tiếp tục</h5>
        <p>Vui lòng đăng nhập để tiếp tục mua sắm.</p>
        <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn">Đăng nhập ngay</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Mobile filter
    $('#mobileFilterBtn').click(() => {
        $('.filter-sidebar').addClass('show');
    });
    $(document).on('click', function(e) {
        if ($(e.target).hasClass('filter-sidebar') || $(e.target).hasClass('btn-close')) {
            $('.filter-sidebar').removeClass('show');
        }
    });

    // Ẩn modal
    function hideAuthModal() {
        document.getElementById('authModal').classList.remove('show');
    }

    // Xử lý nút hành động
    $(document).on('click', '.action-btn', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const action = $(this).data('action');

        <?php if (!isset($_SESSION['user_id'])): ?>
            // Chưa đăng nhập → hiện modal
            document.getElementById('authModal').classList.add('show');
            // Lưu hành động để xử lý sau khi login
            sessionStorage.setItem('pendingAction', JSON.stringify({ productId, action }));
        <?php else: ?>
            // Đã đăng nhập → thực hiện
            $.post('../../ajax/add_to_cart.php', { product_id: productId, quantity: 1 }, function(res) {
                if (res.success) {
                    if (action === 'cart') {
                        alert('Đã thêm vào giỏ hàng!');
                        const badge = $('.cart-badge');
                        badge.text(parseInt(badge.text() || 0) + 1);
                    } else {
                        window.location.href = '../../pages/main/checkout.php';
                    }
                } else {
                    alert(res.message || 'Lỗi!');
                }
            }, 'json');
        <?php endif; ?>
    });

    // Tự động xử lý sau khi quay lại từ login (tùy chọn)
    window.addEventListener('load', function() {
        const pending = sessionStorage.getItem('pendingAction');
        if (pending) {
            sessionStorage.removeItem('pendingAction');
            // Có thể thêm logic tự động thêm giỏ nếu cần
        }
    });
</script>

</body>
</html>