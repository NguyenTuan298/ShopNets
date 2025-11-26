<?php
// HEADER COMPONENT - Không có HTML structure wrapper
// File này chỉ chứa navigation markup, không có <!DOCTYPE>, <html>, <head>, <body>

$home_url = (function_exists('isLocalhost') && isLocalhost()) 
    ? 'http://localhost/shopnets/' 
    : 'https://shopnets.infinityfree.me/';

$user_url = $home_url . 'user/';

$cart_count = 0;
if (function_exists('getCartCount')) {
    try {
        $cart_count = getCartCount();
    } catch (Exception $e) {
        $cart_count = 0;
    }
}
?>

<!-- HEADER STYLES - Inline trong header component -->
<style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-color: #1e293b;
            --text-light: #f8fafc;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-spacing { padding-top: 150px; } /* Dành chỗ cho fixed header */

        /* === TOP BAR === */
        .top-bar {
            background: var(--dark-color);
            color: var(--text-light);
            height: 36px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1100;
            box-shadow: var(--shadow);
            font-size: 13px;
        }
        .top-bar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }
        .top-bar__left, .top-bar__right { display: flex; align-items: center; }
        .top-bar__right { gap: 18px; }
        .top-bar__item {
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            position: relative;
            white-space: nowrap;
        }
        .top-bar__item:hover { color: var(--primary-color); }

        /* === MAIN HEADER === */
        .main-header {
            background: white;
            height: 80px;
            position: fixed;
            top: 36px;
            left: 0;
            width: 100%;
            z-index: 1090;
            box-shadow: var(--shadow);
        }
        .header-with-search {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            gap: 20px;
        }
        .logo-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .logo-link:hover .logo-icon {
            transform: scale(1.05);
        }
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            white-space: nowrap;
        }
        .logo-link:hover .logo-text {
            color: var(--primary-dark);
        }
        
        .search-bar {
            flex: 1;
            max-width: 600px;
            height: 44px;
            background: white;
            border: 2px solid var(--primary-color);
            border-radius: 25px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(37,99,235,0.1);
        }
        .search-input {
            flex: 1;
            padding: 0 16px;
            font-size: 14px;
            border: none;
            outline: none;
        }
        .search-btn {
            width: 60px;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }
        .search-btn:hover { background: var(--primary-dark); }
        .cart-link {
            color: var(--primary-color);
            font-size: 28px;
            text-decoration: none;
            position: relative;
            display: block;
        }
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: white;
            font-size: 11px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* === CATEGORY NAV === */
        .category-nav {
            background: var(--dark-color);
            padding: 12px 0;
            position: fixed;
            top: 116px;
            left: 0;
            width: 100%;
            z-index: 1080;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .category-nav .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .category-nav a {
            color: var(--text-light);
            font-weight: 500;
            padding: 8px 16px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            white-space: nowrap;
            border-radius: 4px;
        }
        .category-nav a:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .header-spacing { padding-top: 170px; }
            .top-bar__right { gap: 8px; font-size: 11px; }
            .header-with-search {
                flex-wrap: wrap;
                height: auto;
                padding: 12px 15px;
            }
            .logo-icon { width: 38px; height: 38px; }
            .logo-text { font-size: 20px; }
            .search-bar { 
                order: 3; 
                width: 100%; 
                max-width: none; 
                height: 40px; 
            }
            .cart-link { font-size: 24px; }
            .category-nav {
                top: 136px;
                padding: 8px 0;
            }
            .category-nav .container { gap: 10px; }
            .category-nav a {
                padding: 6px 12px;
                font-size: 13px;
            }
        }

    </style>

<!-- TOP BAR -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar__left">
            📱 ShopNets - Mua sắm tiện lợi
        </div>
        <div class="top-bar__right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="top-bar__item">👤 <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    <a href="<?php echo $user_url; ?>pages/user/orders.php" class="top-bar__item">📦 Đơn hàng của tôi</a>
                <a href="<?php echo $user_url; ?>auth/logout.php" class="top-bar__item">Đăng xuất</a>
            <?php else: ?>
                <a href="<?php echo $user_url; ?>auth/login.php" class="top-bar__item">Đăng nhập</a>
                <a href="<?php echo $user_url; ?>auth/register.php" class="top-bar__item">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MAIN HEADER -->
<header class="main-header">
    <div class="header-with-search">
        <a href="<?php echo $home_url; ?>" class="logo-link">
            <div class="logo-icon">
                <img src="<?php echo $home_url; ?>user/assets/images/icons_logo/apple-icon.png" alt="ShopNets" onerror="this.parentElement.innerHTML='🛒';">
            </div>
            <span class="logo-text">ShopNets</span>
        </a>
        
        <div class="search-bar">
            <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
            <button class="search-btn">🔍</button>
        </div>
        
        <a href="<?php echo $user_url; ?>pages/main/cart.php" class="cart-link">
            🛒
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<!-- CATEGORY NAV -->
<nav class="category-nav">
    <div class="container">
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=phone">📱 Điện thoại</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=laptop">💻 Laptop</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=tablet">📱 Tablet</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=accessories">🎧 Phụ kiện</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=watch">⌚ Đồng hồ</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?new=1">🆕 Mới</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?discount=1">🔥 Giảm giá</a>
    </div>
</nav>
<!-- END HEADER COMPONENT -->
            <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
            <button class="search-btn">🔍</button>
        </div>
        
        <a href="<?php echo $user_url; ?>pages/main/cart.php" class="cart-link">
            🛒
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</header>

<!-- CATEGORY NAV -->
<nav class="category-nav">
    <div class="container">
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=phone">📱 Điện thoại</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=laptop">💻 Laptop</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=tablet">📱 Tablet</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=accessories">🎧 Phụ kiện</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?category=watch">⌚ Đồng hồ</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?new=1">🆕 Mới</a>
        <a href="<?php echo $user_url; ?>pages/main/products.php?discount=1">🔥 Giảm giá</a>
    </div>
</nav>
<!-- END HEADER COMPONENT -->