<?php
$home_url = (function_exists('isLocalhost') && isLocalhost()) 
    ? 'http://localhost/shopnets/' 
    : 'https://shopnets.infinityfree.me/';

$user_url = $home_url . 'user/';

$cart_count = 0;
if (function_exists('getCartCount')) {
    try { $cart_count = getCartCount(); } catch (Exception $e) { $cart_count = 0; }
}

// THÊM PHẦN NÀY ĐỂ LẤY CATEGORIES - SỬ DỤNG ĐƯỜNG DẪN TUYỆT ĐỐI
$categories = [];
try {
    // Xác định đường dẫn tuyệt đối đến thư mục gốc
    $root_dir = dirname(__DIR__); // Thoát khỏi thư mục includes
    
    require_once $root_dir . '/includes/database.php';
    require_once $root_dir . '/includes/functions.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $categories = getCategories($db);
} catch (Exception $e) {
    // Nếu có lỗi, sử dụng categories mặc định
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* === FIX TRIỆT ĐỂ VÙNG XANH KHI CLICK (tất cả trình duyệt) === */
        *, *::before, *::after { 
            outline: none !important; 
            -webkit-tap-highlight-color: transparent !important;
        }
        a, button, input, [tabindex] {
            outline: none !important;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #1e293b;
            --light: #f8fafc;
            --shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        .header-spacing { padding-top: 136px; }

        /* TOP BAR */
        .top-bar {
            background: var(--dark);
            color: var(--light);
            height: 36px;
            position: fixed;
            top: 0; left: 0; width: 100%;
            z-index: 1100;
            font-size: 13px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .top-bar .container {
            max-width: 1200px; margin: 0 auto; padding: 0 15px;
            display: flex; justify-content: space-between; align-items: center; height: 100%;
        }

        /* USER MENU - ĐÃ XÓA TAM GIÁC ▼ HOÀN TOÀN */
        .user-menu {
            position: relative;
            display: inline-block;
        }
        .user-menu__toggle {
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: 0.3s;
        }
        .user-menu__toggle:hover {
            background: rgba(255,255,255,0.15);
        }
        .user-menu__dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 180px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: all 0.25s ease;
            z-index: 9999;
            overflow: hidden;
        }
        .user-menu:hover .user-menu__dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .user-menu__dropdown a {
            display: block;
            padding: 14px 18px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .user-menu__dropdown a:hover {
            background: #eff6ff;
            color: var(--primary);
            padding-left: 22px;
        }

        /* MAIN HEADER */
        .main-header {
            background: white;
            height: 80px;
            position: fixed;
            top: 36px; left: 0; width: 100%;
            z-index: 1090;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header-with-search {
            max-width: 1200px; margin: 0 auto; padding: 0 15px;
            display: flex; align-items: center; justify-content: space-between;
            height: 100%; gap: 20px;
        }

        /* LOGO */
        .logo-link { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            text-decoration: none;
            -webkit-tap-highlight-color: transparent !important;
        }
        .logo-icon { width: 56px; height: 56px; border-radius: 14px; overflow: hidden; }
        .logo-icon img { width: 100%; height: 100%; object-fit: cover; }
        .logo-text { font-size: 30px; font-weight: 900; color: var(--primary); letter-spacing: -1px; }
        .logo-link:hover .logo-text { color: var(--primary-dark); }

        /* SEARCH BAR */
        .search-bar {
            flex: 1; max-width: 580px; height: 48px;
            border: 2.5px solid var(--primary);
            border-radius: 50px;
            display: flex; overflow: hidden;
            box-shadow: 0 4px 12px rgba(37,99,235,0.15);
        }
        .search-input { flex: 1; padding: 0 20px; border: none; font-size: 15px; }
        .search-btn {
            width: 64px; background: var(--primary); color: white;
            border: none; font-size: 18px; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .search-btn:hover { background: var(--primary-dark); }

        /* GIỎ HÀNG - ĐÃ FIX HOÀN TOÀN VÙNG XANH */
        .cart-link {
            position: relative;
            color: var(--primary);
            font-size: 28px;
            text-decoration: none;
            display: flex;
            align-items: center;
            /* FIX TRIỆT ĐỂ VÙNG FOCUS */
            outline: none !important;
            border: 2px solid transparent !important;
            border-radius: 8px;
            padding: 8px;
            margin: -8px;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent !important;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
        }
        .cart-link:hover i { 
            transform: scale(1.1); 
            transition: 0.3s; 
        }
        .cart-link:focus,
        .cart-link:focus-visible,
        .cart-link:active {
            outline: none !important;
            box-shadow: none !important;
            border-color: transparent !important;
            background: transparent !important;
        }
        .cart-link::-moz-focus-inner {
            border: 0 !important;
            padding: 0 !important;
        }
        .cart-badge {
            position: absolute; top: -10px; right: -10px;
            background: #ef4444; color: white; font-size: 12px;
            width: 22px; height: 22px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; animation: pulse 2s infinite;
        }
        @keyframes pulse { 
            0%, 100% { transform: scale(1); } 
            50% { transform: scale(1.2); } 
        }

        /* CATEGORY NAV */
        .category-nav {
            background: var(--dark);
            height: 48px;
            position: fixed;
            top: 116px; left: 0; width: 100%;
            z-index: 1080;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
        }
        .category-nav .container {
            max-width: 1200px; margin: 0 auto; padding: 0 15px;
            display: flex; justify-content: center; gap: 40px; align-items: center;
        }
        .category-nav a,
        .products-menu__toggle {
            color: white;
            font-weight: 500;
            font-size: 15px;
            text-decoration: none;
            padding: 8px 0;
            position: relative;
            transition: 0.3s;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .category-nav a:hover,
        .products-menu__toggle:hover { color: #60a5fa; }
        .category-nav a::after,
        .products-menu__toggle::after {
            content: ''; position: absolute; bottom: 0; left: 50%;
            width: 0; height: 2px; background: var(--primary);
            transition: 0.3s; transform: translateX(-50%);
        }
        .category-nav a:hover::after,
        .products-menu__toggle:hover::after { width: 100%; }

        .products-menu { position: relative; }
        .products-menu__toggle { 
            display: flex; 
            align-items: center; 
            gap: 6px; 
            -webkit-tap-highlight-color: transparent;
        }

        /* DROPDOWN COLUMNS - PHẦN MỚI */
        .products-dropdown {
            position: absolute; 
            top: 100%; 
            left: 50%;
            transform: translateX(-50%);
            background: white; 
            min-width: 200px;
            border-radius: 10px; 
            box-shadow: var(--shadow);
            opacity: 0; 
            visibility: hidden;
            transition: all 0.3s ease; 
            margin-top: 8px; 
            z-index: 9999;
            padding: 0;
        }
        .products-menu:hover .products-dropdown {
            opacity: 1; 
            visibility: visible;
            transform: translateX(-50%) translateY(4px);
        }

        .dropdown-columns {
            display: flex;
            gap: 0;
        }

        .dropdown-column {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }

        .dropdown-column:first-child {
            border-right: 1px solid #e5e7eb;
        }

        .products-dropdown a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            -webkit-tap-highlight-color: transparent;
            border-bottom: 1px solid #f3f4f6;
        }

        .products-dropdown a:last-child {
            border-bottom: none;
        }

        .products-dropdown a:hover {
            background: #eff6ff;
            color: var(--primary);
            padding-left: 26px;
        }

        /* Khi có 2 cột, mở rộng dropdown */
        .dropdown-columns .dropdown-column:last-child {
            min-width: 180px;
        }

        /* FIX CHO MOBILE */
        @media (max-width: 768px) {
            .header-spacing { padding-top: 160px; }
            .search-bar { order: 3; width: 100%; margin-top: 12px; }
            .category-nav .container { gap: 20px; flex-wrap: wrap; justify-content: center; }
            .category-nav { height: auto; padding: 8px 0; }
            
            /* Fix thêm cho mobile */
            .cart-link {
                padding: 6px;
                margin: -6px;
            }

            /* Mobile dropdown adjustment */
            .dropdown-columns {
                flex-direction: column;
            }
            
            .dropdown-column:first-child {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
        }

        /* FIX CUỐI CÙNG CHO TẤT CẢ TRÌNH DUYỆT */
        button:focus, 
        button:focus-visible,
        input:focus,
        input:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="container">
        <div>ShopNets - Mua sắm thông minh</div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <div class="user-menu__toggle" tabindex="0">
                        <i class="fas fa-user-circle" style="font-size: 28px; color: white;"></i>
                        <span style="margin-left: 8px; font-weight: 500; max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        </span>
                    </div>
                    <div class="user-menu__dropdown">
                        <a href="<?php echo $user_url; ?>pages/user/profile.php">Hồ sơ</a>
                        <a href="<?php echo $user_url; ?>pages/user/orders.php">Đơn hàng của tôi</a>
                        <a href="<?php echo $user_url; ?>auth/logout.php">Đăng xuất</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $user_url; ?>auth/login.php" style="color:white; margin-right:15px; text-decoration: none;">Đăng nhập</a>
                <a href="<?php echo $user_url; ?>auth/register.php" style="color:white; text-decoration: none;">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MAIN HEADER -->
<header class="main-header">
    <div class="header-with-search">
        <a href="<?php echo $home_url; ?>" class="logo-link">
            <div class="logo-icon">
                <img src="<?php echo $home_url; ?>user/assets/images/icons_logo/apple-icon.png" alt="ShopNets">
            </div>
            <span class="logo-text">ShopNets</span>
        </a>

        <div class="search-bar">
            <form action="<?php echo $user_url; ?>pages/main/products.php" method="GET" style="width:100%; display:flex;">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Tìm kiếm sản phẩm..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    autocomplete="off"
                >
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <a href="<?php echo $user_url; ?>pages/main/cart.php" class="cart-link" title="Giỏ hàng" tabindex="0">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<!-- CATEGORY NAV -->
<nav class="category-nav">
    <div class="container">
        <div class="products-menu">
            <div class="products-menu__toggle">
                <i class="fas fa-bars"></i> Sản phẩm
            </div>
            <div class="products-dropdown">
                <div class="dropdown-columns">
                    <div class="dropdown-column">
                        <?php 
                        $display_categories = array_slice($categories, 0, 6);
                        foreach ($display_categories as $cat): ?>
                            <a href="<?php echo $user_url; ?>pages/main/products.php?category=<?php echo urlencode($cat['name']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($categories) > 6): ?>
                    <div class="dropdown-column">
                        <?php 
                        $remaining_categories = array_slice($categories, 6);
                        foreach ($remaining_categories as $cat): ?>
                            <a href="<?php echo $user_url; ?>pages/main/products.php?category=<?php echo urlencode($cat['name']); ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <a href="<?php echo $user_url; ?>pages/info/about.php">Giới thiệu</a>
        <a href="<?php echo $user_url; ?>pages/info/contact.php">Liên hệ</a>
    </div>
</nav>

<div class="header-spacing"></div>

</body>
</html>