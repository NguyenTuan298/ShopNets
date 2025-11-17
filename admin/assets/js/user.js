class UserManager {
    constructor() {
        this.initializeEventListeners();
        this.loadUsers();
    }

    initializeEventListeners() {
        // tìm kiếm người dùng
        $(document).on('input', '#searchInput', (e) => {
            this.loadUsers(e.target.value);
        });

        // chỉnh sửa người dùng nút
        $(document).on('click', '[data-action="edit"]', (e) => {
            const userId = $(e.target).closest('tr').data('user-id');
            this.openEditModal(userId);
        });

        // xóa người dùng nút
        $(document).on('click', '[data-action="delete"]', (e) => {
            const userId = $(e.target).closest('tr').data('user-id');
            const username = $(e.target).closest('tr').find('td:nth-child(2)').text();
            this.openDeleteModal(userId, username);
        });

        // luu chỉnh sửa người dùng
        $(document).on('submit', '#editUserForm', (e) => {
            e.preventDefault();
            this.handleEditSubmit();
        });

        // xoa người dùng xác nhận nút
        $(document).on('click', '#confirmDelete', () => {
            this.handleDelete();
        });

        // thoát modal nút
        $(document).on('click', '.close, #editCancelBtn, #deleteCancelBtn', (e) => {
            const $modal = $(e.target).closest('.modal');
            this.closeModal($modal);
        });

        // thoát modal khi nhấp bên ngoài nội dung
        $(document).on('click', '.modal', (e) => {
            if (e.target === e.currentTarget) {
                this.closeModal($(e.target));
            }
        });
    }

    async loadUsers(searchTerm = '') {
        try {
            this.showLoading();
            const url = searchTerm ? 
                `user_action.php?search=${encodeURIComponent(searchTerm)}` : 
                'user_action.php';
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                this.renderUsersTable(data.data || []);
            } else {
                this.showNotification('Lỗi tải người dùng: ' + (data.error || 'Lỗi không xác định'), 'error');
                this.renderUsersTable([]);
            }
        } catch (error) {
            console.error('Error loading users:', error);
            this.showNotification('Lỗi tải người dùng: ' + error.message, 'error');
            this.renderUsersTable([]);
        } finally {
            this.hideLoading();
        }
    }

    renderUsersTable(users) {
        const tbody = document.querySelector('#usersTable tbody');
        if (!tbody) return;

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Không tìm thấy người dùng nào</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => `
            <tr data-user-id="${user.id}">
                <td>${user.id}</td>
                <td>${this.escapeHtml(user.username)}</td>
                <td>${this.escapeHtml(user.email)}</td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="btn-icon btn-edit" data-action="edit" title="Chỉnh Sửa Người Dùng" style="position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;">
                            <img src="../assets/images/icons/edit.png" alt="Chỉnh Sửa" style="width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;">
                        </button>
                        <button type="button" class="btn-icon btn-delete" data-action="delete" title="Xóa Người Dùng" style="position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;">
                            <img src="../assets/images/icons/delete.png" alt="Xóa" style="width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;">
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async openEditModal(userId) {
        try {
            const response = await fetch(`edit_user.php?id=${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                const user = data.data;
                $('#editUserId').val(user.id);
                $('#editUsername').val(user.username);
                $('#editEmail').val(user.email);
                $('#editUserModal').addClass('show');
                $('#editUsername').focus();
                this.clearEditErrors();
            } else {
                this.showNotification('Lỗi: ' + (data.error || 'Không thể tải dữ liệu người dùng'), 'error');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            this.showNotification('Lỗi tải người dùng: ' + error.message, 'error');
        }
    }

    async handleEditSubmit() {
        this.clearEditErrors();
        
        const $form = $('#editUserForm');
        const $submitBtn = $form.find('button[type="submit"]');
        const $btnText = $submitBtn.find('.btn-text');
        const $spinner = $submitBtn.find('.loading-spinner');
        
        // hiện thị trạng thái tải
        $btnText.hide();
        $spinner.show();
        $submitBtn.prop('disabled', true);
        
        const formData = new FormData($('#editUserForm')[0]);

        try {
            const response = await fetch('edit_user.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message || 'Cập nhật người dùng thành công', 'success');
                this.closeModal($('#editUserModal'));
                this.loadUsers();
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    this.displayEditErrors(data.errors);
                } else {
                    this.showNotification('Lỗi: ' + (data.error || 'Lỗi không xác định xảy ra'), 'error');
                }
            }
        } catch (error) {
            console.error('Error updating user:', error);
            this.showNotification('Lỗi cập nhật người dùng: ' + error.message, 'error');
        } finally {
            // hiện thị trạng thái tải
            $btnText.show();
            $spinner.hide();
            $submitBtn.prop('disabled', false);
        }
    }

    openDeleteModal(userId, username) {
        $('#deleteUserId').val(userId);
        $('#deleteUserName').text(username);
        $('#deleteUserModal').addClass('show');
    }

    async handleDelete() {
        const userId = $('#deleteUserId').val();
        const $deleteBtn = $('#confirmDelete');
        const $btnText = $deleteBtn.find('.btn-text');
        const $spinner = $deleteBtn.find('.loading-spinner');
        
        // hiện thị trạng thái tải
        $btnText.hide();
        $spinner.show();
        $deleteBtn.prop('disabled', true);
        
        try {
            const response = await fetch('delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${encodeURIComponent(userId)}`
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(data.message || 'Xóa người dùng thành công', 'success');
                this.closeModal($('#deleteUserModal'));
                this.loadUsers();
            } else {
                this.showNotification('Lỗi: ' + (data.error || 'Không thể xóa người dùng'), 'error');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            this.showNotification('Lỗi xóa người dùng: ' + error.message, 'error');
        } finally {
            $btnText.show();
            $spinner.hide();
            $deleteBtn.prop('disabled', false);
        }
    }

    displayEditErrors(errors) {
        const errorContainer = $('#editErrorMessages');
        errorContainer.html(errors.map(error => `<div class="error">${this.escapeHtml(error)}</div>`).join(''));
        errorContainer.show();
    }

    clearEditErrors() {
        $('#editErrorMessages').hide().empty();
    }

    closeModal($modal) {
        $modal.removeClass('show');
        this.clearEditErrors();
    }

    showLoading() {
        $('#loadingIndicator').show();
    }

    hideLoading() {
        $('#loadingIndicator').hide();
    }

    showNotification(message, type = 'info') {
        // xóa thông báo hiện tại nếu có
        $('.notification-popup').remove();
        
        const iconHtml = type === 'success' ? 
            '<div class="success-icon">✓</div>' : 
            '<div class="error-icon">✗</div>';
        
        const notification = $(`
            <div class="notification-popup ${type}">
                <div class="notification-content">
                    ${iconHtml}
                    <div class="notification-message">${this.escapeHtml(message)}</div>
                    <div class="notification-close">×</div>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        
        // hiện thị thông báo
        setTimeout(() => notification.addClass('show'), 100);
        
        // tự động ẩn sau 5 giây
        setTimeout(() => {
            notification.addClass('fade-out');
            setTimeout(() => notification.remove(), 400);
        }, 5000);
        
        // đóng thông báo khi nhấp vào nút đóng
        notification.find('.notification-close').on('click', () => {
            notification.addClass('fade-out');
            setTimeout(() => notification.remove(), 400);
        });
    }

    escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

$(document).ready(() => {
    window.userManager = new UserManager();
});
