<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$error = '';
$success = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } else {
        try {
            $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$username, $email]);
            
            if ($check_stmt->fetch()) {
                $error = 'Username hoặc email đã tồn tại.';
            } else {
                if (createUser($db, $username, $email, $password, $full_name, $phone, $address)) {
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                    $_POST = [];
                } else {
                    $error = 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.';
                }
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
  <title>Đăng ký - ShopNets</title>

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
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding-top: 0;
  }

  :root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
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

  /* GRID */
  .grid {
    width: 1200px;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
  }

  /* REGISTER PAGE */
  .register-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }
  .register-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    width: 100%;
    max-width: 800px;
    transition: var(--transition);
  }
  .register-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
  }

  .register-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2.8rem 2rem;
    text-align: center;
  }
  .register-header h1 {
    font-size: 2.4rem;
    font-weight: 700;
    margin-bottom: .5rem;
  }
  .register-header p {
    opacity: .9;
    font-size: 1.45rem;
  }
  .register-header .logo-icon {
    font-size: 3.2rem;
    margin-bottom: .8rem;
    display: block;
  }

  .register-body {
    padding: 2.8rem 2rem;
  }
  .form-group {
    margin-bottom: 1.6rem;
  }
  .form-label {
    font-weight: 600;
    margin-bottom: .6rem;
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
    padding: .9rem 1rem .9rem 3.2rem;
    border: 1px solid var(--border);
    font-size: 1.4rem;
    background: var(--light);
    transition: var(--transition);
    width: 100%;
  }
  .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37,99,235,.15);
    background: white;
  }
  .input-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 1.3rem;
  }
  .password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray);
    cursor: pointer;
    font-size: 1.3rem;
  }
  .password-toggle:hover { color: var(--primary); }

  .form-text { font-size: 1.25rem; color: var(--gray); margin-top: .4rem; }

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

  .form-check {
    margin-bottom: 1.8rem;
  }
  .form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
  }
  .form-check-label {
    font-size: 1.35rem;
    color: var(--dark);
  }
  .form-check-label a {
    color: var(--primary);
    text-decoration: none;
  }
  .form-check-label a:hover {
    text-decoration: underline;
  }

  .btn-register {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    border-radius: var(--radius);
    padding: 1rem;
    font-weight: 600;
    font-size: 1.5rem;
    width: 100%;
    transition: var(--transition);
    box-shadow: var(--shadow);
  }
  .btn-register:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
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
    padding: 1rem 1.2rem;
    margin-bottom: 1.6rem;
    display: flex;
    align-items: center;
    font-size: 1.35rem;
    border: none;
  }
  .alert-danger { background: rgba(239,68,68,.1); color: var(--danger); }
  .alert-success { background: rgba(16,185,129,.1); color: var(--success); }
  .alert i { margin-right: .8rem; }

  /* LOGO GÓC TRÁI */
  .site-logo {
    position: fixed;
    top: 16px;
    left: 16px;
    z-index: 1000;
    background: white;
    padding: 6px 10px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: var(--transition);
    font-size: 1.4rem;
    line-height: 1;
  }
  .site-logo:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
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
    font-size: 1.5rem;
  }

  /* RESPONSIVE */
  @media (max-width: 768px) {
    .register-wrapper { padding: 20px 15px; }
    .register-header { padding: 2.2rem 1.5rem; }
    .register-header h1 { font-size: 2.1rem; }
    .register-body { padding: 2.2rem 1.5rem; }
    .row > div { margin-bottom: 0; }
  }
  @media (max-width: 576px) {
    .site-logo {
      top: 12px;
      left: 12px;
      padding: 5px 8px;
      gap: 6px;
    }
    .site-logo .logo-img { height: 24px; }
    .site-logo span { font-size: 1.35rem; }
  }
</style>

