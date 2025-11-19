<?php
// pages/main/privacy-policy.php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php';

$database = new Database();
$db = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chính Sách Bảo Mật - ShopNets</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
  <link href="../../../assets/css/responsive.css" rel="stylesheet">
</head>

<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { font-size: 62.5%; line-height: 1.6rem; font-family: 'Inter', 'Roboto', sans-serif; }
  body { background: #f1f5f9; color: #1e293b; padding-top: 136px; }

  :root {
    --primary: #2563eb; --primary-dark: #1d4ed8; --dark: #1e293b; --light: #f8fafc;
    --gray: #94a3b8; --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
    --border: #e2e8f0; --shadow-sm: 0 1px 3px rgba(0,0,0,0.1); --shadow: 0 4px 6px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px rgba(0,0,0,0.15); --radius-sm: 8px; --radius: 12px; --radius-lg: 16px;
    --transition: all 0.3s ease;
  }

  .grid { width: 1200px; max-width: 100%; margin: 0 auto; padding: 0 15px; }

  /* Ẩn icon shield-lock trong policy header */
  .policy-icon {
    display: none !important;
  }

  /* BREADCRUMB - Đã tăng kích thước chữ */
  .breadcrumb {
    background: white;
    border-radius: var(--radius);
    padding: 14px 24px;
    box-shadow: var(--shadow-sm);
    font-size: 1.7rem; /* Đã tăng từ 1.6 → 1.7rem */
    margin-bottom: 28px;
    border: 1px solid var(--border);
  }
  .breadcrumb-item a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    font-size: 1.7rem; /* Thêm font-size cho link */
  }
  .breadcrumb-item a:hover {
    color: var(--primary-dark);
  }
  .breadcrumb-item.active {
    color: var(--dark);
    font-weight: 700;
    font-size: 1.7rem; /* Thêm font-size cho item active */
  }

  /* POLICY CARD */
  .policy-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    border: 1px solid var(--border);
    transition: var(--transition);
  }
  .policy-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
  }

  .policy-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 32px 24px;
    text-align: center;
    position: relative;
  }
  .policy-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
  }
  .policy-header h1 {
    font-size: 2.4rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    position: relative;
    z-index: 1;
  }
  .policy-header p {
    font-size: 1.5rem;
    opacity: 0.9;
    margin: 0;
    position: relative;
    z-index: 1;
  }

  .policy-body {
    padding: 32px 28px;
    font-size: 1.5rem;
    line-height: 1.7;
  }

  .section-title {
    font-size: 1.9rem;
    font-weight: 600;
    color: var(--dark);
    margin: 28px 0 16px;
    position: relative;
  }
  .section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 50px;
    height: 4px;
    background: var(--primary);
    border-radius: 2px;
  }

  .policy-body h3 {
    font-size: 1.6rem;
    color: var(--primary);
    margin: 20px 0 10px;
    font-weight: 600;
  }

  .policy-body p, .policy-body li {
    margin-bottom: 12px;
    color: #475569;
  }

  .policy-body ul {
    padding-left: 20px;
    margin-bottom: 16px;
  }

  .highlight {
    background: #f0f9ff;
    border-left: 4px solid var(--primary);
    padding: 16px 20px;
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    margin: 24px 0;
    font-size: 1.45rem;
  }

  .contact-info {
    background: #f8fafc;
    border: 1px solid var(--border);
    padding: 20px;
    border-radius: var(--radius);
    margin: 24px 0;
    font-size: 1.45rem;
  }
  .contact-info strong {
    color: var(--dark);
  }

  .back-btn {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.5rem;
    text-decoration: none;
    box-shadow: var(--shadow);
    transition: var(--transition);
    margin-top: 20px;
  }
  .back-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  /* RESPONSIVE */
  @media (max-width: 992px) {
    .policy-header h1 { font-size: 2.1rem; }
    .policy-body { padding: 24px 20px; }
  }
  @media (max-width: 768px) {
    body { padding-top: 180px; }
    .policy-header { padding: 24px 16px; }
    .policy-header h1 { font-size: 1.9rem; }
    .policy-body { padding: 20px 16px; }
    .section-title { font-size: 1.7rem; }
    .breadcrumb { font-size: 1.6rem; } /* Điều chỉnh cho mobile */
    .breadcrumb-item a { font-size: 1.6rem; }
    .breadcrumb-item.active { font-size: 1.6rem; }
  }
  @media (max-width: 576px) {
    .policy-header h1 { font-size: 1.7rem; }
    .policy-body { font-size: 1.4rem; }
    .back-btn { width: 100%; text-align: center; }
    .breadcrumb { font-size: 1.5rem; } /* Điều chỉnh cho mobile nhỏ */
    .breadcrumb-item a { font-size: 1.5rem; }
    .breadcrumb-item.active { font-size: 1.5rem; }
  }
