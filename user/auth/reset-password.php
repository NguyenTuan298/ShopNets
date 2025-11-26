<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';
$valid_token = false;
$token = '';

// Kiểm tra token
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    error_log("=== DEBUG RESET PASSWORD ===");
    error_log("Token from URL: " . $token);
}

if (!empty($token)) {
    try {
        $debug_query = "SELECT id, reset_token, reset_token_expires, email FROM users WHERE reset_token = ?";
        $debug_stmt = $db->prepare($debug_query);
        $debug_stmt->execute([$token]);
        $debug_user = $debug_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($debug_user) {
            error_log("User found: " . ($debug_user['email'] ?? 'unknown'));
            error_log("Token in DB: " . $debug_user['reset_token']);
            error_log("Expires: " . $debug_user['reset_token_expires']);

            $current_time = time();
            $expire_time = strtotime($debug_user['reset_token_expires']);

            if (hash_equals($debug_user['reset_token'], $token) && $expire_time > $current_time) {
                error_log("Token VALID");
                $valid_token = true;
                $user = $debug_user;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $password = $_POST['password'] ?? '';
                    $confirm_password = $_POST['confirm_password'] ?? '';
                    
                    if (empty($password) || empty($confirm_password)) {
                        $error = 'Vui lòng điền đầy đủ thông tin.';
                    } elseif ($password !== $confirm_password) {
                        $error = 'Mật khẩu xác nhận không khớp.';
                    } elseif (strlen($password) < 6) {
                        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
                        $update_stmt = $db->prepare($update_query);
                        
                        if ($update_stmt->execute([$hashed_password, $user['id']])) {
                            $success = 'Mật khẩu đã được đặt lại thành công! <a href="login.php" class="alert-link">Đăng nhập ngay</a>';
                            $valid_token = false;
                        } else {
                            $error = 'Có lỗi xảy ra khi đặt lại mật khẩu. Vui lòng thử lại.';
                        }
                    }
                }
            } else {
                $error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            }
        } else {
            $error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $error = 'Lỗi hệ thống. Vui lòng thử lại sau.';
    }
} else {
    $error = 'Liên kết đặt lại mật khẩu không hợp lệ.';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - ShopNets</title>
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

        /* RESET PASSWORD PAGE */
        .reset-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
            margin-top: 30px;
        }
        .reset-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 600px;
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
        .reset-side {
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
        .reset-side::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,100 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
            background-size: cover;
        }
        .reset-side-content {
            position: relative;
            z-index: 1;
        }
        .reset-side h2 {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            line-height: 1.2;
        }
        .reset-side p {
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

        .reset-main {
            flex: 1;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .reset-header h1 {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .reset-header p {
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
        .password-toggle {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1.5rem;
            transition: var(--transition);
        }
        .password-toggle:hover { color: var(--primary); }

        .btn-reset {
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
        .btn-reset:hover {
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

        .password-strength {
            height: 6px;
            border-radius: 3px;
            margin-top: .6rem;
            background: #e2e8f0;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .password-strength-text {
            font-size: 1.25rem;
            margin-top: .4rem;
            text-align: right;
            font-weight: 500;
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
            .reset-side {
                display: none;
            }
            .reset-container {
                max-width: 500px;
                height: 550px;
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
            .reset-main {
                padding: 2rem;
            }
            .reset-container {
                height: auto;
                max-height: 85vh;
                margin-top: 20px;
            }
            .reset-wrapper {
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

    <div class="reset-wrapper">
        <div class="reset-container">
            <div class="reset-side">
                <div class="reset-side-content">
                    <h2>Bảo Mật Tài Khoản</h2>
                    <p>Tạo mật khẩu mới mạnh mẽ để bảo vệ tài khoản của bạn khỏi các truy cập trái phép.</p>
                    
                    <ul class="features-list">
                        <li>
                            <i class="bi bi-shield-lock"></i>
                            <span>Mật khẩu được mã hóa an toàn</span>
                        </li>
                        <li>
                            <i class="bi bi-graph-up"></i>
                            <span>Kiểm tra độ mạnh mật khẩu</span>
                        </li>
                        <li>
                            <i class="bi bi-clock-history"></i>
                            <span>Cập nhật ngay lập tức</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle"></i>
                            <span>Xác nhận mật khẩu chính xác</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="reset-main">
                <div class="reset-header">
                    <h1>Đặt Lại Mật Khẩu</h1>
                    <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
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

                <?php if ($valid_token): ?>
                    <form method="POST" action="" id="resetForm">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock"></i>Mật khẩu mới *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Nhập mật khẩu mới" required>
                                <i class="bi bi-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="password-strength-text" id="passwordStrengthText"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock"></i>Xác nhận mật khẩu *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Xác nhận mật khẩu mới" required>
                                <i class="bi bi-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>

                        <button type="submit" class="btn-reset">
                            <i class="bi bi-key me-2"></i>Đặt Lại Mật Khẩu
                        </button>
                    </form>
                <?php endif; ?>

                <div class="links">
                    <p><a href="login.php">Quay lại đăng nhập</a></p>
                </div>
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

        // Toggle password
        ['togglePassword', 'toggleConfirmPassword'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                const input = this.previousElementSibling.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });

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

        // Password strength
        document.getElementById('password').addEventListener('input', function() {
            const pw = this.value;
            const bar = document.getElementById('passwordStrengthBar');
            const text = document.getElementById('passwordStrengthText');
            let strength = 0;

            if (pw.length >= 6) strength += 25;
            if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) strength += 25;
            if (/\d/.test(pw)) strength += 25;
            if(/[^a-zA-Z0-9]/.test(pw)) strength += 25;

            bar.style.width = strength + '%';
            let color = '', msg = '';
            if (strength < 50) { color = '#ef4444'; msg = 'Yếu'; }
            else if (strength < 75) { color = '#f59e0b'; msg = 'Trung bình'; }
            else { color = '#10b981'; msg = 'Mạnh'; }
            bar.style.backgroundColor = color;
            text.textContent = msg;
            text.style.color = color;
        });

        // Confirm password match
        document.getElementById('confirm_password').addEventListener('input', function() {
            const match = document.getElementById('passwordMatch');
            const pw1 = document.getElementById('password').value;
            const pw2 = this.value;
            if (!pw2) {
                match.textContent = '';
                this.style.borderColor = '#e2e8f0';
            } else if (pw1 === pw2) {
                match.textContent = 'Mật khẩu khớp';
                match.style.color = '#10b981';
                this.style.borderColor = '#10b981';
            } else {
                match.textContent = 'Mật khẩu không khớp';
                match.style.color = '#ef4444';
                this.style.borderColor = '#ef4444';
            }
        });

        // Form submit validation
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const pw1 = document.getElementById('password').value;
            const pw2 = document.getElementById('confirm_password').value;
            if (pw1 !== pw2) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
            }
        });

        // Auto focus
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            if (document.getElementById('password')) {
                document.getElementById('password').focus();
            }
        });

        // Ngăn cuộn trang
        document.body.style.overflow = 'hidden';
    </script>
</body>
</html>