<body>
  <!-- LOGO GÓC TRÁI -->
  <a href="../index.php" class="site-logo">
    <img src="../assets/images/logo.png" alt="ShopNets Logo" class="logo-img">
    <span>ShopNets</span>
  </a>

  <div class="register-wrapper">
    <div class="register-card">
      <div class="register-header">
        <i class="bi bi-rocket logo-icon"></i>
        <h1>Đăng Ký Tài Khoản</h1>
        <p>Tham gia cộng đồng ShopNets ngay hôm nay</p>
      </div>

      <div class="register-body">
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-person"></i> Tên đăng nhập *</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="username" id="username"
                         value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Nhập tên đăng nhập" required>
                  <i class="bi bi-person input-icon"></i>
                </div>
                <div class="form-text">Tên đăng nhập của bạn</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-envelope"></i> Email *</label>
                <div class="input-group">
                  <input type="email" class="form-control" name="email" id="email"
                         value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Nhập địa chỉ email" required>
                  <i class="bi bi-envelope input-icon"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-lock"></i> Mật khẩu *</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="password" id="password" placeholder="Nhập mật khẩu" required>
                  <i class="bi bi-lock input-icon"></i>
                  <button type="button" class="password-toggle" id="togglePassword"><i class="bi bi-eye"></i></button>
                </div>
                <div class="password-strength"><div class="password-strength-bar" id="passwordStrengthBar"></div></div>
                <div class="password-strength-text" id="passwordStrengthText"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-lock"></i> Xác nhận mật khẩu *</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Xác nhận mật khẩu" required>
                  <i class="bi bi-lock input-icon"></i>
                  <button type="button" class="password-toggle" id="toggleConfirmPassword"><i class="bi bi-eye"></i></button>
                </div>
                <div id="passwordMatch" class="form-text"></div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="bi bi-id-card"></i> Họ và tên *</label>
            <div class="input-group">
              <input type="text" class="form-control" name="full_name" id="full_name"
                     value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Nhập họ và tên đầy đủ" required>
              <i class="bi bi-id-card input-icon"></i>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-phone"></i> Số điện thoại</label>
                <div class="input-group">
                  <input type="tel" class="form-control" name="phone" id="phone"
                         value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                  <i class="bi bi-phone input-icon"></i>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><i class="bi bi-geo-alt"></i> Địa chỉ</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="address" id="address"
                         value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="Nhập địa chỉ">
                  <i class="bi bi-geo-alt input-icon"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
            <label class="form-check-label" for="agree_terms">
              Tôi đồng ý với <a href="../info/privacy-policy.php">Điều khoản dịch vụ</a> và <a href="../info/terms-of-service.php">Chính sách bảo mật</a>
            </label>
          </div>

          <button type="submit" class="btn-register">
            <i class="bi bi-person-plus"></i> Đăng Ký
          </button>

          <div class="links">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle password
    ['togglePassword', 'toggleConfirmPassword'].forEach(id => {
      document.getElementById(id).addEventListener('click', function () {
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

    // Focus icon
    document.querySelectorAll('.form-control').forEach(input => {
      const icon = input.parentElement.querySelector('.input-icon');
      input.addEventListener('focus', () => icon.style.color = 'var(--primary)');
      input.addEventListener('blur', () => icon.style.color = 'var(--gray)');
    });

    // Password strength
    document.getElementById('password').addEventListener('input', function () {
      const pw = this.value;
      const bar = document.getElementById('passwordStrengthBar');
      const text = document.getElementById('passwordStrengthText');
      let strength = 0;
      if (pw.length >= 6) strength += 25;
      if (pw.match(/[a-z]/) && pw.match(/[A-Z]/)) strength += 25;
      if (pw.match(/\d/)) strength += 25;
      if (pw.match(/[^a-zA-Z\d]/)) strength += 25;

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
    document.getElementById('confirm_password').addEventListener('input', function () {
      const match = document.getElementById('passwordMatch');
      const pw1 = document.getElementById('password').value;
      const pw2 = this.value;
      if (pw2 === '') {
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

    // Focus username
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('username').focus();
    });

    // Form validate
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      const agree = document.getElementById('agree_terms').checked;
      if (!agree) {
        e.preventDefault();
        alert('Vui lòng đồng ý với Điều khoản dịch vụ và Chính sách bảo mật!');
      }
    });
  </script>
</body>
</html>