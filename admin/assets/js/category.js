class CategoryManager {
    constructor() {
        // Add category modal elements
        this.addModal = document.getElementById('addCategoryModal');
        this.addBtn = document.getElementById('addCategoryBtn');
        this.addForm = document.getElementById('addCategoryForm');
        this.addCloseBtn = this.addModal?.querySelector('.close');
        this.addCancelBtn = document.getElementById('cancelBtn');

        // Edit category modal elements
        this.editModal = document.getElementById('editCategoryModal');
        this.editForm = document.getElementById('editCategoryForm');
        this.editCloseBtn = document.getElementById('editModalClose');
        this.editCancelBtn = document.getElementById('editCancelBtn');
        
        this.init();
    }

    init() {
        if (this.addBtn) {
            this.addBtn.addEventListener('click', () => this.showAddModal());
        }
        
        if (this.addCloseBtn) {
            this.addCloseBtn.addEventListener('click', () => this.hideAddModal());
        }
        
        if (this.addCancelBtn) {
            this.addCancelBtn.addEventListener('click', () => this.hideAddModal());
        }
        
        if (this.addForm) {
            this.addForm.addEventListener('submit', (e) => this.handleAddSubmit(e));
        }

        // Edit modal event listeners
        if (this.editCloseBtn) {
            this.editCloseBtn.addEventListener('click', () => this.hideEditModal());
        }
        
        if (this.editCancelBtn) {
            this.editCancelBtn.addEventListener('click', () => this.hideEditModal());
        }
        
        if (this.editForm) {
            this.editForm.addEventListener('submit', (e) => this.handleEditSubmit(e));
        }

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === this.addModal) {
                this.hideAddModal();
            }
            if (e.target === this.editModal) {
                this.hideEditModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.addModal?.classList.contains('show')) {
                    this.hideAddModal();
                }
                if (this.editModal?.classList.contains('show')) {
                    this.hideEditModal();
                }
            }
        });
    }

    // Thêm thể loại Methods
    showAddModal() {
        this.resetAddForm();
        this.addModal.classList.add('show');
        this.addModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        const firstInput = this.addForm.querySelector('input[name="name"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    hideAddModal() {
        this.addModal.classList.remove('show');
        this.addModal.style.display = 'none';
        document.body.style.overflow = '';
        this.resetAddForm();
    }

    resetAddForm() {
        this.addForm.reset();
        this.clearErrors('errorMessages');
        this.setLoading(this.addForm, false);
    }

    async handleAddSubmit(e) {
        e.preventDefault();
        
        this.clearErrors('errorMessages');
        this.setLoading(this.addForm, true);

        const formData = new FormData(this.addForm);
        
        try {
            const response = await fetch('add_category.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('errorMessages', result.message || 'Category added successfully');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showErrors('errorMessages', result.errors || ['An error occurred']);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showErrors('errorMessages', ['Network error. Please try again.']);
        } finally {
            this.setLoading(this.addForm, false);
        }
    }

    // chỉnh sửa thể loại Methods
    async showEditModal(categoryId) {
        try {
            const response = await fetch(`edit_category.php?id=${categoryId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.populateEditForm(result.data);
                this.editModal.classList.add('show');
                this.editModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                
                const firstInput = this.editForm.querySelector('input[name="name"]');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            } else {
                alert(result.error || 'Error loading category data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        }
    }

    hideEditModal() {
        this.editModal.classList.remove('show');
        this.editModal.style.display = 'none';
        document.body.style.overflow = '';
        this.resetEditForm();
    }

    resetEditForm() {
        this.editForm.reset();
        this.clearErrors('editErrorMessages');
        this.setLoading(this.editForm, false);
    }

    populateEditForm(categoryData) {
        document.getElementById('editCategoryId').value = categoryData.id;
        document.getElementById('editCategoryName').value = categoryData.name || '';
        document.getElementById('editCategoryDescription').value = categoryData.description || '';
        this.clearErrors('editErrorMessages');
    }

    async handleEditSubmit(e) {
        e.preventDefault();
        
        this.clearErrors('editErrorMessages');
        this.setLoading(this.editForm, true);

        const formData = new FormData(this.editForm);
        
        try {
            const response = await fetch('edit_category.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('editErrorMessages', result.message || 'Category updated successfully');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showErrors('editErrorMessages', result.errors || ['An error occurred']);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showErrors('editErrorMessages', ['Network error. Please try again.']);
        } finally {
            this.setLoading(this.editForm, false);
        }
    }

    // xóa thể loại Methods
    async deleteCategory(categoryId, categoryName) {
        const confirmed = await this.showDeleteConfirmModal(categoryName);
        if (!confirmed) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id', categoryId);

            const response = await fetch('delete_category.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showDeleteSuccessPopup(result.message);
                
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showDeleteErrorPopup(result.error || 'Error deleting category');
            }
        } catch (error) {
            console.error('Error deleting category:', error);
            this.showDeleteErrorPopup('Network error. Please try again.');
        }
    }

    // Xóa xác nhận modal
    showDeleteConfirmModal(categoryName) {
        return new Promise((resolve) => {
            const existingModal = document.getElementById('deleteConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            const modal = document.createElement('div');
            modal.id = 'deleteConfirmModal';
            modal.className = 'modal show';
            modal.style.display = 'flex';
            
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h2>Confirm Delete</h2>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete category <strong>"${categoryName}"</strong>?</p>
                        <p style="color: #666; font-size: 14px;">This action cannot be undone.</p>
                        <div class="form-actions" style="margin-top: 20px;">
                            <button type="button" class="btn btn-primary delete-confirm-btn" style="background-color: #dc3545; border-color: #dc3545;">
                                Delete
                            </button>
                            <button type="button" class="btn btn-secondary delete-cancel-btn">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            const confirmBtn = modal.querySelector('.delete-confirm-btn');
            const cancelBtn = modal.querySelector('.delete-cancel-btn');

            const cleanup = () => {
                document.body.style.overflow = '';
                modal.remove();
            };

            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });

            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                    resolve(false);
                }
            });

            const escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    document.removeEventListener('keydown', escapeHandler);
                    cleanup();
                    resolve(false);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        });
    }

    setLoading(form, isLoading) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');
        
        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            if (btnText) btnText.style.display = 'none';
            if (spinner) spinner.style.display = 'inline-block';
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            if (btnText) btnText.style.display = 'inline-block';
            if (spinner) spinner.style.display = 'none';
        }
    }

    showErrors(containerId, errors) {
        const errorContainer = document.getElementById(containerId);
        errorContainer.innerHTML = '';
        
        errors.forEach(error => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = error;
            errorContainer.appendChild(errorDiv);
        });
    }

    showSuccess(containerId, message) {
        const errorContainer = document.getElementById(containerId);
        errorContainer.innerHTML = `<div class="success-message">${message}</div>`;
    }

    clearErrors(containerId) {
        const errorContainer = document.getElementById(containerId);
        if (errorContainer) {
            errorContainer.innerHTML = '';
        }
    }

    showDeleteSuccessPopup(message) {
        this.createNotificationPopup(message, 'success');
    }

    showDeleteErrorPopup(message) {
        this.createNotificationPopup(message, 'error');
    }

    createNotificationPopup(message, type) {
        const existingPopup = document.getElementById('deleteNotificationPopup');
        if (existingPopup) {
            existingPopup.remove();
        }

        const popup = document.createElement('div');
        popup.id = 'deleteNotificationPopup';
        popup.className = `notification-popup ${type}`;
        
        const icon = type === 'success' ? '✓' : '✗';
        const iconClass = type === 'success' ? 'success-icon' : 'error-icon';
        
        popup.innerHTML = `
            <div class="notification-content">
                <div class="${iconClass}">${icon}</div>
                <div class="notification-message">${message}</div>
                <div class="notification-close" onclick="this.parentElement.parentElement.remove()">×</div>
            </div>
        `;

        document.body.appendChild(popup);

        setTimeout(() => {
            popup.classList.add('show');
        }, 10);

        setTimeout(() => {
            if (popup.parentElement) {
                popup.classList.add('fade-out');
                setTimeout(() => {
                    if (popup.parentElement) {
                        popup.remove();
                    }
                }, 300);
            }
        }, type === 'success' ? 3000 : 5000);
    }
}

let categoryManager;
document.addEventListener('DOMContentLoaded', function() {
    categoryManager = new CategoryManager();
});

window.editCategory = function(categoryId, categoryName) {
    if (categoryManager) {
        categoryManager.showEditModal(categoryId);
    }
};

window.deleteCategory = function(categoryId, categoryName) {
    if (categoryManager) {
        categoryManager.deleteCategory(categoryId, categoryName);
    }
};
