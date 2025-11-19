<?php
session_start();
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

$db = (new Database())->getConnection();

// === LẤY SẢN PHẨM GIẢM GIÁ ===
$flash_products = $db->query("
    SELECT p.*, pi.image_path, 
           (p.compare_price - p.price) / p.compare_price * 100 AS discount_percent
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.is_active = 1 
      AND p.compare_price > p.price 
      AND p.compare_price IS NOT NULL
    ORDER BY discount_percent DESC 
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$categories = getCategories($db);
$brands = getBrands($db);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khuyến mãi HOT - SHOPNETS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../../assets/css/styles.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #f59e0b, #eab308);
            color: white;
            padding: 12px 0;
            text-align: center;
            position: relative;
            z-index: 1025;
            transition: all 0.3s ease;
        }
        .welcome-banner .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            font-weight: 600;
        }
        .btn-welcome {
            background: white;
            color: #f59e0b;
            border: none;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-welcome:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .close-banner {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        /* Flash Sale */
        .flash-sale-section {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 15px;
            overflow: hidden;
            margin: 2rem 0;
        }
        .flash-sale-header {
            padding: 2rem;
            text-align: center;
        }
        .flash-sale-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .countdown-item {
            background: rgba(255,255,255,0.2);
            padding: 0.8rem 1rem;
            border-radius: 10px;
            min-width: 70px;
            text-align: center;
        }
        .countdown-number {
            font-size: 1.8rem;
            font-weight: 700;
            display: block;
        }
        .countdown-label {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .flash-product-card {
            background: white;
            color: #333;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease;
            height: 100%;
        }
        .flash-product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .flash-product-img {
            height: 180px;
            object-fit: cover;
        }
        .flash-discount {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #ef4444;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
        }

        /* Referral */
        .referral-section {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            margin: 2rem 0;
        }
        .referral-steps {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        .referral-step {
            background: rgba(255,255,255,0.2);
            padding: 1.5rem;
            border-radius: 12px;
            min-width: 180px;
        }
        .step-number {
            background: white;
            color: #10b981;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 1rem;
        }
        .referral-link {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 8px;
            font-family: monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <!-- Welcome Banner -->
    <div class="welcome-banner" id="welcomeBanner">
        <div class="container">
            <div>Chào mừng bạn đến với SHOPNETS! Đăng ký ngay để nhận <strong>10% giảm giá</strong> đơn đầu tiên.</div>
            <a href="../auth/register.php" class="btn-welcome">Đăng ký ngay</a>
            <button class="close-banner" onclick="closeWelcomeBanner()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Flash Sale Section -->
    <section class="flash-sale-section">
        <div class="container">
            <div class="flash-sale-header">
                <h2 class="flash-sale-title">FLASH SALE</h2>
                <p class="flash-sale-subtitle">Giảm giá sập sàn – Chỉ trong <strong id="hoursLeft"></strong> giờ nữa!</p>

                <div class="countdown-timer" id="countdownTimer">
                    <div class="countdown-item">
                        <span class="countdown-number" id="hours">00</span>
                        <span class="countdown-label">Giờ</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="minutes">00</span>
                        <span class="countdown-label">Phút</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Giây</span>
                    </div>
                </div>

                <a href="products.php?sort=discount" class="btn btn-warning btn-lg">
                    <i class="fas fa-bolt me-2"></i>MUA NGAY
                </a>
            </div>

            <?php if ($flash_products): ?>
            <div class="flash-sale-products px-3 pb-4">
                <div class="row g-3">
                    <?php foreach ($flash_products as $p): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="../main/product-detail.php?id=<?php echo $p['id']; ?>" class="text-decoration-none">
                            <div class="flash-product-card position-relative">
                                <div class="flash-discount">
                                    -<?php echo round($p['discount_percent']); ?>%
                                </div>
                                <img src="<?php echo getProductImage($p['image_path']); ?>" 
                                     class="flash-product-img w-100" 
                                     alt="<?php echo htmlspecialchars($p['name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x300/64748b/ffffff?text=No+Image'">
                                <div class="p-3">
                                    <h6 class="mb-1 text-dark" style="font-size:0.9rem; line-height:1.3;">
                                        <?php echo htmlspecialchars($p['name']); ?>
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-danger fw-bold">
                                            <?php echo number_format($p['price'], 0, ',', '.'); ?>đ
                                        </span>
                                        <?php if ($p['compare_price'] > $p['price']): ?>
                                            <small class="text-muted text-decoration-line-through">
                                                <?php echo number_format($p['compare_price'], 0, ',', '.'); ?>đ
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="mb-0">Hiện tại chưa có sản phẩm giảm giá</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Referral Section -->
    <section class="referral-section">
        <div class="container">
            <h2 class="referral-title">Rủ bạn – Cùng nhận quà!</h2>
            <p class="referral-description">
                Mời bạn bè mua sắm tại SHOPNETS – Cả hai nhận <strong>50.000₫</strong> vào tài khoản!
            </p>

            <div class="referral-steps">
                <div class="referral-step">
                    <div class="step-number">1</div>
                    <h5>Đăng nhập</h5>
                    <p>Đăng nhập tài khoản SHOPNETS</p>
                </div>
                <div class="referral-step">
                    <div class="step-number">2</div>
                    <h5>Chia sẻ link</h5>
                    <p>Gửi link giới thiệu cho bạn</p>
                </div>
                <div class="referral-step">
                    <div class="step-number">3</div>
                    <h5>Nhận thưởng</h5>
                    <p>Cả hai nhận 50K khi mua hàng</p>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="text-center">
                    <p class="mb-2">Link giới thiệu của bạn:</p>
                    <div class="referral-link p-2 mb-3">
                        https://SHOPNETS.com/ref/<?php echo $_SESSION['user_id']; ?>
                    </div>
                    <button class="btn btn-warning" onclick="copyReferralLink()">
                        <i class="fas fa-copy me-2"></i>Sao chép link
                    </button>
                </div>
            <?php else: ?>
                <p class="mb-0">Đăng nhập để nhận link giới thiệu!</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/back-to-top.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // === Welcome Banner ===
        function closeWelcomeBanner() {
            $('#welcomeBanner').slideUp(300);
            localStorage.setItem('SHOPNETS_welcome_closed', 'true');
        }

        // === Flash Sale Countdown ===
        const flashSaleEnd = new Date();
        flashSaleEnd.setHours(24, 0, 0, 0); // 00:00 ngày mai

        function updateCountdown() {
            const now = new Date();
            const diff = flashSaleEnd - now;

            if (diff <= 0) {
                $('#countdownTimer').html('<div class="text-center w-100 text-warning">ĐÃ KẾT THÚC</div>');
                return;
            }

            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);

            $('#hours').text(h.toString().padStart(2, '0'));
            $('#minutes').text(m.toString().padStart(2, '0'));
            $('#seconds').text(s.toString().padStart(2, '0'));
            $('#hoursLeft').text(h);
        }

        // === Copy Referral Link ===
        function copyReferralLink() {
            const link = 'https://SHOPNETS.com/ref/<?php echo $_SESSION['user_id'] ?? ''; ?>';
            navigator.clipboard.writeText(link).then(() => {
                alert('Đã sao chép link giới thiệu!');
            }).catch(() => {
                prompt('Sao chép link:', link);
            });
        }

        // === DOM Loaded ===
        $(function() {
            if (localStorage.getItem('SHOPNETS_welcome_closed') === 'true') {
                $('#welcomeBanner').hide();
            }
            <?php if (isset($_SESSION['user_id'])): ?>
                $('#welcomeBanner').hide();
            <?php endif; ?>

            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>