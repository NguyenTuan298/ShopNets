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
    <title>Đặt lại mật khẩu - Omnio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #c7d2fe;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --danger: #ef4444;
            --success: #10b981;
            --font-base: 1.35rem;   /* CHỮ GỐC TO */
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--dark);
            font-size: var(--font-base);
            line-height: 1.8;
        }

        .reset-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
            width: 100%;
        }

        .reset-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.18);
            overflow: hidden;
            transition: all .3s ease;
            width: 100%;
            max-width: 520px;
        }

        .reset-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 100px rgba(0,0,0,0.25);
        }

        .reset-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 3rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .reset-header::before {
            content: '';
            position: absolute;
            top: -60%; left: -60%;
            width: 220%; height: 220%;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            z-index: 1;
        }

        .reset-header h1 {
            font-weight: 700;
            font-size: 2.6rem;        /* TO HƠN */
            margin-bottom: .6rem;
            position: relative;
            z-index: 2;
        }

        .reset-header p {
            font-size: 1.5rem;        /* TO HƠN */
            opacity: .95;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .logo {
            font-size: 4rem;          /* ICON SIÊU TO */
            margin-bottom: 1.2rem;
            display: block;
            position: relative;
            z-index: 2;
        }

        .reset-body { 
            padding: 3rem 2.5rem; 
        }

        .form-group { 
            margin-bottom: 1.8rem; 
            position: relative; 
        }

        .form-label {
            font-weight: 600;
            margin-bottom: .6rem;
            display: flex;
            align-items: center;
            color: var(--dark);
            font-size: 1.5rem;        /* TO HƠN */
        }

        .form-label i {
            margin-right: .6rem;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .input-group { 
            position: relative; 
        }

        .form-control {
            border-radius: 14px;
            padding: 1rem 1.2rem 1rem 3.6rem;
            border: 2.5px solid #e2e8f0;
            font-size: 1.35rem;       /* TO HƠN */
            transition: all .3s ease;
            background-color: var(--light);
            height: 58px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79,70,229,.2);
            background-color: white;
        }

        .input-icon {
            position: absolute;
            left: 1.3rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            z-index: 5;
            font-size: 1.4rem;
        }

        .password-toggle {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            z-index: 5;
            font-size: 1.3rem;
            padding: 0;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover { color: var(--primary); }

        .btn-reset {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 14px;
            padding: 1rem 2.2rem;
            font-weight: 600;
            font-size: 1.4rem;        /* TO HƠN */
            width: 100%;
            transition: all .3s ease;
            margin: 1.8rem 0 1.5rem;
            box-shadow: 0 6px 18px rgba(79,70,229,.35);
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(79,70,229,.45);
        }

        .btn-reset:active { transform: translateY(0); }

        .links { 
            text-align: center; 
            margin-top: 1rem;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.35rem;       /* TO HƠN */
            transition: color .2s ease;
        }

        .links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .alert {
            border-radius: 14px;
            border: none;
            padding: 1.2rem 1.4rem;
            margin-bottom: 1.8rem;
            display: flex;
            align-items: center;
            font-size: 1.35rem;
        }

        .alert-danger { 
            background-color: rgba(239,68,68,.12); 
            color: var(--danger); 
        }

        .alert-success { 
            background-color: rgba(16,185,129,.12); 
            color: var(--success); 
        }

        .alert i { 
            margin-right: .9rem; 
            font-size: 1.6rem; 
        }

        .btn-close {
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1.1em auto no-repeat;
            opacity: .75;
            width: 1.4em;
            height: 1.4em;
        }

        .btn-close:hover { opacity: 1; }

        .password-strength {
            height: 7px;
            border-radius: 7px;
            margin-top: .7rem;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all .3s ease;
            border-radius: 7px;
        }

        .password-strength-text {
            font-size: 1.1rem;
            margin-top: .4rem;
            text-align: right;
            font-weight: 500;
        }

        #passwordMatch {
            font-size: 1.1rem;
            margin-top: .4rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .reset-card { border-radius: 18px; }
            .reset-header { padding: 2.5rem 2rem; }
            .reset-body { padding: 2.5rem 2rem; }
            .reset-header h1 { font-size: 2.3rem; }
            .reset-header p { font-size: 1.4rem; }
            .logo { font-size: 3.5rem; }
        }

        .reset-body .links a { color: var(--primary)!important; }
        .reset-body .links a:hover { color: var(--primary-dark)!important; text-decoration: underline!important; }
        .reset-body .form-control { padding-left: 3.8rem!important; }
        .reset-body .input-icon { left: 1.4rem!important; }
        .alert-link { color: #0d6efd !important; text-decoration: underline !important; font-weight: 600; }
        .alert-link:hover { color: #0a58ca !important; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="fas fa-key logo"></i>
                <h1>Đặt Lại Mật Khẩu</h1>
                <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
            </div>
            <div class="reset-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($valid_token): ?>
                    <form method="POST" action="" id="resetForm">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>Mật khẩu mới *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Nhập mật khẩu mới" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="password-strength-text" id="passwordStrengthText"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i>Xác nhận mật khẩu *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       placeholder="Xác nhận mật khẩu mới" required>
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="form-text"></div>
                        </div>

                        <button type="submit" class="btn btn-reset">
                            <i class="fas fa-save me-2"></i>Đặt Lại Mật Khẩu
                        </button>
                    </form>
                <?php endif; ?>

                <div class="links">
                    <p><a href="login.php">Quay lại đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password
        ['togglePassword', 'toggleConfirmPassword'].forEach(id => {
            document.getElementById(id).addEventListener('click', function() {
                const input = this.previousElementSibling.previousElementSibling;
                const icon = this.querySelector('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });

        // Focus effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--primary)';
            });
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--gray)';
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
            const colors = ['#ef4444', '#f59e0b', '#f59e0b', '#10b981'];
            const texts = ['', 'Yếu', 'Trung bình', 'Mạnh'];
            bar.style.backgroundColor = colors[Math.floor(strength/25)];
            text.textContent = texts[Math.floor(strength/25)];
            text.style.color = colors[Math.floor(strength/25)];
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
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const pw1 = document.getElementById('password').value;
            const pw2 = document.getElementById('confirm_password').value;
            if (pw1 !== pw2) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
            }
        });
    </script>
</body>
</html>