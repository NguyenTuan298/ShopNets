<?php
// PRODUCTION - TẮT ERROR DISPLAY
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
require_once 'config.php';  
require_once 'user/includes/database.php';
require_once 'user/includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Đặt thời gian kết thúc Flash Sale là 7 ngày nữa
$flash_sale_end = strtotime('+7 days');
$new_products = getLatestProductsByBrand($db, 2);
$featured_products = getDiscountProductsByBrand($db, 2);
$categories = getCategories($db);
$brands = getBrands($db);
$posts = getLatestPosts($db);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ShopNets - Mua sắm công nghệ cao cấp</title>

  <!-- Bootstrap + Icons - DÙNG CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { font-size: 62.5%; line-height: 1.6rem; font-family: 'Inter', 'Roboto', sans-serif; }
  body { background: #f1f5f9; color: #1e293b; }

  :root {
    --primary: #2563eb; --primary-dark: #1d4ed8; --dark: #1e293b; --light: #f8fafc;
    --gray: #94a3b8; --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
    --border: #e2e8f0; --shadow-sm: 0 1px 3px rgba(0,0,0,0.1); --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15); --radius-sm: 8px; --radius: 12px; --radius-lg: 16px;
    --transition: all 0.3s ease;
  }

  /* GRID */
  .grid { width: 1200px; max-width: 100%; margin: 0 auto; padding: 0 15px; }
  .grid__row { display: flex; flex-wrap: wrap; margin: -10px; }
  .grid__col { padding: 10px; }
  .col-3 { flex: 0 0 25%; max-width: 25%; }
  .col-4 { flex: 0 0 33.33%; max-width: 33.33%; }
  .col-6 { flex: 0 0 50%; max-width: 50%; }
  .col-12 { flex: 0 0 100%; max-width: 100%; }

  /* BANNER */
  .banner { display: flex; gap: 16px; margin-bottom: 24px; border-radius: var(--radius); overflow: hidden; height: 280px; }
  .banner__left { flex: 2; position: relative; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); }
  .carousel__slide { display: none; width: 100%; height: 100%; }
  .carousel__slide.active { display: block; animation: fadeIn 0.6s ease; }
  .carousel__img { width: 100%; height: 100%; object-fit: cover; }
  .carousel__btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; width: 44px; height: 44px; border-radius: 50%; font-size: 1.8rem; cursor: pointer; z-index: 10; transition: var(--transition); }
  .carousel__btn:hover { background: var(--primary); transform: translateY(-50%) scale(1.1); }
  .carousel__prev { left: 16px; } .carousel__next { right: 16px; }
  .carousel__dots { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10; }
  .carousel__dot { width: 10px; height: 10px; background: rgba(255,255,255,0.5); border-radius: 50%; cursor: pointer; transition: var(--transition); }
  .carousel__dot.active, .carousel__dot:hover { background: white; width: 12px; height: 12px; }
  .banner__right { flex: 1; display: flex; flex-direction: column; gap: 16px; }
  .banner__static { height: calc(50% - 8px); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); transition: var(--transition); }
  .banner__static:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
  .banner__static img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
  .banner__static:hover img { transform: scale(1.05); }

  /* DANH MỤC */
  .category { background: white; padding: 24px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 32px; }
  .category__title { font-size: 1.8rem; font-weight: 600; text-align: center; margin-bottom: 20px; color: var(--dark); position: relative; }
  .category__title::after { content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 60px; height: 4px; background: var(--primary); border-radius: 2px; }
  .category__list { display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; }
  .category__item { width: calc(10% - 16px); text-align: center; text-decoration: none; color: var(--dark); font-weight: 500; font-size: 1.35rem; padding: 12px 8px; border: 1px solid var(--border); border-radius: var(--radius-sm); transition: var(--transition); background: #fff; }
  .category__item:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: var(--primary); color: var(--primary); }
  .category__img { width: 100%; height: 60px; object-fit: contain; margin-bottom: 8px; border-radius: 6px; transition: transform 0.3s ease; }
  .category__item:hover .category__img { transform: scale(1.1); }

  /* PROMO */
  .promo-banner { background: white; padding: 16px 0; overflow: hidden; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 32px; }
  .marquee-content { display: inline-block; white-space: nowrap; animation: marquee 18s linear infinite; }
  .marquee-content:hover { animation-play-state: paused; }
  .promo-card { display: inline-block; padding: 10px 24px; margin: 0 12px; border-radius: 50px; font-weight: 600; font-size: 1.35rem; box-shadow: var(--shadow-sm); transition: var(--transition); }
  .promo-card:hover { transform: scale(1.05); }

  @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

  /* SECTION */
  .section { background: white; padding: 28px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 32px; }
  .section__title { font-size: 1.9rem; font-weight: 600; text-align: center; margin-bottom: 24px; color: var(--dark); position: relative; }
  .section__title::after { content: ''; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 50px; height: 4px; background: var(--primary); border-radius: 2px; }

  /* PRODUCT CARD */
  .product-card { background: #fff; border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); transition: var(--transition); height: 100%; border: 1px solid var(--border); position: relative; }
  .product-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); border-color: var(--primary); }
  .product-image { position: relative; height: 200px; overflow: hidden; }
  .product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
  .product-card:hover .product-image img { transform: scale(1.08); }

  .product-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: var(--transition); z-index: 2; }
  .product-card:hover .product-overlay { opacity: 1; visibility: visible; }
  .btn-view-detail { background: white; color: var(--primary); padding: 10px 20px; border-radius: 50px; font-weight: 600; font-size: 1.4rem; text-decoration: none; box-shadow: var(--shadow); transition: var(--transition); }
  .btn-view-detail:hover { background: var(--primary); color: white; transform: scale(1.05); }

  .product-badge { position: absolute; top: 12px; padding: 6px 12px; border-radius: 50px; font-size: 1.2rem; font-weight: 600; color: white; z-index: 3; box-shadow: var(--shadow-sm); }
  .new-badge { background: var(--success); left: 12px; }
  .discount-badge { background: var(--danger); right: 12px; }

  .product-info { padding: 16px; }
  .product-title { font-size: 1.4rem; font-weight: 500; color: var(--dark); margin-bottom: 8px; height: 44px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  .product-price { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
  .old-price { color: var(--gray); text-decoration: line-through; font-size: 1.3rem; }
  .current-price { font-size: 1.6rem; font-weight: 700; color: var(--danger); }

  .product-actions { display: flex; gap: 8px; }
  .btn { flex: 1; border-radius: 8px; padding: 10px; font-size: 1.35rem; font-weight: 500; transition: var(--transition); text-align: center; }
  .btn-primary { background: var(--primary); border: none; color: white; }
  .btn-primary:hover { background: var(--primary-dark); }
  .btn-outline-primary { border: 1px solid var(--primary); color: var(--primary); }
  .btn-outline-primary:hover { background: var(--primary); color: white; }

  /* FLASH SALE */
  .flash-sale-card { border: 2px solid var(--danger) !important; }
  .flash-sale-card .current-price { color: var(--danger); font-weight: 800; }
  .flash-sale-card .btn-danger { background: var(--danger); border-color: var(--danger); }
  .flash-sale-card .btn-danger:hover { background: #dc2626; }

  .flash-sale-timer {
      background: linear-gradient(135deg, #dc2626, #b91c1c);
      color: white;
      padding: 8px 16px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.4rem;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
      animation: pulse 2s infinite;
      white-space: nowrap;
  }
  @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.03); } }

  /* Back to top button */
  .back-to-top {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      background: var(--primary);
      border: none;
      border-radius: var(--radius);
      box-shadow: var(--shadow-lg);
      transition: var(--transition);
      color: white;
      font-size: 1.6rem;
      cursor: pointer;
  }

  .back-to-top:hover {
      background: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
  }

  /* RESPONSIVE */
  @media (max-width: 992px) { .col-3, .col-4 { flex: 0 0 50%; max-width: 50%; } .category__item { width: calc(20% - 16px); font-size: 1.3rem; } .banner { height: 220px; } }
  @media (max-width: 768px) { body { padding-top: 180px; } .col-3, .col-4, .col-6 { flex: 0 0 100%; max-width: 100%; } .category__item { width: calc(33.33% - 16px); } .banner { flex-direction: column; height: auto; } .banner__right { flex-direction: row; height: 120px; } .banner__static { height: 100%; } .product-image { height: 180px; } .back-to-top { bottom: 20px; right: 20px; width: 45px; height: 45px; font-size: 1.4rem; } }
  @media (max-width: 576px) { .category__item { width: calc(50% - 16px); } .promo-card { font-size: 1.25rem; padding: 8px 16px; } .back-to-top { bottom: 15px; right: 15px; width: 40px; height: 40px; font-size: 1.2rem; } }
</style>

<body>
  <div class="app header-spacing">
    <?php include 'user/includes/header.php'; ?>

    <!-- Modal Đăng nhập -->
    <div id="authModal"></div>

    <div class="grid">
      <!-- BANNER -->
      <div class="banner">
        <div class="banner__left">
          <div class="banner__carousel" id="bannerCarousel">
            <div class="carousel__slide active">
              <img src="user/assets/images/banners/banner1.webp" alt="Đồng Hồ Thông Minh" class="carousel__img">
            </div>
            <div class="carousel__slide">
              <img src="user/assets/images/banners/banner2.webp" alt="Iphone 17" class="carousel__img">
            </div>
            <div class="carousel__slide">
              <img src="user/assets/images/banners/banner3.webp" alt="Điện thoại mới" class="carousel__img">
            </div>
            <button class="carousel__btn carousel__prev" onclick="changeSlide(-1)">❮</button>
            <button class="carousel__btn carousel__next" onclick="changeSlide(1)">❯</button>
            <div class="carousel__dots">
              <span class="carousel__dot active" onclick="currentSlide(0)"></span>
              <span class="carousel__dot" onclick="currentSlide(1)"></span>
              <span class="carousel__dot" onclick="currentSlide(2)"></span>
            </div>
          </div>
        </div>
        <div class="banner__right">
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/uu_dai.png" alt="Ưu đãi"></a>
          </div>
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/giam_gia.png" alt="Miễn phí ship"></a>
          </div>
        </div>
      </div>

      <!-- DANH MỤC -->
      <div class="category">
        <h1 class="category__title">Danh mục</h1>
        <div class="category__list">
          <?php 
          $cats = [
            ['phone', 'dienthoai.jpg', 'Điện thoại'],
            ['laptop', 'laptop.jpg', 'Laptop'],
            ['tablet', 'tablet.jpg', 'Tablet'],
            ['accessories', 'phu-kien.jpg', 'Phụ kiện'],
            ['watch', 'dong-ho.jpg', 'Đồng hồ'],
            ['pc', 'pc.jpg', 'PC'],
          ];
          foreach ($cats as $c): ?>
            <a href="user/pages/main/products.php?category=<?= $c[0] ?>" class="category__item">
              <img src="user/assets/images/main/<?= $c[1] ?>" alt="<?= $c[2] ?>" class="category__img">
              <?= $c[2] ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- PROMO -->
      <div class="promo-banner">
        <div class="marquee-container">
          <div class="marquee-content">
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
          </div>
        </div>
      </div>

      <!-- SẢN PHẨM MỚI -->
      <section class="section">
        <h2 class="section__title">Sản Phẩm Mới</h2>
        <div class="grid__row">
              <span class="carousel__dot" onclick="currentSlide(1)"></span>
              <span class="carousel__dot" onclick="currentSlide(2)"></span>
            </div>
          </div>
        </div>
        <div class="banner__right">
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/uu_dai.png" alt="Ưu đãi"></a>
          </div>
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/giam_gia.png" alt="Miễn phí ship"></a>
          </div>
        </div>
      </div>

      <!-- DANH MỤC -->
      <div class="category">
        <h1 class="category__title">Danh mục</h1>
        <div class="category__list">
          <?php 
          $cats = [
            ['phone', 'dienthoai.jpg', 'Điện thoại'],
            ['laptop', 'laptop.jpg', 'Laptop'],
            ['tablet', 'tablet.jpg', 'Tablet'],
            ['accessories', 'phu-kien.jpg', 'Phụ kiện'],
            ['watch', 'dong-ho.jpg', 'Đồng hồ'],
            ['pc', 'pc.jpg', 'PC'],
          ];
          foreach ($cats as $c): ?>
            <a href="user/pages/main/products.php?category=<?= $c[0] ?>" class="category__item">
              <img src="user/assets/images/main/<?= $c[1] ?>" alt="<?= $c[2] ?>" class="category__img">
              <?= $c[2] ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- PROMO -->
      <div class="promo-banner">
        <div class="marquee-container">
            <a href="#"><img src="user/assets/images/banners/giam_gia.png" alt="Miễn phí ship"></a>
          </div>
        </div>
      </div>

      <!-- DANH MỤC -->
      <div class="category">
        <h1 class="category__title">Danh mục</h1>
        <div class="category__list">
          <?php 
          $cats = [
            ['phone', 'dienthoai.jpg', 'Điện thoại'],
            ['laptop', 'laptop.jpg', 'Laptop'],
            ['tablet', 'tablet.jpg', 'Tablet'],
            ['accessories', 'phu-kien.jpg', 'Phụ kiện'],
            ['watch', 'dong-ho.jpg', 'Đồng hồ'],
            ['pc', 'pc.jpg', 'PC'],
          ];
          foreach ($cats as $c): ?>
            <a href="user/pages/main/products.php?category=<?= $c[0] ?>" class="category__item">
              <img src="user/assets/images/main/<?= $c[1] ?>" alt="<?= $c[2] ?>" class="category__img">
              <?= $c[2] ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- PROMO -->
      <div class="promo-banner">
        <div class="marquee-container">
          <div class="marquee-content">
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
          </div>
        </div>
      </div>

      <!-- SẢN PHẨM MỚI -->
      <section class="section">
        <h2 class="section__title">Sản Phẩm Mới</h2>
        <div class="grid__row">
              <span class="carousel__dot active" onclick="currentSlide(0)"></span>
              <span class="carousel__dot" onclick="currentSlide(1)"></span>
              <span class="carousel__dot" onclick="currentSlide(2)"></span>
            </div>
          </div>
        </div>
        <div class="banner__right">
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/uu_dai.png" alt="Ưu đãi"></a>
          </div>
          <div class="banner__static">
            <a href="#"><img src="user/assets/images/banners/giam_gia.png" alt="Miễn phí ship"></a>
          </div>
        </div>
      </div>

      <!-- DANH MỤC -->
      <div class="category">
        <h1 class="category__title">Danh mục</h1>
        <div class="category__list">
          <?php 
          $cats = [
            ['phone', 'dienthoai.jpg', 'Điện thoại'],
            ['laptop', 'laptop.jpg', 'Laptop'],
            ['tablet', 'tablet.jpg', 'Tablet'],
            ['accessories', 'phu-kien.jpg', 'Phụ kiện'],
            ['watch', 'dong-ho.jpg', 'Đồng hồ'],
            ['pc', 'pc.jpg', 'PC'],
          ];
          foreach ($cats as $c): ?>
            <a href="user/pages/main/products.php?category=<?= $c[0] ?>" class="category__item">
              <img src="user/assets/images/main/<?= $c[1] ?>" alt="<?= $c[2] ?>" class="category__img">
              <?= $c[2] ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- PROMO -->
      <div class="promo-banner">
        <div class="marquee-container">
          <div class="marquee-content">
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
            <div class="promo-card bg-warning text-dark">Giảm đến 50% Laptop Gaming</div>
            <div class="promo-card bg-danger text-white">Miễn phí ship từ 2 triệu</div>
            <div class="promo-card bg-success text-white">Bảo hành 24 tháng</div>
            <div class="promo-card bg-primary text-white">Trả góp 0%</div>
            <div class="promo-card bg-info text-white">Ưu đãi sinh viên</div>
          </div>
        </div>
      </div>

      <!-- SẢN PHẨM MỚI -->
      <section class="section">
        <h2 class="section__title">Sản Phẩm Mới</h2>
        <div class="grid__row">
          <?php foreach ($new_products as $product): 
            $price = $product['price'] ?? 0;
            $img_src = getProductImage($product['image'], 'index');
          ?>
            <div class="grid__col col-3">
              <div class="product-card">
                <div class="product-image">
                  <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                  <div class="product-badge new-badge">Mới</div>
                  <div class="product-overlay">
                    <a href="user/pages/main/product-detail.php?id=<?= $product['id'] ?>" class="btn-view-detail">
                      Xem chi tiết
                    </a>
                  </div>
                </div>
                <div class="product-info">
                  <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                  <div class="product-price">
                    <span class="current-price"><?= number_format($price) ?>đ</span>
                  </div>
                  <div class="product-actions">
                    <button class="btn btn-outline-primary action-btn" data-id="<?= $product['id'] ?>" data-action="buy">
                      Mua ngay
                    </button>
                    <button class="btn btn-primary action-btn" data-id="<?= $product['id'] ?>" data-action="cart">
                      Thêm vào giỏ
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- FLASH SALE -->
      <section class="section">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="section__title text-danger mb-0">
            <i class="bi bi-clock-fill"></i> Flash Sale - Giảm Sốc
          </h2>
          <div class="flash-sale-timer">
            <i class="bi bi-clock-fill"></i> Kết thúc sau: 
            <span id="countdown-timer" data-end="<?= $flash_sale_end ?>"></span>
          </div>
        </div>
        <div class="grid__row" id="flash-sale-products">
          <div class="text-center w-100 p-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </section>

      <!-- KHUYẾN MÃI -->
      <section class="section">
        <h2 class="section__title">Khuyến Mãi Hot</h2>
        <div class="grid__row">
          <?php foreach ($featured_products as $product): 
            $price = $product['price'] ?? 0;
            $compare_price = $product['compare_price'] ?? 0;
            $discount = ($compare_price > $price && $compare_price > 0) 
              ? round((($compare_price - $price) / $compare_price) * 100) : 0;
            $img_src = getProductImage($product['image'], 'index');
          ?>
            <div class="grid__col col-3">
              <div class="product-card">
                <div class="product-image">
                  <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                  <?php if ($discount > 0): ?>
                    <div class="product-badge discount-badge">-<?= $discount ?>%</div>
                  <?php endif; ?>
                  <div class="product-overlay">
                    <a href="user/pages/main/product-detail.php?id=<?= $product['id'] ?>" class="btn-view-detail">
                      Xem chi tiết
                    </a>
                  </div>
                </div>
                <div class="product-info">
                  <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                  <div class="product-price">
                    <?php if ($compare_price > $price && $compare_price > 0): ?>
                      <span class="old-price"><?= number_format($compare_price) ?>đ</span>
                      <span class="current-price"><?= number_format($price) ?>đ</span>
                    <?php else: ?>
                      <span class="current-price"><?= number_format($price) ?>đ</span>
                    <?php endif; ?>
                  </div>
                  <div class="product-actions">
                    <button class="btn btn-outline-primary action-btn" data-id="<?= $product['id'] ?>" data-action="buy">
                      Mua ngay
                    </button>
                    <button class="btn btn-primary action-btn" data-id="<?= $product['id'] ?>" data-action="cart">
                      Thêm vào giỏ
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

    </div>

    <?php include 'user/includes/footer.php'; ?>
    
    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" style="display: none;">
        <i class="bi bi-chevron-up"></i>
    </button>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Carousel
    let slideIndex = 0;
    const slides = document.querySelectorAll('.carousel__slide');
    const dots = document.querySelectorAll('.carousel__dot');
    let autoSlide;

    function showSlide(n) {
      if (n >= slides.length) slideIndex = 0;
      if (n < 0) slideIndex = slides.length - 1;
      slides.forEach(s => s.classList.remove('active'));
      dots.forEach(d => d.classList.remove('active'));
      slides[slideIndex].classList.add('active');
      dots[slideIndex].classList.add('active');
    }

    function changeSlide(n) { slideIndex += n; showSlide(slideIndex); resetAuto(); }
    function currentSlide(n) { slideIndex = n; showSlide(slideIndex); resetAuto(); }
    function autoPlay() { autoSlide = setInterval(() => changeSlide(1), 4000); }
    function resetAuto() { clearInterval(autoSlide); autoPlay(); }

    showSlide(0); autoPlay();

    // XỬ LÝ NÚT MUA / THÊM GIỎ
    $(document).on('click', '.action-btn', function(e) {
      e.preventDefault();
      const productId = $(this).data('id');
      const action = $(this).data('action');

      // Kiểm tra đăng nhập
      $.get('user/includes/auth-check.php', function(isLoggedIn) {
        if (isLoggedIn === 'true') {
          // ĐÃ ĐĂNG NHẬP → THÊM VÀO GIỎ HOẶC MUA NGAY
          $.post('user/ajax/add_to_cart.php', { product_id: productId, quantity: 1 }, function(res) {
            if (res.success) {
              if (action === 'cart') {
                showNotification('Đã thêm vào giỏ hàng!', 'success');
                updateCartBadge();
              } else {
                window.location.href = 'user/pages/main/checkout.php';
              }
            } else {
              showNotification(res.message || 'Lỗi thêm vào giỏ hàng!', 'error');
            }
          }, 'json').fail(function() {
            showNotification('Lỗi kết nối!', 'error');
          });
        } else {
          // CHƯA ĐĂNG NHẬP → LOAD MODAL TỪ FILE RIÊNG
          $.get('user/includes/auth-modal.php', function(data) {
            $('#authModal').html(data);
            const modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
          });
        }
      });
    });

    // Hiển thị thông báo
    function showNotification(message, type = 'info') {
      const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
      const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 9999; min-width: 300px;">
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
      
      $('body').append(alertHTML);
      
      // Tự động ẩn sau 3 giây
      setTimeout(() => {
        $('.alert').alert('close');
      }, 3000);
    }

    // Cập nhật badge giỏ hàng
    function updateCartBadge() {
      $.get('user/ajax/get_cart_count.php', function(count) {
        $('.cart-badge').text(count);
      });
    }

    // Back to top functionality
    document.addEventListener('DOMContentLoaded', function() {
      const backToTopBtn = document.getElementById('backToTop');
      
      if (backToTopBtn) {
        window.addEventListener('scroll', function() {
          if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'flex';
          } else {
            backToTopBtn.style.display = 'none';
          }
        });
        
        backToTopBtn.addEventListener('click', function() {
          window.scrollTo({ 
            top: 0, 
            behavior: 'smooth' 
          });
        });
      }
    });

    // LOAD FLASH SALE + COUNTDOWN
    $.get('user/ajax/flash_sale.php', function(data) {
      $('#flash-sale-products').html(data);

      const countdownEl = document.getElementById('countdown-timer');
      if (!countdownEl) return;

      const endTime = parseInt(countdownEl.getAttribute('data-end')) * 1000;

      function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
          $('#flash-sale-products').html('<div class="text-center p-5 text-danger fw-bold">Flash Sale đã kết thúc!</div>');
          return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let text = '';
        if (days > 0) text += days + ' ngày ';
        text += String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        countdownEl.textContent = text;
      }

      updateCountdown();
      setInterval(updateCountdown, 1000);
    });
  </script>
</body>
</html>