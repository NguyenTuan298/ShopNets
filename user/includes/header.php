<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php';
require_once 'functions.php';
require_once 'config.php';

// Tính đường dẫn trang chủ dựa trên vị trí hiện tại
function getHomePath() {
    // Luôn return về trang chủ chính của ShopNets
    return 'http://localhost/shopnets/index.php';
}

// Tính đường dẫn logo chính xác
function getLogoPath() {
    // Luôn return đường dẫn absolute từ localhost
    return 'http://localhost/shopnets/admin/assets/images/icons/icons_logo/apple-icon.png';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechShop - Dịch vụ Công nghệ Cao cấp</title>

    <!-- Bootstrap 5 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --dark-color: #1e293b;
            --text-light: #f8fafc;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Roboto', sans-serif; }
        
        body { 
            background: #f1f5f9; 
            color: #1e293b; 
            padding-top: 136px; /* 36px (top-bar) + 100px (main-header) */
        }

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
            font-size: 1.3rem;
            overflow: visible !important; /* QUAN TRỌNG: Không cắt dropdown */
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
        .top-bar__left { gap: 0; }
        .top-bar__right { gap: 18px; }
        .top-bar__item {
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            position: relative;
            white-space: nowrap;
            z-index: 1400 !important; /* CAO HƠN main-header */
        }
        .top-bar__item:hover { color: var(--primary-color); }
        .top-bar__item-separate::after {
            content: "";
            position: absolute;
            right: -10px;
            height: 14px;
            width: 1px;
            background: rgba(255,255,255,0.3);
        }

        /* === QR DROPDOWN - ĐÃ SỬA HOÀN HẢO === */
        .qr-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 8px;
            background: white;
            width: 195px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid #e2e8f0;
            padding: 12px;
            display: none;
            z-index: 1500 !important;
            animation: fadeInDrop 0.3s ease;
        }
        .top-bar__item:hover > .qr-dropdown {
            display: block !important;
        }

        .qr-code {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .app-stores {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .app-store-img {
            width: 48%;
            height: auto;
            border-radius: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .app-store-img:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        @keyframes fadeInDrop {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* === NOTIFY DROPDOWN === */
        .notify-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            width: 380px;
            display: none;
            z-index: 1500 !important;
            animation: fadeInDrop 0.3s ease;
        }
        .top-bar__item:hover > .notify-dropdown {
            display: block !important;
        }
        .notify-header {
            background: var(--primary-color);
            color: white;
            padding: 12px 16px;
            font-weight: 500;
            border-radius: 8px 8px 0 0;
        }
        .notify-empty {
            padding: 20px;
            text-align: center;
            color: #777;
            font-size: 1.4rem;
        }

        /* === MAIN HEADER === */
        .main-header {
            background: white;
            height: 100px;
            position: fixed;
            top: 36px;
            left: 0;
            width: 100%;
            z-index: 1090;
            box-shadow: var(--shadow);
            overflow: visible !important;
        }
        .header-with-search {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }
        .logo-img { width: 180px; height: auto; border-radius: 4px; }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            background: white;
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
            font-size: 1.4rem;
            border: none;
            outline: none;
        }
        .search-btn {
            width: 60px;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .search-btn:hover { background: var(--primary-dark); }
        .cart-link {
            color: var(--primary-color);
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
            position: relative;
            z-index: 1080;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .category-nav .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
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