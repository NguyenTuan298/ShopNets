<?php 
$pageTitle = 'Cài Đặt Hệ Thống | ShopNets';
$currentPage = 'settings';
$baseUrl = '../';
include '../includes/header.php';
include '../includes/sidebar.php';

// Load current admin data
require_once '../includes/db_connect.php';
try {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id'] ?? 1]);
    $currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $currentAdmin = ['email' => '', 'phone' => '', 'avatar' => ''];
}
?>

<div class="settings-page">

    <section class="content">
      <div class="content-header">
        <h1>Cài Đặt Hệ Thống</h1>
        <span>Quản lý thông tin cơ bản và cấu hình website</span>
      </div>

      <!-- Admin Profile Settings -->
      <div class="card settings-card">
        <div class="card-header">
          <h2>👤 Thông Tin Quản Trị Viên</h2>
        </div>
        
        <div class="settings-content">
          <form id="adminProfileForm" method="POST" action="settings_action.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="admin_profile">
            
            <div class="admin-profile-section">
              <div class="avatar-upload">
                <div class="current-avatar">
                  <img src="<?php echo !empty($currentAdmin['avatar']) ? '../' . $currentAdmin['avatar'] : '../assets/images/default-avatar.svg'; ?>" alt="Avatar" id="currentAvatar">
                  <div class="avatar-overlay">
                    <span>📷 Thay Đổi</span>
                  </div>
                </div>
                <input type="file" id="adminAvatar" name="admin_avatar" accept="image/*" style="display: none;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('adminAvatar').click()">Chọn Ảnh Đại Diện</button>
                <span class="field-help">JPG, PNG tối đa 5MB. Kích thước đề xuất: 200x200px</span>
              </div>

              <div class="settings-grid">
                <div class="form-group">
                  <label for="adminEmail">Email Đăng Nhập <span class="required">*</span></label>
                  <input type="email" id="adminEmail" name="admin_email" value="<?php echo htmlspecialchars($currentAdmin['email'] ?? ''); ?>" required>
                  <span class="field-help">Email này được sử dụng để đăng nhập vào hệ thống</span>
                </div>
                
                <div class="form-group">
                  <label for="adminPhone">Số Điện Thoại</label>
                  <input type="tel" id="adminPhone" name="admin_phone" value="<?php echo htmlspecialchars($currentAdmin['phone'] ?? ''); ?>">
                </div>
              </div>
            </div>
            
            <button type="submit" class="btn btn-primary">👤 Cập Nhật Thông Tin Quản Trị</button>
          </form>
        </div>
      </div>

      <!-- Website Information Settings -->
      <div class="card settings-card">
        <div class="card-header">
          <h2>🌐 Thông Tin Website</h2>
        </div>
        
        <div class="settings-content">
          <form id="websiteInfoForm" method="POST" action="settings_action.php">
            <input type="hidden" name="action" value="website_info">
            
            <div class="settings-grid">
              <div class="form-group">
                <label for="siteName">Tên Website / Ứng Dụng <span class="required">*</span></label>
                <input type="text" id="siteName" name="site_name" value="ShopNets" required>
              </div>
              
              <div class="form-group">
                <label for="siteTagline">Slogan / Khẩu Hiệu</label>
                <input type="text" id="siteTagline" name="site_tagline" value="Điểm đến mua sắm trực tuyến của bạn" placeholder="Slogan website">
              </div>
              
              <div class="form-group">
                <label for="metaDescription">Mô Tả Ngắn (Meta Description) <span class="required">*</span></label>
                <textarea id="metaDescription" name="meta_description" rows="3" required placeholder="Mô tả ngắn về website để hiện thị trên Google và mạng xã hội...">ShopNets - Nền tảng mua sắm trực tuyến hàng đầu với hàng nghìn sản phẩm chất lượng cao và dịch vụ tốt nhất.</textarea>
              </div>
            
            </div>
            
            <button type="submit" class="btn btn-primary">💾 Lưu Thông Tin Website</button>
          </form>
        </div>
      </div>

      <!-- Contact Information Settings -->
      <div class="card settings-card">
        <div class="card-header">
          <h2>📞 Thông Tin Liên Hệ</h2>
        </div>
        
        <div class="settings-content">
          <form id="contactInfoForm" method="POST" action="settings_action.php">
            <input type="hidden" name="action" value="contact_info">
            
            <div class="settings-grid">
              <div class="form-group">
                <label for="contactEmail">Email Hệ Thống <span class="required">*</span></label>
                <input type="email" id="contactEmail" name="contact_email" value="admin@shopnets.com" required placeholder="Email hệ thống chính">
                <span class="field-help">Email này sẽ được sử dụng cho thông báo và liên hệ</span>
              </div>
              
              <div class="form-group">
                <label for="supportEmail">Email Hỗ Trợ</label>
                <input type="email" id="supportEmail" name="support_email" value="support@shopnets.com" placeholder="Email cho khách hàng liên hệ">
              </div>
              
              <div class="form-group">
                <label for="contactPhone">Số Điện Thoại <span class="required">*</span></label>
                <input type="tel" id="contactPhone" name="contact_phone" value="0123-456-789" required placeholder="Số điện thoại liên hệ">
              </div>
              
              <div class="form-group">
                <label for="contactHotline">Hotline</label>
                <input type="tel" id="contactHotline" name="contact_hotline" value="1900-1234" placeholder="Hotline hỗ trợ 24/7">
              </div>
              
              <div class="form-group full-width">
                <label for="contactAddress">Địa Chỉ Liên Hệ <span class="required">*</span></label>
                <textarea id="contactAddress" name="contact_address" rows="3" required placeholder="Địa chỉ văn phòng/cửa hàng chính...">123 Đường ABC, Phường XYZ, Quận 1, Thành phố Hồ Chí Minh, Việt Nam</textarea>
              </div>
              
              <div class="form-group">
                <label for="workingHours">Giờ Làm Việc</label>
                <input type="text" id="workingHours" name="working_hours" value="8:00 - 17:00, Thứ 2 - Chủ Nhật" placeholder="Ví dụ: 8:00 - 17:00, T2-T7">
              </div>
              
              <div class="form-group">
                <label for="website">Website</label>
                <input type="url" id="website" name="website" value="https://shopnets.com" placeholder="https://website.com">
              </div>
            </div>
            
            <button type="submit" class="btn btn-primary">📱 Cập Nhật Thông Tin Liên Hệ</button>
          </form>
        </div>
      </div>

      <!-- System Settings -->
      <div class="card settings-card">
        <div class="card-header">
          <h2>⚙️ Cài Đặt Hệ Thống</h2>
        </div>
        
        <div class="settings-content">
          <form id="systemSettingsForm" method="POST" action="settings_action.php">
            <input type="hidden" name="action" value="system_settings">
            
            <div class="settings-grid">
              <div class="form-group">
                <label for="currency">Đơn Vị Tiền Tệ <span class="required">*</span></label>
                <select id="currency" name="currency" required>
                  <option value="VND" selected>Việt Nam Đồng (VNĐ)</option>
                  <option value="USD">Dollar Mỹ ($)</option>
                  <option value="EUR">Euro (€)</option>
                  <option value="JPY">Yên Nhật (¥)</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="timezone">Múi Giờ</label>
                <select id="timezone" name="timezone">
                  <option value="Asia/Ho_Chi_Minh" selected>Việt Nam (GMT+7)</option>
                  <option value="Asia/Bangkok">Thái Lan (GMT+7)</option>
                  <option value="Asia/Singapore">Singapore (GMT+8)</option>
                  <option value="UTC">UTC (GMT+0)</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="language">Ngôn Ngữ</label>
                <select id="language" name="language">
                  <option value="vi" selected>Tiếng Việt</option>
                  <option value="en">English</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="dateFormat">Cách Hiển Thị Ngày</label>
                <select id="dateFormat" name="date_format">
                  <option value="d/m/Y" selected>DD/MM/YYYY</option>
                  <option value="m/d/Y">MM/DD/YYYY</option>
                  <option value="Y-m-d">YYYY-MM-DD</option>
                </select>
              </div>
            </div>
            
            <button type="submit" class="btn btn-primary">⚙️ Lưu Cài Đặt Hệ Thống</button>
          </form>
        </div>
      </div>

    </section>
