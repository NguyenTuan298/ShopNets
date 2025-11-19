<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php'; // BASE_URL

$db = (new Database())->getConnection();
$categories = getCategories($db);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính Sách Bảo Hành - TechShop</title>

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
        --bg-card: #ffffff;
        --radius: 18px;
        --transition: all 0.3s ease;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Roboto', sans-serif; }
    body { 
        background: #f1f5f9; 
        color: #1e293b; 
        line-height: 1.8; 
        padding-top: 136px; 
        font-size: 1.7rem; /* TĂNG 13% */
        font-weight: 400;
    }
    .container { max-width: 1200px; }

    /* ============================= */
    /* HERO */
    /* ============================= */
    .warranty-hero {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 5rem 0;
        text-align: center;
        border-radius: 24px;
        margin: 2rem 0 3.5rem;
        position: relative;
        overflow: hidden;
    }
    .warranty-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, rgba(255,255,255,0.25), transparent 70%);
        opacity: 0.6;
    }
    .hero-title {
        font-size: 3.5rem; /* TĂNG */
        font-weight: 700;
        margin-bottom: 1.2rem;
        text-shadow: 0 3px 10px rgba(0,0,0,0.3);
    }
    .hero-subtitle {
        font-size: 1.8rem; /* TĂNG */
        opacity: 0.92;
        font-weight: 500;
    }

    /* ============================= */
    /* SECTION TITLE */
    /* ============================= */
    .section-title {
        font-size: 2.6rem; /* TĂNG */
        font-weight: 700;
        color: var(--dark-color);
        text-align: center;
        margin-bottom: 1.5rem;
        position: relative;
    }
    .section-title::after {
        content: '';
        width: 100px;
        height: 5px;
        background: var(--primary-color);
        display: block;
        margin: 1.2rem auto;
        border-radius: 3px;
    }
    .section-subtitle {
        text-align: center;
        font-size: 1.7rem; /* TĂNG */
        color: #444;
        margin-bottom: 3.5rem;
        font-weight: 500;
    }

    /* ============================= */
    /* POLICY CARD */
    /* ============================= */
    .policy-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 3rem 2.5rem;
        text-align: center;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .policy-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    }
    .policy-card:hover {
        transform: translateY(-10px);
        box-shadow: var(--shadow-lg);
    }
    .policy-icon {
        width: 85px; height: 85px; /* TĂNG */
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.8rem;
        font-size: 2.2rem; /* TĂNG */
        box-shadow: var(--glow);
    }
    .policy-card h3 {
        font-size: 2rem; /* TĂNG */
        font-weight: 700;
        margin-bottom: 1.2rem;
        color: var(--dark-color);
    }
    .policy-card p {
        font-size: 1.6rem; /* TĂNG */
        color: #444;
        line-height: 1.8;
    }

    /* ============================= */
    /* PERIOD CARD */
    /* ============================= */
    .period-card {
        background: var(--bg-card);
        border-radius: var(--radius);
        padding: 2.5rem 2rem;
        text-align: center;
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        transition: var(--transition);
    }
    .period-card:hover {
        transform: translateY(-10px);
        border-color: var(--primary-color);
        box-shadow: var(--shadow-lg);
    }
    .period-icon {
        width: 75px; height: 75px; /* TĂNG */
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem;
        font-size: 1.9rem;
    }
    .period-time {
        font-size: 2.5rem; /* TĂNG */
        font-weight: 700;
        color: var(--primary-color);
        margin: 0.8rem 0;
    }
    .period-card h5 {
        font-size: 1.8rem; /* TĂNG */
        font-weight: 600;
        color: var(--dark-color);
    }
    .period-card p {
        font-size: 1.5rem; /* TĂNG */
        color: #555;
    }

    /* ============================= */
    /* CONDITION */
    /* ============================= */
    .condition-item {
        display: flex;
        align-items: flex-start;
        background: var(--bg-card);
        padding: 1.8rem;
        border-radius: 14px;
        box-shadow: var(--shadow);
        margin-bottom: 1.8rem;
        border: 1px solid var(--border-color);
    }
    .condition-icon {
        width: 58px; height: 58px; /* TĂNG */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.2rem;
        flex-shrink: 0;
        font-size: 1.5rem;
        color: white;
    }
    .condition-item h5 {
        margin: 0 0 0.6rem;
        font-weight: 600;
        font-size: 1.7rem; /* TĂNG */
        color: var(--dark-color);
    }
    .condition-item p {
        margin: 0;
        color: #444;
        font-size: 1.6rem; /* TĂNG */
    }

    /* ============================= */
    /* PROCESS STEPS */
    /* ============================= */
    .process-steps {
        position: relative;
        padding: 4rem 0;
    }
    .process-steps::before {
        content: '';
        position: absolute;
        top: 0; bottom: 0;
        left: 50%;
        width: 5px;
        background: var(--primary-color);
        transform: translateX(-50%);
        border-radius: 3px;
    }
    .process-step {
        display: flex;
        margin-bottom: 3.5rem;
        position: relative;
    }
    .process-step:nth-child(even) {
        flex-direction: row-reverse;
        text-align: right;
    }
    .process-marker {
        width: 52px; height: 52px; /* TĂNG */
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.6rem;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        z-index: 2;
        border: 5px solid white;
        box: 0 0 0 3px var(--primary-color);
    }
    .process-content {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        width: 45%;
        margin-left: 2.5rem;
        border: 1px solid var(--border-color);
    }
    .process-step:nth-child(even) .process-content {
        margin-left: 0;
        margin-right: 2.5rem;
    }
    .process-content h5 {
        margin: 0 0 0.8rem;
        font-weight: 600;
        font-size: 1.8rem; /* TĂNG */
        color: var(--dark-color);
    }
    .process-content p {
        margin: 0;
        color: #444;
        font-size: 1.6rem; /* TĂNG */
    }

    /* ============================= */
    /* CONTACT SECTION */
    /* ============================= */
    .contact-section {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 5rem 0;
        border-radius: 24px;
        margin: 3.5rem 0;
        text-align: center;
    }
    .contact-section .section-title {
        color: white;
        font-size: 2.8rem; /* TĂNG */
    }
    .contact-section .section-title::after {
        background: white;
        height: 5px;
    }
    .contact-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.2rem;
        margin: 2rem 0;
        font-size: 1.7rem;
    }
    .contact-info i {
        font-size: 2.4rem;
        color: #fbbf24;
    }
    .contact-info strong {
        font-weight: 600;
        font-size: 1.8rem;
    }
    .contact-section .btn-warning {
        background: #fbbf24;
        color: #1e293b;
        border: none;
        border-radius: 50px;
        padding: 1rem 2.5rem;
        font-weight: 600;
        font-size: 1.7rem; /* TĂNG */
        margin-top: 1.5rem;
    }
    .contact-section .btn-warning:hover {
        background: #f59e0b;
        transform: translateY(-3px);
        box-shadow: var(--glow);
    }

    /* ============================= */
    /* BACK TO TOP */
    /* ============================= */
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 56px; height: 56px; /* TĂNG */
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 1.6rem;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow);
        z-index: 1000;
        transition: var(--transition);
    }
    .back-to-top.show { display: flex; }
    .back-to-top:hover { 
        background: var(--primary-dark); 
        transform: translateY(-4px);
    }

    /* ============================= */
    /* RESPONSIVE */
    /* ============================= */
    @media (max-width: 992px) {
        .hero-title { font-size: 2.8rem; }
        .process-steps::before { left: 35px; }
        .process-step, .process-step:nth-child(even) {
            flex-direction: row !important;
            text-align: left;
        }
        .process-content { 
            width: calc(100% - 90px); 
            margin-left: 70px !important; 
            margin-right: 0 !important; 
        }
        .process-marker { left: 35px; }
    }
    @media (max-width: 768px) {
        body { font-size: 1.6rem; }
        .hero-title { font-size: 2.5rem; }
        .section-title { font-size: 2.2rem; }
        .policy-card, .period-card { padding: 2rem; }
        .condition-item { flex-direction: column; text-align: center; }
        .condition-icon { margin: 0 0 1.2rem; }
        .contact-info { flex-direction: column; text-align: center; }
    }
