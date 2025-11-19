<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

$database = new Database();
$db = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều Khoản Dịch Vụ - SHOPNETS</title>
    
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- CSS File -->
    <link href="../../../assets/css/terms-of-service.css" rel="stylesheet">
</head>

<style>
/* terms-of-service.css */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html {
  font-size: 62.5%;
  line-height: 1.6rem;
  font-family: 'Inter', 'Roboto', sans-serif;
}

body {
  background: #f1f5f9;
  color: #1e293b;
  padding-top: 136px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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

/* GRID */
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

.col-12 {
  flex: 0 0 100%;
  max-width: 100%;
}

/* TERMS CONTAINER */
.terms-container {
  flex: 1;
  padding: 3rem 1rem;
  display: flex;
  justify-content: center;
}

.terms-card {
  background: white;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  border: 1px solid var(--border);
  overflow: hidden;
  width: 100%;
  max-width: 1000px;
  transition: var(--transition);
  margin-bottom: 32px;
}

.terms-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

.terms-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  padding: 3rem 2rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.terms-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
  z-index: 0;
}

.terms-header h1 {
  font-size: 2.5rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  position: relative;
  z-index: 1;
}

.terms-header p {
  opacity: 0.9;
  font-size: 1.6rem;
  position: relative;
  z-index: 1;
}

.terms-icon {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  z-index: 1;
}

.terms-body {
  padding: 2.5rem;
  /* ĐÃ XÓA max-height VÀ overflow-y ĐỂ HIỂN THỊ TOÀN BỘ NỘI DUNG */
  line-height: 1.7;
  font-size: 1.4rem;
}

/* SECTION TITLE - Giống với index.php */
.section__title {
  font-size: 1.9rem;
  font-weight: 600;
  text-align: center;
  margin-bottom: 24px;
  color: var(--dark);
  position: relative;
}

.section__title::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 50px;
  height: 4px;
  background: var(--primary);
  border-radius: 2px;
}

.terms-body h2.section-title {
  font-size: 1.9rem;
  font-weight: 600;
  margin: 3rem 0 1.5rem;
  color: var(--dark);
  position: relative;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--border);
}

.terms-body h2.section-title::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 60px;
  height: 4px;
  background: var(--primary);
  border-radius: 2px;
}

.terms-body h3 {
  color: var(--dark);
  font-weight: 600;
  margin: 2rem 0 1rem;
  font-size: 1.6rem;
}

.terms-body p {
  margin-bottom: 1.5rem;
  color: var(--dark);
  text-align: justify;
  font-size: 1.4rem;
}

.terms-body ul {
  padding-left: 1.5rem;
  margin: 1rem 0 2rem;
}

.terms-body li {
  margin-bottom: 0.8rem;
  color: var(--dark);
  font-size: 1.4rem;
}

/* HIGHLIGHT BOX - Giống product card style */
.highlight {
  background: rgba(37, 99, 235, 0.05);
  border-left: 4px solid var(--primary);
  padding: 1.5rem;
  border-radius: var(--radius-sm);
  margin: 2rem 0;
  border: 1px solid var(--border);
  transition: var(--transition);
}

.highlight:hover {
  box-shadow: var(--shadow-sm);
}

.warning {
  background: rgba(239, 68, 68, 0.05);
  border-left: 4px solid var(--danger);
  padding: 1.5rem;
  border-radius: var(--radius-sm);
  margin: 2rem 0;
  border: 1px solid var(--border);
}

/* CONTACT INFO - Giống category style */
.contact-info {
  background: var(--light);
  border: 1px solid var(--border);
  padding: 2rem;
  border-radius: var(--radius-sm);
  margin: 2rem 0;
  transition: var(--transition);
}

.contact-info:hover {
  box-shadow: var(--shadow-sm);
}

.contact-info ul {
  list-style: none;
  padding: 0;
}

.contact-info li {
  margin-bottom: 0.8rem;
  color: var(--dark);
  font-size: 1.4rem;
  padding: 0.3rem 0;
}

.contact-info strong {
  color: var(--primary);
  font-weight: 600;
}

/* BUTTON - Giống với index.php buttons */
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: transparent;
  color: var(--primary);
  border: 2px solid var(--primary);
  padding: 12px 24px;
  border-radius: var(--radius-sm);
  font-weight: 500;
  font-size: 1.4rem;
  text-decoration: none;
  transition: var(--transition);
  margin: 3rem 0 1rem;
}

.back-btn:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

/* RESPONSIVE */
@media (max-width: 992px) {
  .terms-card {
    margin: 0 15px 32px;
  }
}

@media (max-width: 768px) {
  body {
    padding-top: 180px;
  }
  
  .terms-header {
    padding: 2rem 1.5rem;
  }
  
  .terms-header h1 {
    font-size: 2rem;
  }
  
  .terms-body {
    padding: 2rem 1.5rem;
    /* ĐẢM BẢO VẪN KHÔNG CÓ CUỘN TRÊN MOBILE */
  }
  
  .terms-body h2.section-title {
    font-size: 1.7rem;
  }
  
  .highlight, .warning, .contact-info {
    padding: 1.2rem;
    margin: 1.5rem 0;
  }
}