</style>

<body>
  <div class="app">
    <?php include '../../includes/header.php'; ?>

    <div class="grid">
      <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Chính Sách Bảo Mật</li>
        </ol>
        </nav>

      <!-- Policy Card -->
      <div class="policy-card">
        <div class="policy-header">
          <!-- Icon đã được ẩn bằng CSS -->
          <i class="bi bi-shield-lock-fill policy-icon"></i>
          <h1>Chính Sách Bảo Mật</h1>
          <p>Chúng tôi cam kết bảo vệ thông tin cá nhân của bạn</p>
        </div>

        <div class="policy-body">
          <div class="highlight">
            <p><strong>Cập nhật lần cuối:</strong> <?php echo date('d/m/Y'); ?></p>
            <p>Chính sách này giải thích cách ShopNets thu thập, sử dụng và bảo vệ dữ liệu của bạn khi sử dụng website.</p>
          </div>

          <h2 class="section-title">1. Thông Tin Chúng Tôi Thu Thập</h2>
          <h3>1.1. Thông tin cá nhân</h3>
          <p>Khi đăng ký tài khoản, chúng tôi có thể yêu cầu:</p>
          <ul>
            <li>Họ và tên</li>
            <li>Email và mật khẩu</li>
            <li>Số điện thoại</li>
            <li>Địa chỉ giao hàng</li>
          </ul>

          <h3>1.2. Thông tin tự động</h3>
          <p>Chúng tôi thu thập:</p>
          <ul>
            <li>Địa chỉ IP, loại trình duyệt</li>
            <li>Cookie và dữ liệu phiên</li>
            <li>Thời gian truy cập, trang đã xem</li>
          </ul>

          <h2 class="section-title">2. Mục Đích Sử Dụng</h2>
          <p>Thông tin được dùng để:</p>
          <ul>
            <li>Xử lý đơn hàng và giao hàng</li>
            <li>Gửi thông báo khuyến mãi (nếu bạn đồng ý)</li>
            <li>Cải thiện trải nghiệm người dùng</li>
            <li>Phát hiện gian lận và bảo mật hệ thống</li>
          </ul>

          <h2 class="section-title">3. Chia Sẻ Thông Tin</h2>
          <p>Chúng tôi <strong>không bán</strong> thông tin của bạn. Chỉ chia sẻ khi:</p>
          <ul>
            <li>Cần thiết cho đối tác vận chuyển</li>
            <li>Tuân thủ yêu cầu pháp lý</li>
            <li>Bảo vệ quyền lợi của ShopNets hoặc người dùng</li>
          </ul>

          <h2 class="section-title">4. Bảo Mật Dữ Liệu</h2>
          <p>Chúng tôi áp dụng:</p>
          <ul>
            <li>Mã hóa SSL cho toàn bộ website</li>
            <li>Lưu trữ mật khẩu đã băm (hashed)</li>
            <li>Kiểm tra bảo mật định kỳ</li>
            <li>Giới hạn quyền truy cập nội bộ</li>
          </ul>

          <h2 class="section-title">5. Quyền Của Bạn</h2>
          <p>Bạn có thể:</p>
          <ul>
            <li>Xem, sửa, xóa thông tin cá nhân</li>
            <li>Hủy nhận email quảng cáo</li>
            <li>Yêu cầu xuất dữ liệu</li>
            <li>Liên hệ xóa tài khoản</li>
          </ul>

          <h2 class="section-title">6. Cookie</h2>
          <p>Chúng tôi dùng cookie để:</p>
          <ul>
            <li>Giữ trạng thái đăng nhập</li>
            <li>Lưu giỏ hàng</li>
            <li>Phân tích hành vi người dùng</li>
          </ul>
          <p>Bạn có thể tắt cookie trong trình duyệt.</p>

          <h2 class="section-title">7. Thay Đổi Chính Sách</h2>
          <p>Chúng tôi có thể cập nhật chính sách này. Phiên bản mới sẽ được đăng tải tại đây.</p>

          <h2 class="section-title">8. Liên Hệ</h2>
          <div class="contact-info">
            <p><strong>Email:</strong> support@shopnets.vn</p>
            <p><strong>Hotline:</strong> 1900 1234</p>
            <p><strong>Địa chỉ:</strong> 123 Đường Công Nghệ, Q.1, TP.HCM</p>
          </div>

          <a href="<?php echo BASE_URL; ?>index.php" class="back-btn">
            Quay Lại Trang Chủ
          </a>
        </div>
      </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