</style>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<!-- Hero -->
<section class="warranty-hero">
    <div class="container position-relative">
        <h1 class="hero-title">Chính Sách Bảo Hành</h1>
        <p class="hero-subtitle">Cam kết bảo vệ quyền lợi khách hàng với chế độ bảo hành minh bạch</p>
    </div>
</section>

<!-- Nguyên Tắc -->
<div class="container mb-5">
    <h2 class="section-title">Nguyên Tắc Chung</h2>
    <p class="section-subtitle">Cam kết về chất lượng và dịch vụ bảo hành</p>
    <div class="policy-card">
        <div class="policy-icon"><i class="bi bi-shield-check"></i></div>
        <h3 class="mb-3">Tất cả sản phẩm đều là hàng chính hãng</h3>
        <p class="text-muted">Chúng tôi cam kết mang đến sản phẩm chất lượng với chế độ bảo hành rõ ràng, minh bạch. Mọi sản phẩm đều được kiểm tra kỹ lưỡng trước khi giao đến tay khách hàng.</p>
    </div>
</div>

<!-- Thời Gian -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="section-title">Thời Gian Bảo Hành</h2>
        <p class="section-subtitle">Thời hạn bảo hành theo từng loại sản phẩm</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="period-card">
                    <div class="period-icon"><i class="bi bi-phone"></i></div>
                    <div class="period-time">12 Tháng</div>
                    <h5>Điện thoại & Thiết bị</h5>
                    <p class="text-muted small">Laptop, tablet, smartwatch...</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="period-card">
                    <div class="period-icon"><i class="bi bi-headset"></i></div>
                    <div class="period-time">6 Tháng</div>
                    <h5>Phụ kiện</h5>
                    <p class="text-muted small">Tai nghe, sạc, ốp lưng...</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="period-card">
                    <div class="period-icon"><i class="bi bi-tag"></i></div>
                    <div class="period-time">Theo SP</div>
                    <h5>Sản phẩm khác</h5>
                    <p class="text-muted small">Xem chi tiết sản phẩm</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Điều Kiện -->
