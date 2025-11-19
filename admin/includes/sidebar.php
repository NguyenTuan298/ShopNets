  <aside class="sidebar">
    <div class="logo-area">
      <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-60x60.png" alt="ShopNets Logo">
      <span class="logo-text">ShopNets</span>
    </div>

    <div class="sidebar-content">
      <nav class="menu">
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php" class="menu-item <?php echo (isset($currentPage) && $currentPage == 'dashboard') ? 'active' : ''; ?>">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Dashboard.png" alt="Bảng Điều Khiển">
          Bảng Điều Khiển
        </a>
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>products/index.php" class="menu-item <?php echo (isset($currentPage) && $currentPage == 'products') ? 'active' : ''; ?>">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Products.png" alt="Sản Phẩm">
          Sản Phẩm
        </a>
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>categories/index.php" class="menu-item <?php echo (isset($currentPage) && $currentPage == 'categories') ? 'active' : ''; ?>">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Categories.png" alt="Danh Mục">
          Danh Mục
        </a>
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>orders/index.php" class="menu-item <?php echo (isset($currentPage) && $currentPage == 'orders') ? 'active' : ''; ?>">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Orders.png" alt="Đơn Hàng">
          Đơn Hàng
        </a>
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>users/index.php" class="menu-item <?php echo (isset($currentPage) && $currentPage == 'users') ? 'active' : ''; ?>">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Users.png" alt="Người Dùng">
          Người Dùng
        </a>
      </nav>

      <div class="menu-bottom">
        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>settings/index.php" class="menu-item">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Setting.png" alt="Cài Đặt">
          Cài Đặt
        </a>
        <a href="#" class="menu-item" id="logoutBtn" onclick="handleLogout(event)">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/SignOut.png" alt="Đăng Xuất">
          Đăng Xuất
        </a>
      </div>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <div></div>
      <div class="actions">
        <span class="icon"><img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Notification.png" alt="Notifications"></span>
        <span class="icon" id="themeToggle" onclick="toggleTheme()" style="cursor: pointer;">
          <img id="themeIcon" src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Light.png" alt="Theme">
        </span>
        <span class="icon" id="profileIcon" style="cursor: pointer;">
          <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Profile.png" alt="Profile">
        </span>
      </div>
    </header>

    <!-- Profile Popup -->
    <div class="profile-popup" id="profilePopup">
      <div class="profile-popup-content">
        <div class="profile-header">
          <div class="profile-avatar">
            <img id="popupAvatar" src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/default-avatar.svg" alt="Admin Avatar">
          </div>
          <div class="profile-info">
            <h3 id="popupAdminName">Admin</h3>
            <p id="popupAdminEmail">admin@shopnets.com</p>
          </div>
        </div>
        
        <div class="profile-menu">
          <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>settings/index.php" class="profile-menu-item">
            <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/Setting.png" alt="Cài Đặt">
            <span>Cài Đặt Tài Khoản</span>
          </a>
          
          <a href="#" class="profile-menu-item" onclick="handleLogout(event)">
            <img src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/SignOut.png" alt="Đăng Xuất">
            <span>Đăng Xuất</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Profile Popup Overlay -->
    <div class="profile-overlay" id="profileOverlay" onclick="closeProfilePopup()"></div>

<script>
function toggleTheme() {
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const baseUrl = '<?php echo isset($baseUrl) ? $baseUrl : ''; ?>';
    
    body.classList.toggle('dark-theme');
    
    if (body.classList.contains('dark-theme')) {
        themeIcon.src = baseUrl + 'assets/images/icons/Light.png';
        themeIcon.alt = 'Switch to Light Mode';
        localStorage.setItem('theme', 'dark');
    } else {
        themeIcon.src = baseUrl + 'assets/images/icons/Dark.png';
        themeIcon.alt = 'Switch to Dark Mode';
        localStorage.setItem('theme', 'light');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const baseUrl = '<?php echo isset($baseUrl) ? $baseUrl : ''; ?>';
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-theme');
        themeIcon.src = baseUrl + 'assets/images/icons/Light.png';
        themeIcon.alt = 'Switch to Light Mode';
    } else {
        themeIcon.src = baseUrl + 'assets/images/icons/Dark.png';
        themeIcon.alt = 'Switch to Dark Mode';
    }

    // Profile icon event listener - add this FIRST with high priority
    const profileIcon = document.getElementById('profileIcon');
    if (profileIcon) {
        // Remove any existing event listeners
        profileIcon.removeEventListener('click', toggleProfilePopup);
        
        // Add our event listener with capture phase for higher priority
        profileIcon.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            toggleProfilePopup(event);
        }, true); // Use capture phase
    }

    // Load admin profile data for popup
    loadAdminProfileData();
});

// Profile Popup Functions
function toggleProfilePopup(event) {
    console.log('Profile popup toggle called');
    
    // Prevent event bubbling
    if (event) {
        event.stopPropagation();
        event.preventDefault();
        event.stopImmediatePropagation();
    }
    
    const popup = document.getElementById('profilePopup');
    const overlay = document.getElementById('profileOverlay');
    
    if (popup && overlay) {
        if (popup.classList.contains('active')) {
            closeProfilePopup();
        } else {
            openProfilePopup();
        }
    } else {
        console.error('Profile popup elements not found');
    }
}

function openProfilePopup() {
    console.log('Opening profile popup...');
    const popup = document.getElementById('profilePopup');
    const overlay = document.getElementById('profileOverlay');
    
    console.log('Popup element:', popup);
    console.log('Overlay element:', overlay);
    
    if (popup && overlay) {
        popup.classList.add('active');
        overlay.classList.add('active');
        document.body.classList.add('popup-open');
        
        console.log('Classes added. Popup classes:', popup.classList.toString());
        console.log('Overlay classes:', overlay.classList.toString());
    } else {
        console.error('Could not find popup or overlay elements');
    }
}

function closeProfilePopup() {
    console.log('Closing profile popup...');
    const popup = document.getElementById('profilePopup');
    const overlay = document.getElementById('profileOverlay');
    
    if (popup && overlay) {
        popup.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('popup-open');
    }
}

function loadAdminProfileData() {
    const baseUrl = '<?php echo isset($baseUrl) ? $baseUrl : ''; ?>';
    console.log('Loading admin profile data...');
    
    // Load admin data via AJAX
    fetch(baseUrl + 'includes/get_admin_profile.php')
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Admin data received:', data);
            if (data.success && data.admin) {
                // Update email
                const emailElement = document.getElementById('popupAdminEmail');
                if (emailElement && data.admin.email) {
                    emailElement.textContent = data.admin.email;
                }
                
                // Update avatar
                const avatarElement = document.getElementById('popupAvatar');
                if (avatarElement) {
                    if (data.admin.avatar && data.admin.avatar.trim() !== '') {
                        const avatarPath = baseUrl + data.admin.avatar;
                        console.log('Setting avatar to:', avatarPath);
                        avatarElement.src = avatarPath;
                    } else {
                        console.log('No avatar found, using default');
                        avatarElement.src = baseUrl + 'assets/images/default-avatar.svg';
                    }
                }
            } else {
                console.error('Failed to load admin profile:', data.error || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error loading admin profile:', error);
        });
}
</script>