</div>

<script>
// Settings Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle avatar upload preview
    const avatarInput = document.getElementById('adminAvatar');
    const currentAvatar = document.getElementById('currentAvatar');
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle form submissions with AJAX
    const forms = ['websiteInfoForm', 'contactInfoForm', 'adminProfileForm', 'systemSettingsForm'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm(this);
            });
        }
    });
});

function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Đang lưu...';
    
    // Create form data
    const formData = new FormData(form);
    
    // Submit with fetch
    fetch('settings_action.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error(`Expected JSON but received: ${text}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug log
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Có lỗi xảy ra', 'error');
        }
    })
    .catch(error => {
        console.error('Full error:', error);
        showNotification(`Lỗi: ${error.message}`, 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function showNotification(message, type) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-popup');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification-popup ${type}`;
    
    const icon = type === 'success' ? '✅' : '❌';
    const iconClass = type === 'success' ? 'success-icon' : 'error-icon';
    
    notification.innerHTML = `
        <div class="notification-content">
            <div class="${iconClass}">${icon}</div>
            <div class="notification-message">${message}</div>
            <div class="notification-close" onclick="this.parentElement.parentElement.remove()">×</div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 400);
        }
    }, 5000);
}

// File upload drag and drop
document.querySelectorAll('.file-upload-area').forEach(area => {
    area.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#3b82f6';
        this.style.backgroundColor = '#eff6ff';
    });
    
    area.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.backgroundColor = '#f9fafb';
    });
    
    area.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#d1d5db';
        this.style.backgroundColor = '#f9fafb';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = this.querySelector('input[type="file"]');
            input.files = files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