<div class="container my-5">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <h2 class="section-title">Được Bảo Hành</h2>
            <div class="condition-item condition-positive">
                <div class="condition-icon"><i class="bi bi-check"></i></div>
                <div><h5>Còn tem & hóa đơn</h5><p>Tem nguyên vẹn, hóa đơn rõ ràng</p></div>
            </div>
            <div class="condition-item condition-positive">
                <div class="condition-icon"><i class="bi bi-check"></i></div>
                <div><h5>Không lỗi người dùng</h5><p>Không rơi vỡ, vào nước</p></div>
            </div>
            <div class="condition-item condition-positive">
                <div class="condition-icon"><i class="bi bi-check"></i></div>
                <div><h5>Tại trung tâm ủy quyền</h5><p>Gửi đúng địa chỉ bảo hành</p></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <h2 class="section-title">Không Bảo Hành</h2>
            <div class="condition-item condition-negative">
                <div class="condition-icon"><i class="bi bi-x"></i></div>
                <div><h5>Tự ý sửa chữa</h5><p>Tháo lắp không ủy quyền</p></div>
            </div>
            <div class="condition-item condition-negative">
                <div class="condition-icon"><i class="bi bi-x"></i></div>
                <div><h5>Hư hỏng do thiên tai</h5><p>Ngập nước, sét đánh...</p></div>
            </div>
            <div class="condition-item condition-negative">
                <div class="condition-icon"><i class="bi bi-x"></i></div>
                <div><h5>Hết hạn bảo hành</h5><p>Vượt quá thời hạn quy định</p></div>
            </div>
        </div>
    </div>
</div>

<!-- Quy Trình -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="section-title">Quy Trình Bảo Hành</h2>
        <p class="section-subtitle">4 bước đơn giản để yêu cầu bảo hành</p>
        <div class="process-steps">
            <div class="process-step"><div class="process-marker">1</div><div class="process-content"><h5>Liên hệ hỗ trợ</h5><p>Gọi 1900 1234 hoặc email warranty@techshop.vn</p></div></div>
            <div class="process-step"><div class="process-marker">2</div><div class="process-content"><h5>Xác nhận đơn hàng</h5><p>Kiểm tra thông tin & tình trạng bảo hành</p></div></div>
            <div class="process-step"><div class="process-marker">3</div><div class="process-content"><h5>Gửi sản phẩm</h5><p>Đóng gói kèm hóa đơn, gửi về trung tâm</p></div></div>
            <div class="process-step"><div class="process-marker">4</div><div class="process-content"><h5>Nhận lại sản phẩm</h5><p>Sửa chữa/thay thế trong 7-14 ngày</p></div></div>
        </div>
    </div>
</section>

<!-- Liên Hệ -->
<section class="contact-section">
    <div class="container">
        <h2 class="section-title">Cần Hỗ Trợ Bảo Hành?</h2>
        <p class="mb-4">Đội ngũ luôn sẵn sàng hỗ trợ 24/7</p>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="contact-info">
                    <i class="bi bi-telephone-fill"></i>
                    <div>
                        <strong>1900 1234</strong><br>
                        <small>Hotline bảo hành</small>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="contact-info">
                    <i class="bi bi-envelope-fill"></i>
                    <div>
                        <strong>warranty@techshop.vn</strong><br>
                        <small>Email hỗ trợ</small>
                    </div>
                </div>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>pages/help/contact.php" class="btn btn-warning btn-lg">
            <i class="bi bi-headset"></i> Liên Hệ Ngay
        </a>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>

<!-- Back to top -->
<button class="back-to-top" id="backToTop">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(window).scroll(() => $('#backToTop').toggleClass('show', $(window).scrollTop() > 300));
    $('#backToTop').click(() => $('html, body').animate({scrollTop: 0}, 600));
</script>

</body>
</html>