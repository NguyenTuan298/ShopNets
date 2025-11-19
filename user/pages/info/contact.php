<?php
// pages/contact.php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

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
  <title>Liên hệ - ShopNets</title>

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
    padding-top: 136px;
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

  /* BREADCRUMB - Đã thêm và tăng kích thước */
  .breadcrumb {
    background: white;
    border-radius: var(--radius);
    padding: 14px 24px;
    box-shadow: var(--shadow-sm);
    font-size: 1.7rem;
    margin-bottom: 28px;
    border: 1px solid var(--border);
  }
  .breadcrumb-item a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    font-size: 1.7rem;
  }
  .breadcrumb-item a:hover {
    color: var(--primary-dark);
  }
  .breadcrumb-item.active {
    color: var(--dark);
    font-weight: 700;
    font-size: 1.7rem;
  }

  /* SECTION */
  .section {
    background: white; padding: 28px; border-radius: var(--radius);
    box-shadow: var(--shadow); margin-bottom: 32px;
  }
  .section__title {
    font-size: 2.1rem; /* Tăng từ 1.9rem */
    font-weight: 600; text-align: center;
    margin-bottom: 24px; color: var(--dark); position: relative;
  }
  .section__title::after {
    content: ''; position: absolute; bottom: -10px; left: 50%;
    transform: translateX(-50%); width: 50px; height: 4px;
    background: var(--primary); border-radius: 2px;
  }

  /* CONTACT HERO */
  .contact-hero {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; padding: 60px 0; border-radius: var(--radius); margin-bottom: 32px;
    box-shadow: var(--shadow-lg); overflow: hidden; position: relative;
  }
  .contact-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('assets/images/main/banner1.jpg') center/cover no-repeat;
    opacity: 0.15; z-index: 0;
  }
  .contact-hero > * { position: relative; z-index: 1; }
  .contact-hero h1 {
    font-size: 3.4rem; /* Tăng từ 3.2rem */
    font-weight: 700; margin-bottom: 12px;
  }
  .contact-hero p { 
    font-size: 1.7rem; /* Tăng từ 1.6rem */
    margin-bottom: 24px; opacity: 0.9; 
  }
  .contact-hero .btn {
    padding: 12px 28px; font-size: 1.6rem; /* Tăng từ 1.5rem */
    border-radius: 50px; font-weight: 600;
    transition: var(--transition);
  }
  .contact-hero .btn-warning {
    background: var(--warning); color: var(--dark); border: none;
  }
  .contact-hero .btn-warning:hover { background: #e68a00; transform: translateY(-2px); }
  .contact-hero .btn-outline-light {
    border: 2px solid white; color: white;
  }
  .contact-hero .btn-outline-light:hover {
    background: white; color: var(--primary);
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
    font-size: 2rem; /* Tăng từ 1.8rem */
    box-shadow: var(--shadow);
  }
  .info-card h5 {
    font-size: 1.7rem; /* Tăng từ 1.6rem */
    font-weight: 600; margin-bottom: 12px; color: var(--dark);
  }
  .info-card p { 
    font-size: 1.5rem; /* Tăng từ 1.4rem */
    color: var(--gray); margin-bottom: 8px; 
  }
  .info-card a {
    color: var(--primary); font-size: 1.45rem; /* Tăng từ 1.35rem */
    font-weight: 500; text-decoration: none;
  }
  .info-card a:hover { text-decoration: underline; }

  /* CONTACT FORM */
  .contact-form-card {
    background: white; padding: 28px; border-radius: var(--radius);
    box-shadow: var(--shadow); border: 1px solid var(--border);
  }
  .contact-form-card h3 {
    font-size: 2.1rem; /* Tăng từ 1.9rem */
    font-weight: 600; margin-bottom: 20px; color: var(--dark);
    position: relative; text-align: center;
  }
  .contact-form-card h3::after {
    content: ''; position: absolute; bottom: -8px; left: 50%;
    transform: translateX(-50%); width: 50px; height: 4px;
    background: var(--primary); border-radius: 2px;
  }
  .form-control {
    border-radius: var(--radius-sm); padding: 12px 16px; 
    font-size: 1.5rem; /* Tăng từ 1.4rem */
    border: 1px solid var(--border); transition: var(--transition);
  }
  .form-control:focus {
    border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
  }
  .btn-submit {
    background: var(--primary); color: white; border: none; padding: 14px;
    font-size: 1.6rem; /* Tăng từ 1.5rem */
    font-weight: 600; border-radius: 50px; width: 100%;
    transition: var(--transition);
  }
  .btn-submit:hover {
    background: var(--primary-dark); transform: translateY(-2px);
  }

  /* HOURS CARD */
  .hours-card {
    background: white; padding: 28px; border-radius: var(--radius);
    box-shadow: var(--shadow); border: 1px solid var(--border); height: 100%;
  }
  .hours-card h3 {
    font-size: 2.1rem; /* Tăng từ 1.9rem */
    font-weight: 600; margin-bottom: 20px; color: var(--dark);
    position: relative; text-align: center;
  }
  .hours-card h3::after {
    content: ''; position: absolute; bottom: -8px; left: 50%;
    transform: translateX(-50%); width: 50px; height: 4px;
    background: var(--primary); border-radius: 2px;
  }
  .hours-list {
    list-style: none; font-size: 1.5rem; /* Tăng từ 1.4rem */
  }
  .hours-list li {
    display: flex; justify-content: space-between; padding: 10px 0;
    border-bottom: 1px dashed var(--border);
  }
  .hours-list li:last-child { border-bottom: none; }
  .hours-list strong { color: var(--primary); font-weight: 600; }

  /* MAP */
  .map-container {
    height: 400px; border-radius: var(--radius); overflow: hidden;
    box-shadow: var(--shadow); cursor: pointer; position: relative;
  }
  .map-placeholder {
    background: #e2e8f0; display: flex; flex-direction: column;
    align-items: center; justify-content: center; height: 100%;
    color: var(--dark); font-size: 1.6rem; /* Tăng từ 1.5rem */
  }
  .map-placeholder i { font-size: 3.5rem; margin-bottom: 12px; color: var(--primary); }
  .map-placeholder small { color: var(--gray); }

  /* FAQ */
  .faq-item {
    background: white; border-radius: var(--radius); margin-bottom: 16px;
    box-shadow: var(--shadow-sm); overflow: hidden; border: 1px solid var(--border);
  }
  .faq-question {
    padding: 16px 20px; margin: 0; background: #f8fafc; cursor: pointer;
    font-weight: 600; font-size: 1.6rem; /* Tăng từ 1.5rem */
    color: var(--dark); position: relative;
    transition: var(--transition);
  }
  .faq-question::after {
    content: '+'; position: absolute; right: 20px; font-size: 1.8rem;
    transition: transform 0.3s ease;
  }
  .faq-question.active::after { content: '−'; }
  .faq-question:hover { background: #eef2ff; }
  .faq-answer {
    padding: 0 20px; max-height: 0; overflow: hidden; transition: max-height 0.4s ease;
    font-size: 1.5rem; /* Tăng từ 1.4rem */
    color: var(--gray); line-height: 1.7;
  }
  .faq-answer.active { padding: 16px 20px; max-height: 200px; }

  /* NEWSLETTER */
  .newsletter {
    background: var(--primary); color: white; padding: 40px 0; border-radius: var(--radius);
    text-align: center; margin-bottom: 32px; box-shadow: var(--shadow);
  }
  .newsletter h2 {
    font-size: 2.4rem; /* Tăng từ 2.2rem */
    font-weight: 700; margin-bottom: 12px;
  }
  .newsletter p { 
    font-size: 1.6rem; /* Tăng từ 1.5rem */
    margin-bottom: 20px; opacity: 0.9; 
  }
  .newsletter-form {
    max-width: 500px; margin: 0 auto; display: flex; gap: 12px;
  }
  .newsletter-input {
    flex: 1; padding: 14px 20px; border-radius: 50px; border: none;
    font-size: 1.5rem; /* Tăng từ 1.4rem */
  }
  .newsletter-btn {
    background: var(--warning); color: var(--dark); border: none;
    padding: 0 28px; border-radius: 50px; font-weight: 600; 
    font-size: 1.5rem; /* Tăng từ 1.4rem */
  }

  /* RESPONSIVE */
  @media (max-width: 992px) {
    .col-3, .col-4 { flex: 0 0 50%; max-width: 50%; }
    .section__title { font-size: 1.9rem; }
    .contact-hero h1 { font-size: 2.8rem; }
    .contact-form-card h3, .hours-card h3 { font-size: 1.9rem; }
    .newsletter h2 { font-size: 2.1rem; }
  }
  @media (max-width: 768px) {
    body { padding-top: 180px; }
    .col-3, .col-4, .col-6 { flex: 0 0 100%; max-width: 100%; }
    .contact-hero h1 { font-size: 2.4rem; }
    .contact-hero p { font-size: 1.5rem; }
    .newsletter-form { flex-direction: column; }
    .newsletter-input, .newsletter-btn { border-radius: 50px; }
    .breadcrumb { font-size: 1.6rem; }
    .breadcrumb-item a { font-size: 1.6rem; }
    .breadcrumb-item.active { font-size: 1.6rem; }
  }
  @media (max-width: 576px) {
    .contact-hero { padding: 40px 0; }
    .contact-hero .btn { width: 100%; margin-bottom: 12px; }
    .contact-hero h1 { font-size: 2.1rem; }
    .section__title { font-size: 1.7rem; }
    .breadcrumb { font-size: 1.5rem; }
    .breadcrumb-item a { font-size: 1.5rem; }
    .breadcrumb-item.active { font-size: 1.5rem; }
  }
</style>

<body>
  <div class="app">
    <?php include '../../includes/header.php'; ?>

    <!-- Modal Đăng nhập -->
    <div id="authModal"></div>

    <div class="grid">

      <!-- Breadcrumb - Đã thêm -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
            <a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Liên Hệ</li>
        </ol>
      </nav>

      <!-- CONTACT HERO -->
      <section class="contact-hero">
        <div class="grid">
          <div class="grid__row">
            <div class="grid__col col-6">
              <h1>Liên Hệ Với <span style="color:#fbbf24">ShopNets</span></h1>
              <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
              <div style="display:flex; gap:16px; flex-wrap:wrap;">
                <a href="tel:19001234" class="btn btn-warning">
                  <i class="bi bi-telephone-fill"></i> Gọi ngay
                </a>
                <a href="#contact-form" class="btn btn-outline-light">
                  <i class="bi bi-envelope-fill"></i> Gửi tin nhắn
                </a>
              </div>
            </div>
            <div class="grid__col col-6" style="text-align:center;">
              <i class="bi bi-headset" style="font-size:180px; opacity:0.2;"></i>
            </div>
          </div>
        </div>
      </section>

      <!-- THÔNG TIN LIÊN HỆ -->
      <section class="section">
        <h2 class="section__title">Thông Tin Liên Hệ</h2>
        <div class="grid__row">
          <div class="grid__col col-4">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-geo-alt-fill"></i></div>
              <h5>Địa chỉ</h5>
              <p>123 Nguyễn Trãi, P. Bến Thành</p>
              <p style="font-size:1.4rem; color:#64748b;">Quận 1, TP. Hồ Chí Minh</p>
              <a href="#" onclick="showMapModal()">Xem bản đồ →</a>
            </div>
          </div>
          <div class="grid__col col-4">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-telephone-fill"></i></div>
              <h5>Điện thoại</h5>
              <p>1900 1234</p>
              <p style="font-size:1.4rem; color:#64748b;">(8:00 - 22:00)</p>
              <a href="tel:19001234">Gọi ngay →</a>
            </div>
          </div>
          <div class="grid__col col-4">
            <div class="info-card">
              <div class="info-icon"><i class="bi bi-envelope-fill"></i></div>
              <h5>Email</h5>
              <p>support@shopnets.vn</p>
              <p style="font-size:1.4rem; color:#64748b;">Phản hồi trong 24h</p>
              <a href="mailto:support@shopnets.vn">Gửi email →</a>
            </div>
          </div>
        </div>
      </section>

      <!-- FORM + GIỜ LÀM VIỆC -->
      <section id="contact-form" class="section">
        <div class="grid__row">
          <div class="grid__col col-8">
            <div class="contact-form-card">
              <h3>Gửi Tin Nhắn Cho Chúng Tôi</h3>
              <form id="contactForm">
                <div class="grid__row">
                  <div class="grid__col col-6">
                    <input type="text" class="form-control" placeholder="Họ và tên *" required>
                  </div>
                  <div class="grid__col col-6">
                    <input type="email" class="form-control" placeholder="Email *" required>
                  </div>
                  <div class="grid__col col-12" style="margin-top:12px;">
                    <input type="text" class="form-control" placeholder="Tiêu đề *" required>
                  </div>
                  <div class="grid__col col-12" style="margin-top:12px;">
                    <textarea class="form-control" rows="5" placeholder="Nội dung tin nhắn *" required></textarea>
                  </div>
                  <div class="grid__col col-12" style="margin-top:16px;">
                    <button type="submit" class="btn-submit">
                      <i class="bi bi-send-fill"></i> Gửi Tin Nhắn
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="grid__col col-4">
            <div class="hours-card">
              <h3>Giờ Làm Việc</h3>
              <ul class="hours-list">
                <li><span>Thứ 2 - Thứ 6</span><strong>8:00 - 22:00</strong></li>
                <li><span>Thứ 7</span><strong>8:00 - 20:00</strong></li>
                <li><span>Chủ nhật</span><strong>8:00 - 18:00</strong></li>
              </ul>
              <div style="margin-top:20px; padding:12px; background:#dbeafe; border-radius:8px; font-size:1.4rem;">
                <i class="bi bi-shield-fill-check" style="color:var(--primary);"></i>
                Hỗ trợ 24/7 qua email & hotline
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- BẢN ĐỒ -->
      <section class="section">
        <h2 class="section__title">Vị Trí Cửa Hàng</h2>
        <div class="map-container" onclick="showMapModal()">
          <div class="map-placeholder">
            <i class="bi bi-geo-alt-fill"></i>
            <p>123 Nguyễn Trãi, Quận 1, TP.HCM</p>
            <small>Click để xem bản đồ lớn</small>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section class="section">
        <h2 class="section__title">Câu Hỏi Thường Gặp</h2>
        <div class="faq-item">
          <div class="faq-question" onclick="toggleFAQ(this)">Làm thế nào để theo dõi đơn hàng?</div>
          <div class="faq-answer">Đăng nhập tài khoản → "Đơn hàng của tôi" → Nhập mã vận đơn hoặc xem trạng thái trực tiếp.</div>
        </div>
        <div class="faq-item">
          <div class="faq-question" onclick="toggleFAQ(this)">Chính sách đổi trả sản phẩm?</div>
          <div class="faq-answer">Đổi trả trong 30 ngày, sản phẩm nguyên seal, đầy đủ hộp và phụ kiện.</div>
        </div>
        <div class="faq-item">
          <div class="faq-question" onclick="toggleFAQ(this)">Có hỗ trợ trả góp 0% không?</div>
          <div class="faq-answer">Có! Hỗ trợ trả góp 0% qua thẻ tín dụng ngân hàng đối tác.</div>
        </div>
      </section>

      <!-- NEWSLETTER -->
      <div class="newsletter">
        <h2>Nhận Ưu Đãi Độc Quyền</h2>
        <p>Đăng ký để nhận mã giảm giá & thông tin khuyến mãi</p>
        <form class="newsletter-form">
          <input type="email" class="newsletter-input" placeholder="Email của bạn...">
          <button type="submit" class="newsletter-btn">Đăng Ký</button>
        </form>
      </div>

    </div> <!-- END .grid -->

    <?php include '../../includes/footer.php'; ?>
  </div>

  <!-- Map Modal -->
  <div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content" style="background:#1e293b; color:white; border-radius:16px; overflow:hidden;">
        <div class="modal-header border-0">
          <h5 class="modal-title"><i class="bi bi-geo-alt-fill"></i> Vị Trí Cửa Hàng ShopNets</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <div style="height:500px; background:#334155; display:flex; flex-direction:column; align-items:center; justify-content:center;">
            <i class="bi bi-geo-alt-fill" style="font-size:4rem; color:var(--primary); margin-bottom:16px;"></i>
            <p class="h5">123 Nguyễn Trãi, Quận 1, TP.HCM</p>
            <p class="text-muted">(Bản đồ Google Maps sẽ được tích hợp tại đây)</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function showMapModal() {
      new bootstrap.Modal(document.getElementById('mapModal')).show();
    }

    function toggleFAQ(el) {
      const answer = el.nextElementSibling;
      const isActive = answer.classList.contains('active');
      document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('active'));
      document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));
      if (!isActive) {
        answer.classList.add('active');
        el.classList.add('active');
      }
    }

    document.getElementById('contactForm').addEventListener('submit', function(e) {
      e.preventDefault();
      alert('Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong vòng 24h.');
      this.reset();
    });
  </script>
</body>
</html>
