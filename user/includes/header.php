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
            height: 80px;
            height: 100px;
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
        }
        .logo-img { width: 180px; height: auto; border-radius: 4px; }
        
        .logo-container {
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
        .logo-icon .logo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        .logo-icon .logo-img:not([src]),
        .logo-icon .logo-img[src=""] {
            display: none;
        }
        .logo-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .logo-main {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }
        .logo-sub {
            font-size: 1.2rem;
            color: #666;
            font-weight: 400;
            margin-top: -2px;
        }
        .logo-link {
            text-decoration: none;
        }
        .logo-link:hover .logo-main {
            color: var(--primary-dark);
        }
        .logo-link:hover .logo-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .logo-link:hover .logo-img {
            transform: scale(1.1);
        }
        
        .logo-svg {
            width: 100%;
            height: 100%;
            display: none; /* Ẩn ban đầu, chỉ hiện khi image lỗi */
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
            margin: 0 30px;
            box-shadow: 0 2px 4px rgba(37,99,235,0.1);
        }
        .search-input {
            flex: 1;
            padding: 0 16px;
            font-size: 14px;
            font-size: 1.4rem;
            border: none;
            outline: none;
        }
        .search-btn {
            width: 60px;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 18px;
            font-size: 1.5rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .search-btn:hover { background: var(--primary-dark); }
        .cart-link {
            color: var(--primary-color);
            font-size: 28px;
            font-size: 2.8rem;
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
            font-size: 1.1rem;
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
            position: relative;
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
            justify-content: space-between;
            align-items: center;
        }
        .category-toggler {
            display: none;
            background: transparent;
            border: none;
            color: var(--text-light);
            font-size: 1.8rem;
            padding: 8px;
            cursor: pointer;
        }

        .category-collapse {
            display: flex !important;
            width: 100%;
            justify-content: center;
        }
        .category-nav .navbar-nav {
            display: flex !important;
            flex-direction: row !important;
            justify-content: center;
            align-items: center;
            gap: 20px;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
        }
        .category-nav .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            padding: 8px 16px;
            font-size: 1.4rem;
            text-decoration: none;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        .category-nav .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-1px);
        }

        .category-nav .dropdown-menu {
            background: var(--dark-color);
            border: none;
            border-radius: 8px;
            box-shadow: var(--shadow);
            min-width: 200px;
            margin-top: 8px;
        }
        .category-nav .dropdown-item {
            color: var(--text-light);
            padding: 10px 16px;
            font-size: 1.35rem;
        }
        .category-nav .dropdown-item:hover {
            background: var(--primary-color);
            color: white;
        }

        /* === RESPONSIVE === */
        @media (max-width: 992px) {
            .top-bar__right { gap: 12px; font-size: 1.2rem; }
            .search-bar { margin: 0 15px; max-width: 500px; }
            .category-nav .navbar-nav { gap: 12px; }
        }

        @media (max-width: 768px) {
            body { padding-top: 136px; }
            .top-bar .container { padding: 0 10px; }
            .top-bar__right { gap: 8px; font-size: 1.15rem; }
            .header-with-search {
                flex-direction: column;
                gap: 12px;
                padding: 12px 15px;
            }
            .logo-main { font-size: 2rem; }
            .logo-sub { font-size: 1.1rem; }
            .logo-icon { width: 40px; height: 40px; }
            .logo-container { gap: 8px; }
            .search-bar { order: 3; margin: 0; height: 40px; max-width: none; }
            .cart-wrap { order: 2; width: 60px; }
            .cart-link { font-size: 2.4rem; }

            .category-toggler { display: block !important; }
            .category-collapse {
                display: none !important;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--dark-color);
                padding: 15px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                flex-direction: column;
            }
            .category-collapse.show { display: flex !important; }
            .category-nav .navbar-nav {
                flex-direction: column !important;
                align-items: flex-start;
                gap: 0;
            }
            .category-nav .nav-link {
                padding: 12px 0;
                font-size: 1.5rem;
                width: 100%;
            }
            .category-nav .dropdown-menu {
                position: static !important;
                float: none;
                box-shadow: none;
                background: transparent;
                border: none;
                margin-top: 0;
            }
            .category-nav .dropdown-item {
                color: #ccc !important;
                padding: 8px 20px;
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar__left">
                <a href="#" class="top-bar__item">
                    <span>Tải ứng dụng</span>
                    <div class="qr-dropdown">
                        <img src="<?php echo BASE_URL; ?>assets/images/header/qr.png" alt="QR Code" class="qr-code">
                        <div class="app-stores">
                            <a href="https://apps.apple.com" target="_blank">
                                <img src="<?php echo BASE_URL; ?>assets/images/header/appstore.png" alt="App Store" class="app-store-img">
                            </a>
                            <a href="https://play.google.com" target="_blank">
                                <img src="<?php echo BASE_URL; ?>assets/images/header/google-play.png" alt="Google Play" class="app-store-img">
                            </a>
                        </div>
                    </div>
                </a>
            </div>

            <div class="top-bar__right">
                <a href="#" class="top-bar__item top-bar__item-separate">
                    <i class="bi bi-bell-fill"></i> Thông báo
                    <div class="notify-dropdown">
                        <div class="notify-header">Thông Báo Mới</div>
                        <div class="notify-empty">Chưa có thông báo mới</div>
                    </div>
                </a>
                <a href="<?php echo BASE_URL; ?>pages/info/help.php" class="top-bar__item top-bar__item-separate">
                    <i class="bi bi-headset"></i> Hỗ trợ
                </a>
                <div class="top-bar__item top-bar__item-separate">
                    <i class="bi bi-globe2"></i> VN
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>pages/user/profile.php" class="top-bar__item top-bar__item-separate">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>auth/logout.php" class="top-bar__item">Đăng xuất</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>auth/login.php" class="top-bar__item top-bar__item-separate">Đăng nhập</a>
                    <a href="<?php echo BASE_URL; ?>auth/register.php" class="top-bar__item">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN HEADER -->
    <header class="main-header">
        <div class="header-with-search">
            <a href="<?php echo getHomePath(); ?>" class="logo-link" onclick="handleLogoClick(event)">
                <div class="logo-container">
                    <div class="logo-icon">
                        <?php 
                        $localLogoPath = __DIR__ . '/../../admin/assets/images/icons/icons_logo/apple-icon.png';
                        if (file_exists($localLogoPath)): ?>
                            <img src="<?php echo getLogoPath(); ?>" alt="ShopNets Logo" class="logo-img">
                        <?php else: ?>
                            <!-- SVG Logo Fallback -->
                            <svg class="logo-svg" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="50" height="50" fill="#2563eb" rx="12"/>
                                <path d="M15 20h20v2H15v-2zm0 4h20v2H15v-2zm0 4h15v2H15v-2z" fill="white"/>
                                <circle cx="37" cy="15" r="3" fill="white"/>
                                <path d="M12 35h26v2H12v-2z" fill="white"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="logo-text">
                        <span class="logo-main">ShopNets</span>
                        <span class="logo-sub">Mua sắm tiện lợi</span>
                    </div>
                </div>
            </a>

            <div class="search-bar">
                <form action="<?php echo BASE_URL; ?>pages/main/products.php" method="GET" class="d-flex w-100 h-100">
                    <input 
                        type="text" 
                        name="q" 
                        class="search-input" 
                        placeholder="Tìm kiếm sản phẩm..." 
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                        required
                        autocomplete="off"
                    >
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <div class="cart-wrap">
                <a href="<?php echo BASE_URL; ?>pages/main/cart.php" class="cart-link">
                    <i class="bi bi-cart3"></i>
                    <?php if (getCartCount() > 0): ?>
                        <span class="cart-badge"><?php echo getCartCount(); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <!-- CATEGORY NAV -->
    <nav class="category-nav">
        <div class="container">
            <button class="category-toggler" data-bs-toggle="collapse" data-bs-target="#categoryNavCollapse">
                <i class="bi bi-list"></i>
            </button>

            <div class="collapse navbar-collapse category-collapse" id="categoryNavCollapse">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Danh Mục</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=phone">Điện Thoại</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=laptop">Laptop</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=tablet">Máy Tính Bảng</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=accessories">Phụ Kiện</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=watch">Đồng Hồ</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>pages/main/products.php?category=earphones">Tai Nghe</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>pages/main/products.php?new=1">Sản Phẩm Mới</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>pages/main/products.php?discount=1">Khuyến Mãi</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>pages/info/promotions.php">Ưu Đãi</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>pages/info/contact.php">Liên Hệ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS HỖ TRỢ MOBILE (TÙY CHỌN) -->
    <script>
        // Hỗ trợ tap trên mobile
        document.querySelectorAll('.top-bar__item').forEach(item => {
            const dropdown = item.querySelector('.qr-dropdown, .notify-dropdown');
            if (!dropdown) return;

            let timeout;

            item.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
                dropdown.style.display = 'block';
            });

            item.addEventListener('mouseleave', () => {
                timeout = setTimeout(() => {
                    dropdown.style.display = 'none';
                }, 300);
            });

            // Mobile: tap để mở
            item.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    const isOpen = dropdown.style.display === 'block';
                    document.querySelectorAll('.qr-dropdown, .notify-dropdown').forEach(d => d.style.display = 'none');
                    dropdown.style.display = isOpen ? 'none' : 'block';
                }
            });
        });
    </script>
    
    <script>
    function handleLogoClick(event) {
        // Kiểm tra xem đang ở trang chủ hay không
        const currentPath = window.location.pathname;
        
        // Các pattern trang chủ (lowercase cho localhost)
        const homePatterns = [
            '/shopnets/',
            '/shopnets/index.php',
            '/shopnets/user/',
            '/shopnets/user/index.php'
        ];
        
        // Kiểm tra nếu đang ở trang chủ
        const isHomePage = homePatterns.some(pattern => 
            currentPath === pattern || 
            currentPath.endsWith(pattern)
        ) || (currentPath.endsWith('/shopnets/') || currentPath.endsWith('/shopnets/index.php'));
        
        console.log('Current path:', currentPath, 'Is home:', isHomePage);
        
        if (isHomePage) {
            // Nếu đang ở trang chủ, reload
            event.preventDefault();
            location.reload();
        }
        // Nếu không ở trang chủ, để link bình thường (về trang chủ)
    }
    
    // Handle logo loading error
    document.addEventListener('DOMContentLoaded', function() {
        const logoImg = document.querySelector('.logo-img');
        if (logoImg) {
            logoImg.onerror = function() {
                // Ẩn image và hiển thị SVG fallback
                this.style.display = 'none';
                const svgLogo = this.parentElement.querySelector('.logo-svg');
                if (svgLogo) {
                    svgLogo.style.display = 'block';
                }
            };
        }
    });
    </script>
</body>
</html>
