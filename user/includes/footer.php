<?php 
// Đảm bảo BASE_URL đã được định nghĩa (nếu chưa thì include config)
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/config.php';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <title>TechShop - Footer</title>
</head>

<style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1d4ed8;
        --dark-color: #1e293b;
        --text-light: #f8fafc;
        --text-color: #333;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Inter', 'Roboto', sans-serif;
    }

    html {
        font-size: 62.5%;
    }

    /* Responsive Grid */
    .grid {
        width: 1200px;
        max-width: 100%;
        margin: 0 auto;
    }

    .grid__row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -12px;
        margin-right: -12px;
    }

    .grid__column-3 {
        padding-left: 12px;
        padding-right: 12px;
        flex: 0 0 25%;
        max-width: 25%;
    }

    @media (max-width: 992px) {
        .grid__column-3 { flex: 0 0 50%; max-width: 50%; }
    }

    @media (max-width: 576px) {
        .grid__column-3 { flex: 0 0 100%; max-width: 100%; }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* FOOTER */
    .footer {
        background: #f8f9fa;
        border-top: 3px solid var(--primary-color);
        margin-top: 40px;
        padding-top: 40px;
        font-size: 1.4rem;
        color: var(--text-color);
    }

    .footer__top {
        padding: 30px 0;
        animation: fadeIn 0.5s ease;
    }

    .footer__section {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--shadow);
        height: 100%;
        transition: transform 0.3s ease;
    }

    .footer__section:hover {
        transform: translateY(-4px);
    }

    .footer__title {
        font-size: 1.6rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer__title i {
        color: var(--primary-color);
    }

    /* Vận chuyển */
    .shipping-logos {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }

    .shipping-logo {
        width: 60px;
        height: 60px;
        object-fit: contain;
        padding: 8px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .shipping-logo:hover {
        transform: scale(1.1);
    }

    /* Thanh toán */
    .payment-list {
        list-style: none;
        padding: 0;
    }

    .payment-item {
        padding: 6px 0;
        color: #555;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .payment-item i {
        color: #10b981;
        font-size: 1.2rem;
    }

    /* Mạng xã hội */
    .social-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .social-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        background: #f1f5f9;
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-color);
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: var(--primary-color);
        color: white;
        transform: translateX(4px);
    }

    .social-link img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }

    .social-link:hover img {
        filter: brightness(0) invert(1);
    }

    /* App */
    .app-download {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 16px;
    }

    .qr-code {
        width: 90px;
        height: 90px;
        padding: 6px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .app-stores {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .app-store-img {
        width: 110px;
        height: auto;
        border-radius: 8px;
        transition: transform 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .app-store-img:hover {
        transform: scale(1.05);
    }

    /* Footer Bottom */
    .footer__bottom {
        background: white;
        color: var(--text-light);
        padding: 20px 0;
        margin-top: 40px;
        font-size: 1.3rem;
    }

    .footer__bottom .grid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
    }

    .footer__links {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .footer__link {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer__link:hover {
        color: var(--primary-color);
        text-decoration: underline;
    }

    .footer__copyright {
        color: #64748b;
        font-size: 1.2rem;
        margin-top: 8px;
    }
</style>

<!-- Footer Content -->
<footer class="footer">
    <div class="grid">
        <!-- Top Section -->
        <div class="footer__top">
            <div class="grid__row">
                <!-- Đơn vị vận chuyển -->
                <div class="grid__column-3">
                    <div class="footer__section">
                        <h3 class="footer__title"><i class="bi bi-truck"></i> Đơn vị vận chuyển</h3>
                        <div class="shipping-logos">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/ahamove.png" alt="Ahamove" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/be.png" alt="Be" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/giaohangnhanh.png" alt="GHN" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/grap.png" alt="Grab" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/j&t.png" alt="J&T" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/ninjavan.png" alt="Ninja Van" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/spx.png" alt="SPX" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/vietnam-post.png" alt="VN Post" class="shipping-logo">
                            <img src="<?php echo BASE_URL; ?>assets/images/footer/viettel-post.png" alt="Viettel Post" class="shipping-logo">
                        </div>
                    </div>
                </div>

                <!-- Thanh toán -->
                <div class="grid__column-3">
                    <div class="footer__section">
                        <h3 class="footer__title"><i class="bi bi-credit-card"></i> Thanh toán</h3>
                        <ul class="payment-list">
                            <li class="payment-item"><i class="bi bi-check-circle-fill"></i> COD (Trả tiền khi nhận hàng)</li>
                            <li class="payment-item"><i class="bi bi-check-circle-fill"></i> Quét mã QR</li>
                            <li class="payment-item"><i class="bi bi-check-circle-fill"></i> Trả góp 0% lãi suất</li>
                            <li class="payment-item"><i class="bi bi-check-circle-fill"></i> Miễn phí vận chuyển (đơn trước)</li>
                            <li class="payment-item"><i class="bi bi-check-circle-fill"></i> Ưu đãi thành viên VIP</li>
                        </ul>
                    </div>
                </div>

                <!-- Theo dõi -->
                <div class="grid__column-3">
                    <div class="footer__section">
                        <h3 class="footer__title"><i class="bi bi-share-fill"></i> Theo dõi chúng tôi</h3>
                        <div class="social-links">
                            <a href="https://facebook.com" target="_blank" class="social-link">
                                <img src="<?php echo BASE_URL; ?>assets/images/footer/facebook.jpg" alt="Facebook">
                                Facebook
                            </a>
                            <a href="https://instagram.com" target="_blank" class="social-link">
                                <img src="<?php echo BASE_URL; ?>assets/images/footer/instagram.jpg" alt="Instagram">
                                Instagram
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tải app -->
                <div class="grid__column-3">
                    <div class="footer__section">
                        <h3 class="footer__title"><i class="bi bi-phone-fill"></i> Tải ứng dụng</h3>
                        <div class="app-download">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="footer__bottom">
            <div class="grid">
                <p class="footer__text text-dark">© 2025 TechShop. Tất cả quyền được bảo lưu.</p>
                <div class="footer__links">
                    <a href="<?php echo BASE_URL; ?>pages/info/privacy-policy.php" class="footer__link">Chính sách bảo mật</a>
                    <a href="<?php echo BASE_URL; ?>pages/info/terms-of-service.php" class="footer__link">Điều khoản sử dụng</a>
                    <a href="<?php echo BASE_URL; ?>pages/info/warranty.php" class="footer__link">Chính sách bảo hành</a>
                </div>
                <p class="footer__copyright">
                    Địa chỉ: Số 2, đường Võ Oanh, P. Thạnh Mỹ Tây, TP. HCM — 
                    Hotline: <a href="tel:+8412345678" style="color: var(--primary-color);">+84 123 456 78</a> — 
                    Email: <a href="mailto:support@techshop.vn" style="color: var(--primary-color);">support@techshop.vn</a>
                </p>
                <p class="footer__copyright">
                    Giấy phép ĐKKD: 0100123456 — Cấp ngày 12/10/2023
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/cart.js"></script>

</body>
</html>