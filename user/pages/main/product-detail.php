<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

$db = (new Database())->getConnection();

$product_id = $_GET['id'] ?? 0;
$product = getProductById($db, $product_id);

if (!$product) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$product_images = getProductImages($db, $product_id);
$product_reviews = getProductReviews($db, $product_id);
$related_products = getRelatedProducts($db, $product_id, $product['category_id'], 4);

incrementProductViews($db, $product_id);

// Set default rating since reviews are not implemented yet
$avg_rating = 4.5; // Default rating for display
$review_count = 0; // No reviews yet
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - TechShop</title>

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
            padding-top: 160px; 
            font-size: 1rem; /* Nội dung chính - đã giảm từ 1.5rem */
        }

        .container { max-width: 1200px; }

        /* ============================= */
        /* BREADCRUMB - 1.7rem (17px) */
        /* ============================= */
        .breadcrumb {
            background: white;
            padding: 14px 20px;
            border-radius: 14px;
            box-shadow: var(--shadow);
            font-size: 1.7rem;
            font-weight: 500;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }
        .breadcrumb-item a { 
            color: var(--primary-color); 
            text-decoration: none; 
            font-size: 1.7rem;
            font-weight: 500;
        }
        .breadcrumb-item a:hover { 
            color: var(--primary-dark); 
            text-decoration: underline;
        }
        .breadcrumb-item.active { 
            color: var(--dark-color); 
            font-weight: 600;
            font-size: 1.7rem;
        }

        /* ============================= */
        /* PRODUCT TITLE - 2.6rem */
        /* ============================= */
        .product-title {
            font-size: 2.6rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 16px;
            line-height: 1.3;
        }

        /* ============================= */
        /* META INFO */
        /* ============================= */
        .product-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .rating-stars {
            font-size: 1.3rem;
            color: var(--warning);
        }
        .review-count { 
            font-size: 1.5rem; 
            color: var(--text-muted); 
            font-weight: 500;
        }

        /* ============================= */
        /* PRICE - 2.4rem */
        /* ============================= */
        .product-price {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .current-price {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .old-price {
            font-size: 1.6rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        .discount-badge {
            background: var(--danger);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        /* ============================= */
        /* STOCK & QUANTITY */
        /* ============================= */
        .stock-status {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--success);
            font-weight: 600;
            margin: 16px 0;
            font-size: 1.5rem;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 24px 0;
            font-size: 1.5rem;
        }
        .quantity-controls {
            display: flex;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }
        .quantity-btn {
            width: 48px;
            height: 48px;
            background: white;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            transition: 0.3s;
        }
        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        .quantity-input {
            width: 70px;
            height: 48px;
            border: none;
            text-align: center;
            font-weight: 600;
            font-size: 1.5rem;
        }

        /* ============================= */
        /* BUTTONS - 1.5rem */
        /* ============================= */
        .product-actions {
            display: flex;
            gap: 16px;
            margin: 28px 0;
            flex-wrap: wrap;
        }
        .btn-custom {
            flex: 1;
            min-width: 160px;
            padding: 14px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }
        .btn-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }
        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
            border: 2px solid var(--primary-color);
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--glow);
        }

        /* ============================= */
        /* BENEFITS */
        /* ============================= */
        .product-benefits {
            background: #f8fafc;
            padding: 20px;
            border-radius: 14px;
            border-left: 5px solid var(--primary-color);
            margin-top: 24px;
        }
        .benefit-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            font-size: 1.5rem;
        }
        .benefit-item i {
            font-size: 1.6rem;
            color: var(--primary-color);
        }

        /* ============================= */
        /* GALLERY */
        /* ============================= */
        .product-gallery {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        .main-image {
            height: 440px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            background: #f8fafc;
        }
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.4s ease;
        }
        .main-image img:hover { transform: scale(1.05); }

        .thumbnail-container {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding: 10px 0;
            scrollbar-width: thin;
        }
        .thumbnail {
            width: 100px;
            height: 100px;
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            flex-shrink: 0;
            box-shadow: var(--shadow);
        }
        .thumbnail:hover {
            border-color: var(--primary-color);
            transform: scale(1.1);
            box-shadow: var(--glow);
        }
        .thumbnail.active {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
        }
        .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

        /* ============================= */
        /* TABS - 1.7rem */
        /* ============================= */
        .nav-tabs {
            border: none;
            margin-bottom: 28px;
            gap: 12px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 10px;
        }
        .nav-tabs .nav-link {
            border: none;
            background: #f1f5f9;
            color: var(--dark-color);
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1.7rem;
            transition: 0.3s;
        }
        .nav-tabs .nav-link.active,
        .nav-tabs .nav-link:hover {
            background: var(--primary-color);
            color: white;
            box-shadow: var(--shadow);
        }

        .tab-content {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            font-size: 1.5rem;
            line-height: 1.8;
        }

        /* ============================= */
        /* SECTION TITLE - 1.9rem */
        /* ============================= */
        .section-title {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-color);
        }

        /* SPECS TABLE */
        .specifications-section {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
        }
        .spec-category-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .spec-category-title:before {
            content: "📋";
            font-size: 1.6rem;
        }
        .specs-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .specs-table td {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 1.5rem;
            vertical-align: top;
        }
        .specs-table tr:last-child td {
            border-bottom: none;
        }
        .specs-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .specs-table tr:hover {
            background: #e0f2fe;
            transition: background 0.2s ease;
        }
        .spec-name {
            font-weight: 600;
            color: var(--dark-color);
            width: 35%;
            position: relative;
        }
        .spec-name:after {
            content: ":";
            margin-left: 4px;
            color: var(--primary-color);
        }
        .spec-value {
            color: #475569;
            font-weight: 500;
        }
        .no-specs {
            padding: 40px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 16px;
            border: 2px dashed #cbd5e1;
        }

        /* REVIEW */
        .review-item {
            background: #f8fafc;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary-color);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .review-header strong {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .review-header small {
            font-size: 1.3rem;
            color: var(--text-muted);
        }

        /* ============================= */
        /* PRODUCT CARD - GIỐNG INDEX.PHP */
        /* ============================= */
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

        .product-info {
            padding: 16px;
        }
        .related-products .product-title {
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
        .related-products .product-price {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }
        .related-products .current-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .product-actions .btn {
            border-radius: 8px;
            padding: 10px;
            font-size: 1.35rem;
            font-weight: 500;
            transition: var(--transition);
            text-align: center;
        }
        .btn-outline-primary {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }

        /* ============================= */
        /* BACK TO TOP */
        /* ============================= */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.6rem;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: 0.3s;
            z-index: 1000;
        }
        .back-to-top.show { display: flex; }
        .back-to-top:hover {
            background: var(--primary-dark);
            transform: translateY(-6px);
        }

        /* ============================= */
        /* AUTH MODAL */
        /* ============================= */
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
        }
        .auth-modal .btn {
            background: var(--primary-color);
            color: white;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.5rem;
            width: 100%;
        }

        /* ============================= */
        /* RESPONSIVE - THEO CHUẨN BẠN */
        /* ============================= */
        @media (max-width: 992px) {
            .product-title { font-size: 2.3rem; }
            .current-price { font-size: 2.1rem; }
            .breadcrumb,
            .breadcrumb-item a,
            .breadcrumb-item.active { font-size: 1.6rem; }
        }

        @media (max-width: 768px) {
            body { font-size: 1.4rem; }
            .product-title { font-size: 2.1rem; }
            .current-price { font-size: 1.9rem; }
            .section-title { font-size: 1.7rem; }
            .breadcrumb,
            .breadcrumb-item a,
            .breadcrumb-item.active { font-size: 1.6rem; }
            .btn-custom { font-size: 1.4rem; padding: 12px 20px; }
            .main-image { height: 380px; }
            .product-image { height: 180px; }
        }

        @media (max-width: 576px) {
            .product-title { font-size: 1.9rem; }
            .current-price { font-size: 1.7rem; }
            body { font-size: 1.4rem; }
            .breadcrumb,
            .breadcrumb-item a,
            .breadcrumb-item.active { font-size: 1.5rem; }
            .quantity-btn { width: 44px; height: 44px; font-size: 1.3rem; }
            .quantity-input { width: 60px; height: 44px; font-size: 1.3rem; }
            .btn-custom { font-size: 1.4rem; padding: 10px 16px; min-width: 100%; }
            .product-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
</div>

<section class="product-detail py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image">
                        <?php 
                        $main_image_src = isset($product_images[0]['image_path']) ? getProductImage($product_images[0]['image_path'], 'product-detail') : getProductImage($product['image'], 'product-detail'); 
                        ?>
                        <img id="main-product-image" src="<?php echo $main_image_src; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <?php if (count($product_images) > 1): ?>
                    <div class="thumbnail-container">
                        <?php foreach ($product_images as $i => $img): ?>
                        <div class="thumbnail <?php echo $i === 0 ? 'active' : ''; ?>" data-image="<?php echo getProductImage($img['image_path'], 'product-detail'); ?>">
                            <img src="<?php echo getProductImage($img['image_path'], 'product-detail'); ?>" alt="">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif (!empty($product['image'])): ?>
                    <div class="thumbnail-container">
                        <div class="thumbnail active" data-image="<?php echo getProductImage($product['image'], 'product-detail'); ?>">
                            <img src="<?php echo getProductImage($product['image'], 'product-detail'); ?>" alt="">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <div class="product-meta">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?php echo $i <= $avg_rating ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="review-count">(<?php echo $review_count; ?> đánh giá)</span>
                    </div>

                    <div class="product-price">
                        <span class="current-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                        <?php if ($product['compare_price'] > $product['price']): ?>
                            <span class="old-price"><?php echo number_format($product['compare_price'], 0, ',', '.'); ?>đ</span>
                            <span class="discount-badge">
                                -<?php echo round(100 - ($product['price'] / $product['compare_price'] * 100)); ?>%
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="stock-status">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Còn hàng</span>
                    </div>

                    <div class="quantity-selector">
                        <span>Số lượng:</span>
                        <div class="quantity-controls">
                            <button class="quantity-btn" id="decrease-quantity">-</button>
                            <input type="number" class="quantity-input" id="product-quantity" value="1" min="1">
                            <button class="quantity-btn" id="increase-quantity">+</button>
                        </div>
                    </div>

                    <div class="product-actions">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="btn-custom btn-outline add-to-cart" data-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                            <button class="btn-custom btn-primary-custom buy-now" data-id="<?php echo $product['id']; ?>">
                                <i class="bi bi-lightning-charge-fill"></i> Mua ngay
                            </button>
                        <?php else: ?>
                            <button class="btn-custom btn-outline" onclick="showAuthModal()">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </button>
                            <button class="btn-custom btn-primary-custom" onclick="showAuthModal()">
                                <i class="bi bi-lightning-charge-fill"></i> Mua ngay
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="product-benefits">
                        <div class="benefit-item"><i class="bi bi-truck"></i> Miễn phí vận chuyển toàn quốc</div>
                        <div class="benefit-item"><i class="bi bi-shield-check"></i> Bảo hành chính hãng 12-24 tháng</div>
                        <div class="benefit-item"><i class="bi bi-arrow-repeat"></i> Đổi trả dễ dàng trong 7 ngày</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="product-tabs mt-5">
            <h3 class="section-title">Chi tiết sản phẩm</h3>
            <ul class="nav nav-tabs" id="productTabs">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Mô tả</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#specs">Thông số</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Đánh giá (<?php echo $review_count; ?>)</button></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="desc">
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-description">
                            <?php echo $product['description']; ?>
                        </div>
                    <?php elseif (!empty($product['short_description'])): ?>
                        <div class="p-4 bg-light rounded">
                            <p><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-light rounded text-center">
                            <i class="bi bi-info-circle text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted">Chưa có mô tả chi tiết cho sản phẩm này.</p>
                            <small class="text-muted">Thông tin chi tiết sẽ được cập nhật sớm nhất.</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="specs">
                    <?php 
                    $specs = getProductSpecifications($db, $product_id); 
                    $category_name = $product['category_name'] ?? '';
                    ?>
                    <?php if ($specs): ?>
                        <div class="specifications-section">
                            <h5 class="spec-category-title">Thông số kỹ thuật</h5>
                            <table class="specs-table">
                                <?php foreach ($specs as $s): ?>
                                <tr>
                                    <td class="spec-name"><?php echo htmlspecialchars($s['attribute_name']); ?></td>
                                    <td class="spec-value"><?php echo nl2br(htmlspecialchars($s['attribute_value'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-specs text-center">
                            <i class="bi bi-info-circle-fill text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted">Chưa có thông số kỹ thuật cho sản phẩm này.</p>
                            <small class="text-muted">Thông tin chi tiết sẽ được cập nhật sớm nhất.</small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="reviews">
                    <?php if ($product_reviews): ?>
                        <?php foreach ($product_reviews as $r): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div>
                                    <strong><?php echo htmlspecialchars($r['full_name']); ?></strong>
                                    <div class="rating-stars d-inline ms-2">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="bi <?php echo $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star'; ?> text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></small>
                            </div>
                            <p class="mt-2 mb-0"><?php echo htmlspecialchars($r['comment']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted">Chưa có đánh giá nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($related_products): ?>
        <div class="related-products mt-5">
            <h3 class="section-title">Sản phẩm liên quan</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $p): 
                    $img_src = getProductImage($p['image'], 'product-card');
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <div class="product-overlay">
                                <a href="product-detail.php?id=<?php echo $p['id']; ?>" class="btn-view-detail">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h6 class="product-title"><?php echo htmlspecialchars($p['name']); ?></h6>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($p['price'], 0, ',', '.'); ?>đ</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-outline-primary w-100 add-to-cart" data-id="<?php echo $p['id']; ?>">
                                    Thêm giỏ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>

<button class="back-to-top" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

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
    // Thumbnail
    $('.thumbnail').click(function() {
        $('#main-product-image').attr('src', $(this).data('image'));
        $('.thumbnail').removeClass('active');
        $(this).addClass('active');
    });

    // Quantity
    $('#increase-quantity').click(() => {
        let input = $('#product-quantity');
        input.val(Math.min(parseInt(input.val()) + 1, 999));
    });
    $('#decrease-quantity').click(() => {
        let input = $('#product-quantity');
        input.val(Math.max(parseInt(input.val()) - 1, 1));
    });

    // Add to cart
    $('.add-to-cart').click(function() {
        if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            showAuthModal();
            return;
        }
        $.post('../../ajax/add_to_cart.php', {
            product_id: $(this).data('id'),
            quantity: $('#product-quantity').val() || 1
        }, res => {
            if (res.success) {
                alert('Đã thêm vào giỏ hàng!');
                $('.cart-badge').text(res.cart_count).show();
            } else {
                alert(res.message || 'Lỗi');
            }
        }, 'json');
    });

    // Buy now
    $('.buy-now').click(function() {
        if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            showAuthModal();
            return;
        }
        location.href = '<?php echo BASE_URL; ?>pages/main/checkout.php?product_id=' + $(this).data('id') + '&quantity=' + ($('#product-quantity').val() || 1);
    });

    // Back to top
    $(window).scroll(() => $('#backToTop').toggleClass('show', $(window).scrollTop() > 300));
    $('#backToTop').click(() => $('html,body').animate({scrollTop: 0}, 600));

    // Modal
    function showAuthModal() { $('#authModal').addClass('show'); }
    function hideAuthModal() { $('#authModal').removeClass('show'); }
    $('#authModal').click(e => e.target === this && hideAuthModal());
</script>

</body>
</html>
