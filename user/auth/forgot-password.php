<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// XỬ LÝ FORM – KHÔNG CÒN WARNING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅ GIẢI PHÁP CUỐI CÙNG – 100% SẠCH
    $email = array_key_exists('email', $_POST) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';

    if (empty($email)) {
        $error = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ email không hợp lệ.';
    } else {
        try {
            $query = "SELECT id, username, full_name, email FROM users WHERE email = ? AND status = 'active'";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $reset_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Debug log
                error_log("=== DEBUG FORGOT PASSWORD ===");
                error_log("User found: " . ($user['email'] ?? 'unknown'));
                error_log("Token: " . $reset_token);
                
                // Kiểm tra & tạo cột reset_token
                $check_column = $db->query("SHOW COLUMNS FROM users LIKE 'reset_token'")->fetch();
                if (!$check_column) {
                    $db->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL, ADD COLUMN reset_token_expires DATETIME DEFAULT NULL");
                }
                
                // Lưu token
                $update_query = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->execute([$reset_token, $expires_at, $user['id']]);
                
                if ($update_stmt->rowCount() > 0) {
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/shopnets/user/auth/reset-password.php?token=" . urlencode($reset_token);
                    
                    $success = "Liên kết đặt lại mật khẩu đã được gửi đến email của bạn. <br><br>
                            <strong>Liên kết demo:</strong> <a href='$reset_link' class='alert-link' target='_blank'>Nhấn vào đây để đặt lại mật khẩu</a><br><br>
                            <small><em>Lưu ý: Token hết hạn sau 1 giờ.</em></small>";
                } else {
                    $error = 'Có lỗi xảy ra khi tạo yêu cầu đặt lại mật khẩu. Vui lòng thử lại.';
                }
            } else {
                $error = 'Địa chỉ email không tồn tại trong hệ thống.';
            }
        } catch (PDOException $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $error = 'Lỗi hệ thống. Vui lòng thử lại sau.';
        }
    }
}

