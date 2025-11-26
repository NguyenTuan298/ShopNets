<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$error = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin đăng nhập.';
    } else {
        try {
            $user = getUserByEmail($db, $username);
            if (!$user) {
                $query = "SELECT * FROM users WHERE username = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($user && verifyPassword($password, $user['password'])) {
                if ($user['status'] === 'active') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['username']; // Sử dụng username làm display name

                    if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
                        setcookie('remember_user', $user['id'], time() + (30 * 24 * 60 * 60), '/');
                    }

                    header('Location: ../index.php');
                   [exit];
                } else {
                    $error = 'Tài khoản của bạn đã bị khóa.';
                }
            } else {
                $error = 'Username/Email hoặc mật khẩu không đúng.';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập - ShopNets</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
</head>

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

  /* LOGIN PAGE */
  .login-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    z-index: 1;
  }
  .login-container {
    display: flex;
    width: 100%;
    max-width: 1000px;
    height: 580px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    box-shadow: 
      0 20px 40px rgba(0, 0, 0, 0.1),
      0 0 0 1px rgba(255, 255, 255, 0.1);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  .login-side {
    flex: 1;
    background: 
      linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(37, 99, 235, 0.8)),
      url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
    background-size: cover;
    color: white;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  .login-side::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,100 L100,0 L100,100 Z" fill="rgba(255,255,255,0.05)"/></svg>');
    background-size: cover;
  }
  .login-side-content {
    position: relative;
    z-index: 1;
  }
  .login-side h2 {
    font-size: 2.6rem;
    font-weight: 700;
    margin-bottom: 1.2rem;
    line-height: 1.2;
  }
  .login-side p {
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

  .login-main {
    flex: 1;
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .login-header {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  .login-header h1 {
    font-size: 2.4rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }
  .login-header p {
    color: var(--gray);
    font-size: 1.5rem;
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

  .remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.8rem;
    font-size: 1.4rem;
  }
  .form-check { margin: 0; }
  .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

  .btn-login {
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
  .btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
  }
  .btn-login:active {
    transform: translateY(0);
  }

  .divider {
    display: flex;
    align-items: center;
    margin: 2rem 0;
    color: var(--gray);
    font-size: 1.4rem;
  }
  .divider::before,
  .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }
  .divider span { padding: 0 1.5rem; }

  .social-login {
    display: flex;
    gap: 1.2rem;
    margin-bottom: 2rem;
  }
  .btn-social {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: var(--dark);
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    font-size: 1.4rem;
  }
  .btn-social:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
  }
  .btn-social i { margin-right: .6rem; font-size: 1.5rem; }

  .links {
    text-align: center;
    font-size: 1.4rem;
  }
  .links a {
    color: var(--primary);
    font-weight: 500;
    text-decoration: none;
  }
  .links a:hover { text-decoration: underline; color: var(--primary-dark); }

  .alert {
    border-radius: var(--radius);
    padding: 1.2rem;
    margin-bottom: 1.8rem;
    display: flex;
    align-items: center;
    font-size: 1.4rem;
    background: rgba(239,68,68,.1);
    color: var(--danger);
    border: none;
  }
  .alert i { margin-right: 1rem; }

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
    .login-side {
      display: none;
    }
    .login-container {
      max-width: 450px;
      height: 520px;
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
    .login-main {
      padding: 2rem;
    }
    .social-login {
      flex-direction: column;
    }
    .remember-forgot {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    .login-container {
      height: auto;
      max-height: 90vh;
    }
  }
</style>

<body>
  <!-- Hiệu ứng particles -->
  <div class="particles" id="particles"></div>

  <!-- LOGO -->
  <a href="../index.php" class="site-logo">
    <img src="../assets/images/logo.png" alt="ShopNets Logo" class="logo-img">
    <span>ShopNets</span>
  </a>

  <div class="login-wrapper">
    <div class="login-container">
      <div class="login-side">
        <div class="login-side-content">
          <h2>Nền tảng Thương mại Điện tử Thế hệ Mới</h2>
          <p>Trải nghiệm mua sắm trực tuyến với công nghệ tiên tiến, bảo mật tối đa và giao diện thông minh.</p>
          
          <ul class="features-list">
            <li>
              <i class="bi bi-cart-check"></i>
              <span>Hệ thống thanh toán đa kênh an toàn</span>
            </li>
            <li>
              <i class="bi bi-shield-lock"></i>
              <span>Bảo mật dữ liệu với công nghệ mã hóa</span>
            </li>
            <li>
              <i class="bi bi-graph-up-arrow"></i>
              <span>AI đề xuất sản phẩm thông minh</span>
            </li>
            <li>
              <i class="bi bi-lightning-charge"></i>
              <span>Tốc độ xử lý siêu nhanh với cloud</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="login-main">
        <div class="login-header">
          <h1>Đăng Nhập</h1>
          <p>Truy cập vào nền tảng thương mại điện tử ShopNets</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label class="form-label"><i class="bi bi-person-badge"></i> Tên đăng nhập hoặc Email</label>
            <div class="input-group">
              <input type="text" class="form-control" name="username" id="username"
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                     placeholder="Nhập tên đăng nhập hoặc email" required>
              <i class="bi bi-person input-icon"></i>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="bi bi-key"></i> Mật khẩu</label>
            <div class="input-group">
              <input type="password" class="form-control" name="password" id="password"
                     placeholder="Nhập mật khẩu" required>
              <i class="bi bi-lock input-icon"></i>
              <button type="button" class="password-toggle" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="remember-forgot">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
              <label class="form-check-label" for="remember_me">Ghi nhớ</label>
            </div>
            <a style='text-decoration: none;'href="forgot-password.php">Quên mật khẩu?</a>
          </div>

          <button type="submit" class="btn-login">
            Đăng Nhập
          </button>

          <div class="divider"><span>Đăng nhập nhanh</span></div>

          <div class="social-login">
            <a href="#" class="btn-social btn-google"><i class="bi bi-google"></i> Google</a>
            <a href="#" class="btn-social btn-facebook"><i class="bi bi-facebook"></i> Facebook</a>
          </div>

          <div class="links">
            Chưa có tài khoản? <a href="register.php">Đăng ký tài khoản mới</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Tạo hiệu ứng particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 15;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Kích thước ngẫu nhiên
        const size = Math.random() * 5 + 2;
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        
        // Vị trí ngẫu nhiên
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100 + 100}%`;
        
        // Thời gian animation ngẫu nhiên
        const duration = Math.random() * 20 + 15;
        particle.style.animationDuration = `${duration}s`;
        
        // Độ trễ ngẫu nhiên
        const delay = Math.random() * 5;
        particle.style.animationDelay = `${delay}s`;
        
        particlesContainer.appendChild(particle);
      }
    }

    // Hiển thị/ẩn mật khẩu
    document.getElementById('togglePassword').addEventListener('click', function () {
      const input = document.getElementById('password');
      const icon = this.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    });

    // Focus icon khi input được focus
    document.querySelectorAll('.form-control').forEach(input => {
      const icon = input.parentElement.querySelector('.input-icon');
      input.addEventListener('focus', () => {
        icon.style.color = 'var(--primary)';
      });
      input.addEventListener('blur', () => {
        icon.style.color = 'var(--gray)';
      });
    });

    // Focus vào username khi load
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('username').focus();
      createParticles();
    });

    // Demo social login
    document.querySelectorAll('.btn-social').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        alert('Tính năng đang phát triển. Vui lòng đăng nhập bằng tài khoản.');
      });
    });

    // Ngăn cuộn trang
    document.body.style.overflow = 'hidden';
  </script>
</body>
</html>