<?php
session_start();

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: ../index.php');  
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ShopNet - Đăng nhập</title>
    <link rel="apple-touch-icon" sizes="57x57" href="../assets/images/icons/icons_logo/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../assets/images/icons/icons_logo/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../assets/images/icons/icons_logo/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/images/icons/icons_logo/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../assets/images/icons/icons_logo/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../assets/images/icons/icons_logo/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../assets/images/icons/icons_logo/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../assets/images/icons/icons_logo/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/icons/icons_logo/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="../assets/images/icons/icons_logo/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/icons/icons_logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/images/icons/icons_logo/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/icons/icons_logo/favicon-16x16.png">
    <link rel="manifest" href="../assets/images/icons/icons_logo/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="../assets/images/icons/icons_logo/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/pages/login.css" />
    <link rel="stylesheet" href="../assets/css/pages/login-effects.css" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../assets/js/login.js"></script>
</head>
<body>
  <main class="page">
    <div class="auth-card">
      <section class="left">
        <header class="brand">
          <img src="../assets/images/icons/icons_logo/apple-icon-180x180.png?v=<?php echo time(); ?>" alt="ShopNet" class="logo" />
          <h1 class="name">ShopNet</h1>
          <p class="subtitle">Đăng nhập vào tài khoản</p>
        </header>

        <form class="form" action="login_action.php" method="post" autocomplete="off">
          <div class="field">
            <label for="email">Địa chỉ email</label>
            <div class="control with-icon">
              <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required />
              <span class="icon">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                  <path fill="currentColor" d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5v2Z"/>
                </svg>
              </span>
            </div>
          </div>

          <div class="field">
            <label for="password">Mật khẩu</label>
            <div class="control with-icon">
              <input type="password" id="password" name="password" placeholder="Nhập mật khẩu của bạn" required />
              <span class="icon">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                  <path fill="currentColor" d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-6 0V6a3 3 0 1 1 6 0v2h-6Z"/>
                </svg>
              </span>
            </div>
          </div>

          <button class="btn-primary" type="submit">Đăng Nhập</button>
        </form>
      </section>

      <section class="right">
        <img src="../assets/images/icons/Login.png?v=<?php echo time(); ?>" alt="Login" class="hero" />
      </section>
    </div>
  </main>

  <div id="loadingOverlay">
    <div class="loading-modal">
      <div class="loading-spinner"></div>
      <p id="loadingText"></p>
      <p id="loadingSubtext"></p>
    </div>
  </div>
</body>
</html>