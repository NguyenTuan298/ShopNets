<?php
// includes/auth-modal.php
?>
<!-- Login Modal -->
<div class="modal fade login-modal-custom" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-header-content">
          <i class="bi bi-person-circle modal-icon"></i>
          <h5 class="modal-title" id="loginModalLabel">Đăng nhập tài khoản</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="welcome-message">
          <p>Chào mừng bạn đến với <strong>ShopNets</strong></p>
          <small class="text-muted">Đăng nhập để mua sắm và trải nghiệm dịch vụ tốt nhất</small>
        </div>
        
        <div class="modal-features">
          <div class="feature-item">
            <i class="bi bi-truck"></i>
            <span>Miễn phí vận chuyển</span>
          </div>
          <div class="feature-item">
            <i class="bi bi-shield-check"></i>
            <span>Bảo mật tuyệt đối</span>
          </div>
          <div class="feature-item">
            <i class="bi bi-gift"></i>
            <span>Ưu đãi thành viên</span>
          </div>
        </div>

        <div class="action-buttons">
          <a href="auth/login.php" class="btn btn-primary btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Đăng nhập ngay
          </a>
          <a href="auth/register.php" class="btn btn-outline-primary btn-register">
            <i class="bi bi-person-plus"></i>
            Tạo tài khoản mới
          </a>
        </div>

        <div class="quick-info">
          <div class="info-item">
            <i class="bi bi-lightning"></i>
            <span>Mua sắm nhanh chóng</span>
          </div>
          <div class="info-item">
            <i class="bi bi-bag-check"></i>
            <span>Theo dõi đơn hàng</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* QUAN TRỌNG: Đặt z-index cao để modal hiển thị trên header */
.login-modal-custom.modal {
  z-index: 99999 !important;
}

.login-modal-custom .modal-dialog {
  z-index: 99999 !important;
}

.login-modal-custom .modal-backdrop {
  z-index: 99998 !important;
}

.login-modal-custom .modal-content {
  border-radius: 20px;
  border: none;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  z-index: 99999 !important;
}

.login-modal-custom .modal-header {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border-bottom: none;
  padding: 30px 30px 20px;
  position: relative;
}

.login-modal-custom .modal-header::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 4px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 2px;
}

.modal-header-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-icon {
  font-size: 2.4rem;
  opacity: 0.9;
}

.login-modal-custom .modal-title {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0;
}

.login-modal-custom .btn-close {
  filter: invert(1);
  opacity: 0.8;
  background: none;
}

.login-modal-custom .btn-close:hover {
  opacity: 1;
}

.login-modal-custom .modal-body {
  padding: 30px;
}

.welcome-message {
  text-align: center;
  margin-bottom: 25px;
}

.welcome-message p {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 5px;
}

.welcome-message small {
  font-size: 1.3rem;
}

.modal-features {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  padding: 20px;
  background: var(--light);
  border-radius: var(--radius);
  border: 1px solid var(--border);
}

.feature-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  flex: 1;
  padding: 0 10px;
}

.feature-item i {
  font-size: 2rem;
  color: var(--primary);
  margin-bottom: 8px;
}

.feature-item span {
  font-size: 1.2rem;
  font-weight: 500;
  color: var(--dark);
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 25px;
}

.btn-login, .btn-register {
  padding: 14px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.4rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  text-decoration: none;
}

.btn-login {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  border: none;
  color: white;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
  color: white;
}

.btn-register {
  border: 2px solid var(--primary);
  color: var(--primary);
  background: transparent;
}

.btn-register:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
}

.quick-info {
  display: flex;
  justify-content: center;
  gap: 30px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}

.info-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.3rem;
  color: var(--gray);
}

.info-item i {
  color: var(--success);
  font-size: 1.4rem;
}

/* Animation */
.login-modal-custom .modal-content {
  animation: modalAppear 0.4s ease-out;
}

@keyframes modalAppear {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .login-modal-custom .modal-dialog {
    margin: 20px;
  }
  
  .login-modal-custom .modal-header {
    padding: 25px 25px 15px;
  }
  
  .login-modal-custom .modal-body {
    padding: 25px;
  }
  
  .modal-features {
    flex-direction: column;
    gap: 15px;
    padding: 15px;
  }
  
  .feature-item {
    flex-direction: row;
    justify-content: flex-start;
    text-align: left;
  }
  
  .feature-item i {
    margin-bottom: 0;
    margin-right: 10px;
    font-size: 1.8rem;
  }
  
  .quick-info {
    flex-direction: column;
    gap: 15px;
    align-items: center;
  }
  
  .btn-login, .btn-register {
    padding: 12px 18px;
    font-size: 1.3rem;
  }
}

@media (max-width: 576px) {
  .login-modal-custom .modal-header {
    padding: 20px 20px 15px;
  }
  
  .login-modal-custom .modal-body {
    padding: 20px;
  }
  
  .modal-header-content {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
  
  .modal-icon {
    font-size: 2rem;
  }
  
  .login-modal-custom .modal-title {
    font-size: 1.6rem;
  }
}
</style>