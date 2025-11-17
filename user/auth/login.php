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
    padding-top: 0;               /* không có header */
  }

  :root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
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

  /* GRID & CONTAINER */
  .grid {
    width: 1200px;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 15px;
  }

  /* LOGIN PAGE */
  .login-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
  }
  .login-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    width: 100%;
    max-width: 420px;
    transition: var(--transition);
  }
  .login-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
  }

  .login-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
  }
  .login-header h1 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: .5rem;
  }
  .login-header p {
    opacity: .9;
    font-size: 1.4rem;
  }
  .login-header .logo-icon {
    font-size: 3rem;
    margin-bottom: .8rem;
    display: block;
  }

  .login-body {
    padding: 2.5rem 2rem;
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

  .remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.6rem;
    font-size: 1.35rem;
  }
  .form-check { margin: 0; }
  .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

  .btn-login {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    border-radius: var(--radius);
    padding: .9rem;
    font-weight: 600;
    font-size: 1.45rem;
    width: 100%;
    transition: var(--transition);
    box-shadow: var(--shadow);
  }
  .btn-login:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
  }

  .divider {
    display: flex;
    align-items: center;
    margin: 1.8rem 0;
    color: var(--gray);
    font-size: 1.35rem;
  }
  .divider::before,
  .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }
  .divider span { padding: 0 1rem; }

  .social-login {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.6rem;
  }
  .btn-social {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: var(--dark);
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
  }
  .btn-social:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
  }
  .btn-social i { margin-right: .5rem; }

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
    padding: 1rem 1.2rem;
    margin-bottom: 1.6rem;
    display: flex;
    align-items: center;
    font-size: 1.35rem;
    background: rgba(239,68,68,.1);
    color: var(--danger);
    border: none;
  }
  .alert i { margin-right: .8rem; }
/* LOGO GÓC TRÁI - DÙNG ẢNH NHỎ GỌN */
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
@media (max-width: 576px) {
  .site-logo {
    top: 12px;
    left: 12px;
    padding: 5px 8px;
    gap: 6px;
  }
  .site-logo .logo-img {
    height: 24px;
  }
  .site-logo span {
    font-size: 1.35rem;
  }
}
</style>

<body>
  <!--  LOGO  -->
    <a href="../index.php" class="site-logo">
    <img src="../assets/images/logo.png" alt="ShopNets Logo" class="logo-img">
    <span>ShopNets</span>
    </a>

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <i class="bi bi-box-arrow-in-right logo-icon"></i>
        <h1>Đăng Nhập</h1>
        <p>Chào mừng trở lại với ShopNets</p>
      </div>

      <div class="login-body">
        <?php if ($error): ?>
          <div class="alert alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label class="form-label"><i class="bi bi-person"></i> Tên đăng nhập hoặc Email</label>
            <div class="input-group">
              <input type="text" class="form-control" name="username" id="username"
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                     placeholder="Nhập tên đăng nhập hoặc email" required>
              <i class="bi bi-person input-icon"></i>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label"><i class="bi bi-lock"></i> Mật khẩu</label>
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
              <label class="form-check-label" for="remember_me">Ghi nhớ đăng nhập</label>
            </div>
            <a href="forgot-password.php">Quên mật khẩu?</a>
          </div>

          <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Đăng Nhập
          </button>

          <div class="divider"><span>Hoặc đăng nhập với</span></div>

          <div class="social-login">
            <a href="#" class="btn-social btn-google"><i class="bi bi-google"></i> Google</a>
            <a href="#" class="btn-social btn-facebook"><i class="bi bi-facebook"></i> Facebook</a>
          </div>

          <div class="links">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
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
      input.addEventListener('focus', () => icon.style.color = 'var(--primary)');
      input.addEventListener('blur', () => icon.style.color = 'var(--gray)');
    });

    // Focus vào username khi load
    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('username').focus();
    });

    // Demo social login
    document.querySelectorAll('.btn-social').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        alert('Tính năng đang phát triển. Vui lòng đăng nhập bằng tài khoản.');
      });
    });
  </script>
</body>
</html>