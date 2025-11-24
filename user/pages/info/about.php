<?php
session_start();

// === THÊM ĐOẠN NÀY ĐỂ KHỞI TẠO BASE_URL (BẮT BUỘC) ===
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $folder = dirname($_SERVER['SCRIPT_NAME']); // tự động lấy thư mục gốc
    $folder = $folder === '/' ? '' : rtrim($folder, '/') . '/';
    define('BASE_URL', $protocol . $domain . $folder);
}
// ========================================================

require_once '../../../user/includes/database.php';
require_once '../../../user/includes/functions.php';
$database = new Database();
$db = $database->getConnection();

$categories = getCategories($db);
$brands = getBrands($db);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Giới thiệu - ShopNets</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
</head>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html {
    font-size: 62.5%;
    line-height: 1.6rem;
    font-family: 'Inter', 'Roboto', sans-serif;
  }
  body {
    background: #f1f5f9;
    color: #1e293b;
  }

  :root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --dark: #1e293b;
    --light: #f8fafc;
    --gray: #94a3b8;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --border: #e2e8f0;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
    --radius-sm: 8px;
    --radius: 12px;
    --radius-lg: 16px;
    --transition: all 0.3s ease;
  }

  /* GRID & CONTAINER */
  .grid {
    width: 1200px;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
  }
  .grid__row {
    display: flex;
    flex-wrap: wrap;
    margin: -10px;
  }
  .grid__col {
    padding: 10px;
  }
  .col-3 { flex: 0 0 25%; max-width: 25%; }
  .col-4 { flex: 0 0 33.33%; max-width: 33.33%; }
  .col-6 { flex: 0 0 50%; max-width: 50%; }
  .col-12 { flex: 0 0 100%; max-width: 100%; }

  /* === BREADCRUMB === */
  .breadcrumb {
    background: white;
    border-radius: var(--radius);
    padding: 1.2rem 2rem;
    box-shadow: var(--shadow-sm);
    font-size: 1.4rem !important;
    margin-bottom: 2rem;
    margin-top: 30px;
    width: 100%;
  }
  .breadcrumb-item a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 1.4rem !important;
  }
  .breadcrumb-item.active { 
    color: var(--dark); 
    font-weight: 500;
    font-size: 1.4rem !important;
  }
  .breadcrumb-item a:hover { 
    color: #2563eb; 
    text-decoration: underline;
  }

  @media (max-width: 768px) {
    .breadcrumb {
      padding: 1rem 1.5rem;
      margin-top: 1rem;
      margin-bottom: 1.5rem;
    }
  }

  @media (max-width: 576px) {
    .breadcrumb {
      padding: 0.8rem 1.2rem;
      font-size: 1.2rem !important;
    }
  }

  /* SECTION */
  .section {
    background: white; padding: 28px; border-radius: var(--radius);
    box-shadow: var(--shadow); margin-bottom: 32px;
  }
  .section__title {
    font-size: 2.1rem;
    font-weight: 600; text-align: center;
    margin-bottom: 24px; color: var(--dark); position: relative;
  }
  .section__title::after {
    content: ''; position: absolute; bottom: -10px; left: 50%;
    transform: translateX(-50%); width: 50px; height: 4px;
    background: var(--primary); border-radius: 2px;
  }

  /* ABOUT HERO */
  .about-hero {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; padding: 60px 0; border-radius: var(--radius); margin-bottom: 32px;
    box-shadow: var(--shadow-lg); overflow: hidden; position: relative;
  }
  .about-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('assets/images/main/banner1.jpg') center/cover no-repeat;
    opacity: 0.15; z-index: 0;
  }
  .about-hero > * { position: relative; z-index: 1; }
  .about-hero h1 {
    font-size: 3.4rem;
    font-weight: 700; margin-bottom: 12px;
  }
  .about-hero p { 
    font-size: 1.7rem;
    margin-bottom: 24px; opacity: 0.9; 
  }

  /* INFO CARD */
  .info-card {
    background: white; border-radius: var(--radius); padding: 24px;
    text-align: center; box-shadow: var(--shadow); transition: var(--transition);
    height: 100%; border: 1px solid var(--border);
  }
  .info-card:hover {
    transform: translateY(-6px); box-shadow: var(--shadow-lg);
  }
  .info-icon {
    width: 64px; height: 64px; margin: 0 auto 16px;
    background: var(--primary); color: white; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    box-shadow: var(--shadow);
  }
  .info-card h5 {
    font-size: 1.7rem;
    font-weight: 600; margin-bottom: 12px; color: var(--dark);
  }
  .info-card p { 
    font-size: 1.5rem;
    color: var(--gray); margin-bottom: 8px; 
  }

  /* STATS CARD */
  .stats-card {
    background: white; border-radius: var(--radius); padding: 24px;
    text-align: center; box-shadow: var(--shadow); transition: var(--transition);
    height: 100%; border: 1px solid var(--border);
  }
  .stats-card:hover {
    transform: translateY(-6px); box-shadow: var(--shadow-lg);
  }
  .stats-number {
    font-size: 3.2rem;
    font-weight: 700; color: var(--primary); margin-bottom: 8px;
  }
  .stats-label {
    font-size: 1.5rem;
    color: var(--dark); font-weight: 600;
  }

  /* VALUES CARD */
  .values-card {
    background: white; border-radius: var(--radius); padding: 24px;
    text-align: center; box-shadow: var(--shadow); transition: var(--transition);
    height: 100%; border: 1px solid var(--border);
  }
  .values-card:hover {
    transform: translateY(-6px); box-shadow: var(--shadow-lg);
  }
  .values-icon {
    width: 64px; height: 64px; margin: 0 auto 16px;
    background: var(--primary); color: white; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
    box-shadow: var(--shadow);
  }
  .values-card h5 {
    font-size: 1.7rem;
    font-weight: 600; margin-bottom: 12px; color: var(--dark);
  }
  .values-card p { 
    font-size: 1.5rem;
    color: var(--gray); margin-bottom: 8px; 
  }

  /* ABOUT CONTENT */
  .about-content {
    font-size: 1.6rem;
    line-height: 1.7;
    color: var(--dark);
    margin-bottom: 20px;
  }
  .about-content p {
    margin-bottom: 15px;
  }

  /* NEWSLETTER */
  .newsletter {
    background: var(--primary); color: white; padding: 40px 0; border-radius: var(--radius);
    text-align: center; margin-bottom: 32px; box-shadow: var(--shadow);
  }
  .newsletter h2 {
    font-size: 2.4rem;
    font-weight: 700; margin-bottom: 12px;
  }
  .newsletter p { 
    font-size: 1.6rem;
    margin-bottom: 20px; opacity: 0.9; 
  }
  .newsletter-form {
    max-width: 500px; margin: 0 auto; display: flex; gap: 12px;
  }
  .newsletter-input {
    flex: 1; padding: 14px 20px; border-radius: 50px; border: none;
    font-size: 1.5rem;
  }
  .newsletter-btn {
    background: var(--warning); color: var(--dark); border: none;
    padding: 0 28px; border-radius: 50px; font-weight: 600; 
    font-size: 1.5rem;
  }

  /* RESPONSIVE */
  @media (max-width: 992px) {
    .col-3, .col-4 { flex: 0 0 50%; max-width: 50%; }
    .section__title { font-size: 1.9rem; }
    .about-hero h1 { font-size: 2.8rem; }
    .newsletter h2 { font-size: 2.1rem; }
  }
  @media (max-width: 768px) {
    body { }
    .col-3, .col-4, .col-6 { flex: 0 0 100%; max-width: 100%; }
    .about-hero h1 { font-size: 2.4rem; }
    .about-hero p { font-size: 1.5rem; }
    .newsletter-form { flex-direction: column; }
    .newsletter-input, .newsletter-btn { border-radius: 50px; }
  }
  @media (max-width: 576px) {
    .about-hero { padding: 40px 0; }
    .about-hero h1 { font-size: 2.1rem; }
    .section__title { font-size: 1.7rem; }
  }