// ✅ BIẾN CHO INPUT VALUE – KHÔNG CÒN WARNING
$input_email = array_key_exists('email', $_POST) ? $_POST['email'] : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - ShopNets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            font-size: 62.5%;
            line-height: 1.6rem;
            font-family: 'Inter', 'Roboto', sans-serif;
            overflow: hidden;
        }
        body {
            background: 
                linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.9)),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(56, 189, 248, 0.2)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/><g fill="rgba(56, 189, 248, 0.1)"><circle cx="200" cy="150" r="4"/><circle cx="800" cy="300" r="6"/><circle cx="400" cy="700" r="5"/><circle cx="900" cy="600" r="3"/><circle cx="150" cy="800" r="7"/></g></svg>'),
                #0f172a;
            background-size: cover;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Hiệu ứng particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .particle {
            position: absolute;
            background: rgba(56, 189, 248, 0.3);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; }
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --danger: #ef4444;
            --success: #10b981;
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

        /* FORGOT PASSWORD PAGE */
        .forgot-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
            margin-top: 30px;
        }
        .forgot-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 550px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 10px;
        }
        .forgot-side {
            flex: 1;
            background: 
                linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(37, 99, 235, 0.8)),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
            color: white;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .forgot-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,100 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }
        .forgot-side-content {
            position: relative;
            z-index: 1;
        }
        .forgot-side h2 {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            line-height: 1.2;
        }
        .forgot-side p {
            font-size: 1.5rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .features-list {
            list-style: none;
            margin-top: 2rem;
        }
        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 1.4rem;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            backdrop-filter: blur(5px);
            transition: var(--transition);
        }
        .features-list li:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        .features-list i {
            margin-right: 1.2rem;
            font-size: 1.8rem;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .forgot-main {
            flex: 1;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .forgot-header h1 {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .forgot-header p {
            color: var(--gray);
            font-size: 1.4rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: .8rem;
            display: flex;
            align-items: center;
            color: var(--dark);
            font-size: 1.4rem;
        }
        .form-label i { margin-right: .5rem; color: var(--primary); }

        .input-group {
            position: relative;
        }
        .form-control {
            border-radius: var(--radius);
            padding: 1rem 1rem 1rem 4rem;
            border: 1px solid var(--border);
            font-size: 1.4rem;
            background: var(--light);
            transition: var(--transition);
            height: 48px;
            width: 100%;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            background: white;
        }
        .input-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .btn-forgot {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--radius);
            padding: 1.2rem;
            font-weight: 600;
            font-size: 1.5rem;
            width: 100%;
            transition: var(--transition);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        .btn-forgot:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .links {
            text-align: center;
            font-size: 1.4rem;
            margin-top: 1.6rem;
        }
        .links a {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
            color: var(--primary-dark);
        }

        .alert {
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 1.8rem;
            display: flex;
            align-items: center;
            font-size: 1.4rem;
            border: none;
        }
        .alert-danger { background: rgba(239,68,68,.1); color: var(--danger); }
        .alert-success { background: rgba(16,185,129,.1); color: var(--success); }
        .alert i { margin-right: 1rem; }

        .instructions {
            background: rgba(59, 130, 246, 0.05);
            border-radius: var(--radius);
            padding: 1.2rem;
            margin-bottom: 1.8rem;
            border-left: 4px solid var(--primary);
        }
        .instructions h5 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
        }
        .instructions p {
            color: var(--gray);
            font-size: 1.3rem;
            margin-bottom: 0;
        }

        /* LOGO */
        .site-logo {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 8px 12px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: var(--transition);
            font-size: 1.4rem;
            line-height: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .site-logo:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: white;
        }
        .site-logo .logo-img {
            height: 28px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .site-logo span {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.6rem;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .forgot-side {
                display: none;
            }
            .forgot-container {
                max-width: 500px;
                height: 500px;
            }
        }
        @media (max-width: 576px) {
            .site-logo {
                top: 15px;
                left: 15px;
                padding: 6px 10px;
            }
            .site-logo .logo-img {
                height: 24px;
            }
            .site-logo span {
                font-size: 1.4rem;
            }
            .forgot-main {
                padding: 2rem;
            }
            .forgot-container {
                height: auto;
                max-height: 85vh;
                margin-top: 20px;
            }
            .forgot-wrapper {
                margin-top: 20px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Hiệu ứng particles -->
    <div class="particles" id="particles"></div>

    <!-- LOGO -->
    <a href="../index.php" class="site-logo">
        <img src="../assets/images/logo.png" alt="ShopNets Logo" class="logo-img">
        <span>ShopNets</span>
    </a>

    <div class="forgot-wrapper">
        <div class="forgot-container">
            <div class="forgot-side">
                <div class="forgot-side-content">
                    <h2>Khôi Phục Quyền Truy Cập</h2>
                    <p>Chúng tôi hiểu rằng việc quên mật khẩu có thể gây bất tiện. Hãy để chúng tôi giúp bạn lấy lại quyền truy cập vào tài khoản một cách an toàn.</p>
                    
                    <ul class="features-list">
                        <li>
                            <i class="bi bi-shield-check"></i>
                            <span>Bảo mật tuyệt đối thông tin tài khoản</span>
                        </li>
                        <li>
                            <i class="bi bi-clock"></i>
                            <span>Liên kết đặt lại có hiệu lực trong 1 giờ</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope-check"></i>
                            <span>Hướng dẫn chi tiết qua email</span>
                        </li>
                        <li>
                            <i class="bi bi-headset"></i>
                            <span>Hỗ trợ 24/7 nếu gặp sự cố</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="forgot-main">
                <div class="forgot-header">
                    <h1>Quên Mật Khẩu</h1>
                    <p>Nhập email để nhận liên kết đặt lại mật khẩu</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!$error && !$success): ?>
                    <div class="instructions">
                        <h5><i class="bi bi-info-circle me-2"></i>Hướng dẫn</h5>
                        <p>Nhập địa chỉ email bạn đã sử dụng để đăng ký tài khoản. Chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="forgotForm">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i>Địa chỉ email *
                        </label>
                        <div class="input-group">
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($input_email); ?>"
                                   placeholder="Nhập địa chỉ email của bạn" required>
                            <i class="bi bi-envelope input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-forgot">
                        <i class="bi bi-send me-2"></i>Gửi Liên Kết Đặt Lại
                    </button>

                    <div class="links">
                        <p>Nhớ mật khẩu? <a href="login.php">Quay lại đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tạo hiệu ứng particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 15;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 5 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100 + 100}%`;
                
                const duration = Math.random() * 20 + 15;
                particle.style.animationDuration = `${duration}s`;
                
                const delay = Math.random() * 5;
                particle.style.animationDelay = `${delay}s`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Focus icon effect
        document.querySelectorAll('.form-control').forEach(input => {
            const icon = input.parentElement.querySelector('.input-icon');
            input.addEventListener('focus', () => {
                icon.style.color = 'var(--primary)';
                input.style.borderColor = 'var(--primary)';
            });
            input.addEventListener('blur', () => {
                icon.style.color = 'var(--gray)';
                input.style.borderColor = '#e2e8f0';
            });
        });

        // Auto focus email input
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('email').focus();
            createParticles();
        });

        // Client-side validation
        document.getElementById('forgotForm').addEventListener('submit', e => {
            const email = document.getElementById('email').value.trim();
            if (!email) {
                e.preventDefault();
                alert('Vui lòng nhập địa chỉ email!');
                document.getElementById('email').focus();
                return false;
            }
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Địa chỉ email không hợp lệ!');
                document.getElementById('email').focus();
                return false;
            }
        });

        // Ngăn cuộn trang
        document.body.style.overflow = 'hidden';
    </script>
</body>
</html>