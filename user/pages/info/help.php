<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/config.php'; // BASE_URL

$database = new Database();
$db = $database->getConnection();

$categories = getCategories($db);
$brands = getBrands($db);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm hỗ trợ - TechShop</title>

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
            --border: #e2e8f0;
            --radius: 16px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', 'Roboto', sans-serif; }
        body { 
            background: #f1f5f9; 
            color: #1e293b; 
            line-height: 1.7; 
            padding-top: 136px; 
            font-size: 1.5rem;
        }
        .container { max-width: 1200px; }

        /* ============================= */
        /* BREADCRUMB */
        /* ============================= */
        .breadcrumb-section {
            background: white;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            font-size: 1.75rem;
        }
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        .breadcrumb-item a:hover { color: var(--primary-dark); text-decoration: underline; }
        .breadcrumb-item.active { color: var(--dark-color); font-weight: 600; }

        /* ============================= */
        /* HERO */
        /* ============================= */
        .support-hero {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 4rem 0;
            text-align: center;
            border-radius: 20px;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        .support-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.2), transparent 70%);
            opacity: 0.6;
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .hero-subtitle {
            font-size: 1.6rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .hero-actions .btn {
            border-radius: 50px;
            padding: 0.9rem 2rem;
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0 0.5rem;
            transition: var(--transition);
        }
        .btn-warning {
            background: #fbbf24;
            color: #1e293b;
            border: none;
        }
        .btn-warning:hover {
            background: #f59e0b;
            transform: translateY(-3px);
            box-shadow: var(--glow);
        }
        .btn-outline-light {
            border: 2px solid white;
            color: white;
        }
        .btn-outline-light:hover {
            background: white;
            color: var(--primary-color);
        }

        /* ============================= */
        /* SUPPORT CARDS */
        /* ============================= */
        .support-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            height: 100%;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }
        .support-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }
        .support-icon {
            width: 70px; height: 70px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            box-shadow: var(--glow);
        }
        .support-card h5 {
            font-size: 1.7rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }
        .support-card p {
            font-size: 1.4rem;
            color: #555;
            margin-bottom: 1.5rem;
        }
        .support-card .btn {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            font-size: 1.4rem;
        }
        .support-card .btn:hover {
            background: var(--primary-color);
            color: white;
        }

        /* ============================= */
        /* FAQ */
        /* ============================= */
        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 1rem;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            display: block;
            margin: 1rem auto;
            border-radius: 2px;
        }
        .section-subtitle {
            text-align: center;
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 3rem;
        }

        .faq-item {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .faq-question {
            background: #f8fafc;
            border: none;
            padding: 1.5rem;
            font-weight: 600;
            text-align: left;
            width: 100%;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--dark-color);
            transition: var(--transition);
        }
        .faq-question i { color: var(--primary-color); }
        .faq-question:hover { background: #eff6ff; color: var(--primary-color); }
        .faq-answer {
            padding: 1.5rem;
            background: white;
            font-size: 1.5rem;
            line-height: 1.8;
            color: #444;
            border-top: 1px dashed var(--border-color);
        }

        /* ============================= */
        /* CONTACT FORM */
        /* ============================= */
        .support-form {
            background: white;
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .support-form h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.5rem;
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 0.8rem 1rem;
            font-size: 1.5rem;
            border: 1px solid var(--border-color);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.15);
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 50px;
            padding: 0.9rem 2.5rem;
            font-weight: 600;
            font-size: 1.5rem;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--glow);
        }

        /* ============================= */
        /* RESPONSIVE */
        /* ============================= */
        @media (max-width: 992px) {
            .hero-title { font-size: 2.5rem; }
            .hero-subtitle { font-size: 1.5rem; }
        }
        @media (max-width: 768px) {
            body { font-size: 1.4rem; }
            .hero-actions .btn { display: block; width: 100%; margin: 0.5rem 0; }
            .support-card { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<?php include '../../includes/header.php'; ?>

<!-- Breadcrumb -->
<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active">Trung tâm hỗ trợ</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Hero -->
<section class="support-hero">
    <div class="container position-relative">
        <h1 class="hero-title">Trung tâm hỗ trợ</h1>
        <p class="hero-subtitle">Chúng tôi luôn sẵn sàng giúp bạn giải đáp mọi thắc mắc</p>
        <div class="hero-actions">
            <a href="#faq" class="btn btn-warning">FAQ</a>
            <a href="#contact" class="btn btn-outline-light">Liên hệ ngay</a>
        </div>
    </div>
</section>

<!-- Quick Links -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="support-card">
                <div class="support-icon"><i class="bi bi-cart-plus"></i></div>
                <h5>Đặt hàng & Thanh toán</h5>
                <p>Hướng dẫn đặt hàng và thanh toán an toàn</p>
                <a href="#ordering" class="btn">Xem ngay</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="support-card">
                <div class="support-icon"><i class="bi bi-arrow-repeat"></i></div>
                <h5>Đổi trả & Bảo hành</h5>
                <p>Chính sách đổi trả trong 7 ngày</p>
                <a href="#warranty" class="btn">Xem ngay</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="support-card">
                <div class="support-icon"><i class="bi bi-truck"></i></div>
                <h5>Vận chuyển</h5>
                <p>Giao hàng nhanh toàn quốc</p>
                <a href="#shipping" class="btn">Xem ngay</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="support-card">
                <div class="support-icon"><i class="bi bi-headset"></i></div>
                <h5>Hỗ trợ kỹ thuật</h5>
                <p>Hỗ trợ 24/7 qua hotline & chat</p>
                <a href="#technical" class="btn">Xem ngay</a>
            </div>
        </div>
    </div>
</div>

<!-- FAQ -->
<section id="faq" class="container mb-5">
    <h2 class="section-title">Câu hỏi thường gặp</h2>
    <p class="section-subtitle">Giải đáp nhanh các thắc mắc phổ biến</p>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="faq-item">
                <button class="faq-question collapsed" data-bs-toggle="collapse" data-bs-target="#faq1">
                    Làm thế nào để đặt hàng?
                </button>
                <div id="faq1" class="collapse faq-answer">
                    1. Chọn sản phẩm → "Thêm vào giỏ"<br>
                    2. Vào giỏ hàng → "Thanh toán"<br>
                    3. Điền thông tin → Xác nhận đơn
                </div>
            </div>
            <!-- Thêm các FAQ khác -->
        </div>
    </div>
</section>

<!-- Contact Form -->
<section id="contact" class="container">
    <div class="support-form">
        <h3>Gửi yêu cầu hỗ trợ</h3>
        <form id="supportForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Họ tên</label>
                    <input type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Chủ đề</label>
                    <select class="form-select">
                        <option>Đặt hàng</option>
                        <option>Thanh toán</option>
                        <option>Vận chuyển</option>
                        <option>Bảo hành</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Nội dung</label>
                    <textarea class="form-control" rows="4" required></textarea>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
<?php include '../../includes/back-to-top.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('supportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Cảm ơn bạn! Yêu cầu đã được gửi thành công.');
        this.reset();
    });
</script>

</body>
</html>