</style>

<body>
  <div class="app">
    <?php include '../../../user/includes/header.php'; ?>

    <!-- Modal Đăng nhập -->
    <div id="authModal"></div>

    <div class="grid">

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>../../index.php">Trang chủ</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Giới Thiệu</li>
        </ol>
      </nav>

      <!-- ABOUT HERO -->
      <section class="about-hero">
        <div class="grid">
          <div class="grid__row">
            <div class="grid__col col-8">
              <h1>Giới Thiệu Về <span style="color:#fbbf24">ShopNets</span></h1>
              <p>Cửa hàng công nghệ uy tín với giá cả hợp lý và dịch vụ tốt</p>
            </div>
            <div class="grid__col col-4" style="text-align:center;">
              <i class="bi bi-laptop" style="font-size:120px; opacity:0.2;"></i>
            </div>
          </div>
        </div>
      </section>

      <!-- GIỚI THIỆU CHUNG -->
      <section class="section">
        <h2 class="section__title">Về Chúng Tôi</h2>
        <div class="about-content">
          <p>ShopNets là cửa hàng công nghệ được thành lập với mục tiêu mang đến cho khách hàng những sản phẩm công nghệ chất lượng với giá cả phải chăng.</p>
          <p>Chúng tôi chuyên cung cấp các sản phẩm như laptop, điện thoại, phụ kiện công nghệ và các thiết bị điện tử khác.</p>
          <p>Với đội ngũ nhân viên trẻ trung, nhiệt tình và am hiểu công nghệ, chúng tôi cam kết mang đến trải nghiệm mua sắm tốt nhất cho khách hàng.</p>
        </div>
      </section>

      <!-- THỐNG KÊ ĐƠN GIẢN -->
      <section class="section">
        <h2 class="section__title">ShopNets Trong Số Liệu</h2>
        <div class="grid__row">
          <div class="grid__col col-3">
            <div class="stats-card">
              <div class="stats-number">1</div>
              <div class="stats-label">Tháng Hoạt Động</div>
            </div>
          </div>
          <div class="grid__col col-3">
            <div class="stats-card">
              <div class="stats-number">120+</div>
              <div class="stats-label">Sản Phẩm</div>
            </div>
          </div>
          <div class="grid__col col-3">
            <div class="stats-card">
              <div class="stats-number">200+</div>
              <div class="stats-label">Khách Hàng</div>
            </div>
          </div>
          <div class="grid__col col-3">
            <div class="stats-card">
              <div class="stats-number">5</div>
              <div class="stats-label">Nhân Viên</div>
            </div>
          </div>
        </div>
      </section>

      <!-- TẦM NHÌN & SỨ MỆNH -->
      <section class="section">
        <h2 class="section__title">Điều Chúng Tôi Hướng Đến</h2>
        <div class="grid__row">
          <div class="grid__col col-6">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-eye-fill"></i></div>
              <h5>Tầm Nhìn</h5>
              <p>Trở thành cửa hàng công nghệ đáng tin cậy cho sinh viên và người dùng cá nhân.</p>
            </div>
          </div>
          <div class="grid__col col-6">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-bullseye"></i></div>
              <h5>Sứ Mệnh</h5>
              <p>Cung cấp sản phẩm công nghệ chất lượng với giá cả hợp lý và dịch vụ hỗ trợ tận tình.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- GIÁ TRỊ CỐT LÕI -->
      <section class="section">
        <h2 class="section__title">Giá Trị Cốt Lõi</h2>
        <div class="grid__row">
          <div class="grid__col col-4">
            <div class="values-card">
              <div class="values-icon"><i class="bi bi-shield-check"></i></div>
              <h5>Chất Lượng</h5>
              <p>Sản phẩm chính hãng, đảm bảo chất lượng.</p>
            </div>
          </div>
          <div class="grid__col col-4">
            <div class="values-card">
              <div class="values-icon"><i class="bi bi-currency-dollar"></i></div>
              <h5>Giá Tốt</h5>
              <p>Giá cả cạnh tranh, phù hợp với sinh viên.</p>
            </div>
          </div>
          <div class="grid__col col-4">
            <div class="values-card">
              <div class="values-icon"><i class="bi bi-headset"></i></div>
              <h5>Hỗ Trợ</h5>
              <p>Hỗ trợ kỹ thuật và tư vấn nhiệt tình.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- TẠI SAO CHỌN CHÚNG TÔI -->
      <section class="section">
        <h2 class="section__title">Tại Sao Chọn ShopNets?</h2>
        <div class="grid__row">
          <div class="grid__col col-6">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-truck"></i></div>
              <h5>Giao Hàng Nhanh</h5>
              <p>Giao hàng trong ngày tại nội thành.</p>
            </div>
          </div>
          <div class="grid__col col-6">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-arrow-left-right"></i></div>
              <h5>Đổi Trả Dễ Dàng</h5>
              <p>Đổi trả trong vòng 7 ngày nếu có lỗi.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- NEWSLETTER -->
      <div class="newsletter">
        <h2>Nhận Thông Báo Khuyến Mãi</h2>
        <p>Đăng ký để nhận thông tin về sản phẩm mới và khuyến mãi</p>
        <form class="newsletter-form">
          <input type="email" class="newsletter-input" placeholder="Email của bạn...">
          <button type="submit" class="newsletter-btn">Đăng Ký</button>
        </form>
      </div>

    </div> <!-- END .grid -->

    <?php include '../../../user/includes/footer.php'; ?>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>