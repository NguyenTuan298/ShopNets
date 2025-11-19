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
            $query = "SELECT id, username, full_name, email FROM users WHERE email = ? AND is_active = TRUE";
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
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/shopnets/auth/reset-password.php?token=" . urlencode($reset_token);
                    
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
    <title>Quên mật khẩu - Omnio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5; --primary-dark: #4338ca; --primary-light: #c7d2fe;
            --secondary: #10b981; --dark: #1e293b; --light: #f8fafc; --gray: #64748b;
            --danger: #ef4444; --success: #10b981;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; flex-direction: column; color: var(--dark); font-size: 1.35rem; line-height: 1.7; }
        .forgot-container { flex:1; display:flex; align-items:center; justify-content:center; padding:40px 20px; width:100%; }
        .forgot-card { background:white; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.15); overflow:hidden; transition:transform .3s ease, box-shadow .3s ease; width:100%; max-width:500px; }
        .forgot-card:hover { transform:translateY(-5px); box-shadow:0 25px 80px rgba(0,0,0,0.2); }
        .forgot-header { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:white; padding:2.5rem 2rem; text-align:center; position:relative; overflow:hidden; }
        .forgot-header::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,rgba(255,255,255,0) 70%); z-index:1; }
        .forgot-header h1 { font-weight:700; font-size:2.4rem; margin-bottom:.5rem; position:relative; z-index:2; }
        .forgot-header p { opacity:.9; margin-bottom:0; position:relative; z-index:2; font-size:1.45rem; }
        .logo { font-size:3.6rem; margin-bottom:1rem; display:block; position:relative; z-index:2; }
        .forgot-body { padding:2.5rem 2rem; }
        .form-group { margin-bottom:1.5rem; position:relative; }
        .form-label { font-weight:600; margin-bottom:.5rem; display:flex; align-items:center; color:var(--dark); font-size:1.45rem; }
        .form-label i { margin-right:.5rem; color:var(--primary); font-size:1.45rem; }
        .input-group { position:relative; }
        .form-control { border-radius:12px; padding:.85rem 1rem .85rem 3rem; border:2px solid #e2e8f0; font-size:1.35rem; transition:all .3s ease; background-color:var(--light); }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.15); background-color:white; }
        .input-icon { position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--gray); z-index:5; font-size:1.35rem; }
        .btn-forgot { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:white; border:none; border-radius:12px; padding:.85rem 2rem; font-weight:600; font-size:1.35rem; width:100%; transition:all .3s ease; margin-bottom:1.5rem; box-shadow:0 4px 15px rgba(79,70,229,.3); }
        .btn-forgot:hover { transform:translateY(-2px); box-shadow:0 7px 20px rgba(79,70,229,.4); }
        .btn-forgot:active { transform:translateY(0); }
        .links { text-align:center; }
        .links a { color:var(--primary); text-decoration:none; font-weight:500; font-size:1.3rem; transition:color .2s ease; }
        .links a:hover { color:var(--primary-dark); text-decoration:underline; }
        .alert { border-radius:12px; border:none; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; font-size:1.3rem; }
        .alert-danger { background-color:rgba(239,68,68,.1); color:var(--danger); }
        .alert-success { background-color:rgba(16,185,129,.1); color:var(--success); }
        .alert i { margin-right:.75rem; font-size:1.45rem; }
        .btn-close { background:transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; opacity:.7; }
        .btn-close:hover { opacity:1; }
        .instructions { background-color:rgba(79,70,229,.05); border-radius:12px; padding:1.25rem; margin-bottom:1.5rem; border-left:4px solid var(--primary); }
        .instructions h5 { color:var(--primary); margin-bottom:.5rem; font-weight:600; font-size:1.45rem; }
        .instructions p { margin-bottom:0; color:var(--gray); font-size:1.2rem; }
        @media (max-width:768px){ .forgot-card{border-radius:16px;} .forgot-header{padding:2rem 1.5rem;} .forgot-body{padding:2rem 1.5rem;} }
        .forgot-body .links a{color:var(--primary)!important;}
        .forgot-body .links a:hover{color:var(--primary-dark)!important;text-decoration:underline!important;}
        .forgot-body .form-control{padding-left:3.5rem!important;}
        .forgot-body .input-icon{left:1.25rem!important;}
        .alert-link { color: #0d6efd !important; text-decoration: underline !important; }
        .alert-link:hover { color: #0a58ca !important; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <i class="fas fa-key logo"></i>
                <h1>Quên Mật Khẩu</h1>
                <p>Chúng tôi sẽ gửi liên kết đặt lại mật khẩu đến email của bạn</p>
            </div>
            <div class="forgot-body">
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

                <?php if (!$error && !$success): ?>
                    <div class="instructions">
                        <h5><i class="fas fa-info-circle me-2"></i>Hướng dẫn</h5>
                        <p>Nhập địa chỉ email bạn đã sử dụng để đăng ký tài khoản. Chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.</p>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="forgotForm">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>Địa chỉ email *
                        </label>
                        <div class="input-group">
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($input_email); ?>"
                                   placeholder="Nhập địa chỉ email của bạn" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-forgot">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Liên Kết Đặt Lại
                    </button>

                    <div class="links">
                        <p>Nhớ mật khẩu? <a href="login.php">Quay lại đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Icon focus effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--primary)';
            });
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--gray)';
            });
        });

        // Auto focus email input
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('email').focus();
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
    </script>
</body>
</html>