@media (max-width: 576px) {
  .terms-header h1 {
    font-size: 1.8rem;
  }
  
  .terms-header p {
    font-size: 1.4rem;
  }
  
  .terms-body h2.section-title {
    font-size: 1.6rem;
  }
  
  .terms-body h3 {
    font-size: 1.5rem;
  }
  
  .back-btn {
    width: 100%;
    justify-content: center;
    margin: 2rem 0 1rem;
  }
  
  .terms-body {
    padding: 1.5rem;
  }
}
</style>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="terms-container">
        <div class="terms-card">
            <div class="terms-header">
                <i class="bi bi-file-text-fill terms-icon"></i>
                <h1>Điều Khoản Dịch Vụ</h1>
                <p>SHOPNETS - Chính sách sử dụng nền tảng</p>
            </div>

            <div class="terms-body">
                <div class="highlight">
                    <p><strong>Cập nhật lần cuối:</strong> <?php echo date('d/m/Y'); ?></p>
                    <p>Vui lòng đọc kỹ các điều khoản trước khi sử dụng dịch vụ của <strong>SHOPNETS</strong>.</p>
                </div>

                <h2 class="section-title">1. Chấp Nhận Điều Khoản</h2>
                <p>Bằng việc truy cập và sử dụng SHOPNETS, bạn đồng ý bị ràng buộc bởi các điều khoản này. Nếu không đồng ý, vui lòng không sử dụng dịch vụ.</p>

                <h2 class="section-title">2. Mô Tả Dịch Vụ</h2>
                <p>SHOPNETS là nền tảng thương mại điện tử cung cấp sản phẩm công nghệ chính hãng, giao hàng nhanh, bảo hành đầy đủ.</p>

                <h2 class="section-title">3. Đăng Ký Tài Khoản</h2>
                <h3>3.1. Điều kiện đăng ký</h3>
                <ul>
                    <li>Đủ 16 tuổi trở lên</li>
                    <li>Cung cấp thông tin chính xác</li>
                    <li>Chịu trách nhiệm bảo mật tài khoản</li>
                </ul>

                <h3>3.2. Bảo mật tài khoản</h3>
                <ul>
                    <li>Giữ bí mật mật khẩu</li>
                    <li>Thông báo ngay nếu bị xâm phạm</li>
                    <li>Đăng xuất sau khi sử dụng</li>
                </ul>

                <h2 class="section-title">4. Quyền và Nghĩa Vụ</h2>
                <h3>4.1. Quyền của bạn</h3>
                <ul>
                    <li>Mua sắm sản phẩm chính hãng</li>
                    <li>Nhận hỗ trợ 24/7</li>
                    <li>Đổi trả trong 7 ngày</li>
                </ul>

                <h3>4.2. Nghĩa vụ</h3>
                <ul>
                    <li>Tuân thủ pháp luật Việt Nam</li>
                    <li>Không gian lận, lừa đảo</li>
                    <li>Không spam, quấy rối</li>
                </ul>

                <h2 class="section-title">5. Nội Dung Cấm</h2>
                <div class="warning">
                    <p><strong>CẢNH BÁO:</strong> Vi phạm sẽ bị khóa tài khoản vĩnh viễn:</p>
                </div>
                <ul>
                    <li>Phát tán nội dung bất hợp pháp</li>
                    <li>Lừa đảo, giả mạo</li>
                    <li>Xâm phạm bản quyền</li>
                    <li>Spam, quảng cáo trái phép</li>
                </ul>

                <h2 class="section-title">6. Sở Hữu Trí Tuệ</h2>
                <p>Tất cả nội dung, hình ảnh, logo thuộc về SHOPNETS và được bảo vệ bởi luật bản quyền.</p>

                <h2 class="section-title">7. Giới Hạn Trách Nhiệm</h2>
                <p>SHOPNETS không chịu trách nhiệm cho thiệt hại gián tiếp, mất dữ liệu, hoặc hành vi của bên thứ ba.</p>

                <h2 class="section-title">8. Chấm Dứt Dịch Vụ</h2>
                <p>Chúng tôi có quyền khóa tài khoản nếu phát hiện vi phạm nghiêm trọng.</p>

                <h2 class="section-title">9. Thay Đổi Điều Khoản</h2>
                <p>Thông báo thay đổi sẽ được gửi qua email hoặc hiển thị trên trang chủ.</p>

                <h2 class="section-title">10. Liên Hệ</h2>
                <div class="contact-info">
                    <ul>
                        <li><strong>Email:</strong> support@shopnets.vn</li>
                        <li><strong>Hotline:</strong> 1900 1234</li>
                        <li><strong>Thời gian:</strong> 8:00 - 22:00 hàng ngày</li>
                    </ul>
                </div>

                <a href="../../index.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i> Quay về Trang chủ
                </a>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>