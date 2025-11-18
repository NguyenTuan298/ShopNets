class ProductManager {
    constructor() {
        // Thêm sản phẩm - modal elements
        this.addModal = document.getElementById('addProductModal');
        this.addBtn = document.getElementById('addProductBtn');
        this.addForm = document.getElementById('addProductForm');
        this.addCloseBtn = this.addModal?.querySelector('.close');
        this.addCancelBtn = document.getElementById('cancelBtn');

        this.editModal = document.getElementById('editProductModal');
        this.editForm = document.getElementById('editProductForm');
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

        // Render specifications when add-category changes
        const addCategory = document.getElementById('productCategory');
        if (addCategory) {
            addCategory.addEventListener('change', () => this.renderSpecifications('product', addCategory.options[addCategory.selectedIndex]?.text || '', {}));
        }

        // Chỉnh sửa sản phẩm - modal event listeners
        if (this.editCloseBtn) {
            this.editCloseBtn.addEventListener('click', () => this.hideEditModal());
        }
        
        if (this.editCancelBtn) {
            this.editCancelBtn.addEventListener('click', () => this.hideEditModal());
        }
        
        if (this.editForm) {
            this.editForm.addEventListener('submit', (e) => this.handleEditSubmit(e));
        }

        // Render specifications when edit-category changes
        const editCategory = document.getElementById('editProductCategory');
        if (editCategory) {
            editCategory.addEventListener('change', () => this.renderSpecifications('editProduct', editCategory.options[editCategory.selectedIndex]?.text || '', {}));
        }

        window.addEventListener('click', (e) => {
            if (e.target === this.addModal) {
                this.hideAddModal();
            }
            if (e.target === this.editModal) {
                this.hideEditModal();
            }
        });

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

    // Thêm sản phẩm
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

        // Serialize specifications into hidden input before sending
        this.serializeSpecifications('product');
        const formData = new FormData(this.addForm);
        
        try {
            const response = await fetch('add_product.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('errorMessages', result.message || 'Product added successfully');
                
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

    // Chỉnh sửa sản phẩm
    async showEditModal(productId) {
        try {
            // Fetch product data
            const response = await fetch(`edit_product.php?id=${productId}`, {
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
                alert(result.error || 'Lỗi tải dữ liệu sản phẩm');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Lỗi mạng. Vui lòng thử lại.');
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

    populateEditForm(productData) {
        document.getElementById('editProductId').value = productData.id;
        document.getElementById('editProductName').value = productData.name || '';
        
        // Set selected category - xử lý cả cấu trúc cũ và mới
        const categorySelect = document.getElementById('editProductCategory');
        if (categorySelect) {
            if (productData.category_id) {
                // Cấu trúc mới - sử dụng category_id
                categorySelect.value = productData.category_id;
            } else if (productData.category) {
                // Cấu trúc cũ - tìm category theo tên (fallback)
                for (let option of categorySelect.options) {
                    if (option.text === productData.category) {
                        categorySelect.value = option.value;
                        break;
                    }
                }
            }
        }
        
        document.getElementById('editProductPrice').value = productData.price || '';
        
        // Cập nhật các trường mới (kiểm tra tồn tại trước)
        const comparePrice = document.getElementById('editProductComparePrice');
        if (comparePrice) comparePrice.value = productData.compare_price || '';
        
        const quantity = document.getElementById('editProductQuantity');
        if (quantity) {
            // Xử lý cả quantity (mới) và inventory (cũ)
            quantity.value = productData.quantity || productData.inventory || '';
        }
        
        const shortDescription = document.getElementById('editProductShortDescription');
        if (shortDescription) shortDescription.value = productData.short_description || '';
        
        document.getElementById('editProductDescription').value = productData.description || '';
        
        // Checkbox cho featured và is_active (chỉ khi có)
        const featured = document.getElementById('editProductFeatured');
        if (featured) featured.checked = productData.featured == 1;
        
        const isActive = document.getElementById('editProductActive');
        if (isActive) isActive.checked = productData.is_active == 1;
        
        // Show current image preview
        const imagePreview = document.getElementById('currentImagePreview');
        if (productData.image && imagePreview) {
            imagePreview.innerHTML = `
                <div style="margin-top: 10px;">
                    <label style="display: block; margin-bottom: 5px; font-size: 12px; color: #666;">Current Image:</label>
                    <img src="../assets/images/uploads/${productData.image}" alt="Current product image" 
                         style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            `;
        } else if (imagePreview) {
            imagePreview.innerHTML = '<div style="margin-top: 10px; color: #999; font-size: 12px;">No current image</div>';
        }
        
        this.clearErrors('editErrorMessages');
        
        // Render specifications (if any)
        // Delay rendering to ensure category dropdown is updated first
        setTimeout(() => {
            try {
                const specs = productData.specifications ? JSON.parse(productData.specifications) : {};
                const categorySelect = document.getElementById('editProductCategory');
                const categoryName = categorySelect?.options[categorySelect.selectedIndex]?.text || '';
                console.log('Rendering specs for category:', categoryName, 'with data:', specs);
                this.renderSpecifications('editProduct', categoryName, specs);
            } catch (e) {
                console.error('Error parsing specifications:', e);
                this.renderSpecifications('editProduct', '', {});
            }
        }, 100);
    }

    async handleEditSubmit(e) {
        e.preventDefault();
        
        this.clearErrors('editErrorMessages');
        this.setLoading(this.editForm, true);

        // Serialize specifications into hidden input before sending
        this.serializeSpecifications('editProduct');
        const formData = new FormData(this.editForm);
        
        try {
            const response = await fetch('edit_product.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess('editErrorMessages', result.message || 'Product updated successfully');
                
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

    // Shared utility methods
    setLoading(form, isLoading) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.loading-spinner');
        
        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            btnText.style.display = 'inline-block';
            spinner.style.display = 'none';
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

    // Method to handle delete product with confirmation
    async deleteProduct(productId, productName) {
        // Create custom confirmation modal instead of browser confirm
        const confirmed = await this.showDeleteConfirmModal(productName);
        if (!confirmed) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id', productId);

            const response = await fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showDeleteSuccessPopup(result.message);
                
                // Remove the row from table with animation
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showDeleteErrorPopup(result.error || 'Error deleting product');
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            this.showDeleteErrorPopup('Network error. Please try again.');
        }
    }

    // xóa san phẩm - hiển thị modal xác nhận
    showDeleteConfirmModal(productName) {
        return new Promise((resolve) => {
            // Remove existing confirmation modal if any
            const existingModal = document.getElementById('deleteConfirmModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create confirmation modal
            const modal = document.createElement('div');
            modal.id = 'deleteConfirmModal';
            modal.className = 'modal show';
            modal.style.display = 'flex';
            
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h2>Xác Nhận Xóa</h2>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa sản phẩm <strong>"${productName}"</strong>?</p>
                        <p style="color: #666; font-size: 14px;">Hành động này không thể hoàn tác.</p>
                        <div class="form-actions" style="margin-top: 20px;">
                            <button type="button" class="btn btn-primary delete-confirm-btn" style="background-color: #dc3545; border-color: #dc3545;">
                                Xóa
                            </button>
                            <button type="button" class="btn btn-secondary delete-cancel-btn">
                                Hủy
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);
            document.body.style.overflow = 'hidden';

            // Event listeners
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

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                    resolve(false);
                }
            });

            // Close on Escape key
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

    // Show success popup after delete
    showDeleteSuccessPopup(message) {
        this.createNotificationPopup(message, 'success');
    }

    // Show error popup for delete
    showDeleteErrorPopup(message) {
        this.createNotificationPopup(message, 'error');
    }

    // Create notification popup
    createNotificationPopup(message, type) {
        // Remove existing popup if any
        const existingPopup = document.getElementById('deleteNotificationPopup');
        if (existingPopup) {
            existingPopup.remove();
        }

        // Create popup element
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

        // Add to page
        document.body.appendChild(popup);

        // Show with animation
        setTimeout(() => {
            popup.classList.add('show');
        }, 10);

        // Auto remove after 3 seconds for success, 5 seconds for error
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

// Specifications support
ProductManager.prototype.specTemplates = {
    'Điện thoại': ['Màn hình', 'CPU', 'RAM', 'Ổ cứng', 'Pin', 'Camera'],
    'Laptop': ['Màn hình', 'CPU', 'RAM', 'Ổ cứng', 'Pin', 'Card đồ hoa'],
    'Tablet': ['Màn hình', 'CPU', 'RAM', 'Ổ cứng', 'Pin'],
    'Headphones': ['Loại', 'Kết nối', 'Thời lượng pin', 'Trở kháng'],
    'Smartwatch': ['Màn hình', 'Pin', 'Kết nối', 'Tính năng']
};

ProductManager.prototype.renderSpecifications = function(formPrefix, categoryName, existingSpecs) {
    const containerId = formPrefix === 'product' ? 'addSpecificationsContainer' : (formPrefix + 'SpecificationsContainer');
    const inputId = formPrefix === 'product' ? 'addSpecificationsInput' : (formPrefix + 'SpecificationsInput');
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(inputId);
    if (!container) return;
    container.innerHTML = '';

    const template = this.specTemplates[categoryName] || [];

    // Render template fields
    template.forEach(key => {
        const row = document.createElement('div');
        row.className = 'spec-row';
        row.style.marginBottom = '8px';
        row.innerHTML = `
            <label style="display:block;font-size:13px;margin-bottom:4px;">${key}</label>
            <input type="text" class="spec-input" data-spec-key="${key}" value="${existingSpecs[key] ? existingSpecs[key] : ''}" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
        `;
        container.appendChild(row);
    });

    // If no template, show generic key/value pairs UI
    if (template.length === 0) {
        // First render existing specs if any
        const existingKeys = Object.keys(existingSpecs || {});
        if (existingKeys.length > 0) {
            existingKeys.forEach(key => {
                const row = document.createElement('div');
                row.className = 'spec-row';
                row.style.display = 'flex';
                row.style.gap = '8px';
                row.style.marginBottom = '8px';
                row.innerHTML = `
                    <input type="text" class="spec-key" placeholder="Tên thông số" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="${key}">
                    <input type="text" class="spec-value" placeholder="Giá trị" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="${existingSpecs[key] || ''}">
                `;
                container.appendChild(row);
            });
        } else {
            // Show 2 empty rows by default
            for (let i = 0; i < 2; i++) {
                const row = document.createElement('div');
                row.className = 'spec-row';
                row.style.display = 'flex';
                row.style.gap = '8px';
                row.style.marginBottom = '8px';
                row.innerHTML = `
                    <input type="text" class="spec-key" placeholder="Tên thông số" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="">
                    <input type="text" class="spec-value" placeholder="Giá trị" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="">
                `;
                container.appendChild(row);
            }
        }
        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'btn btn-secondary';
        addBtn.style.marginTop = '6px';
        addBtn.textContent = 'Thêm thông số';
        addBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'spec-row';
            row.style.display = 'flex';
            row.style.gap = '8px';
            row.style.marginBottom = '8px';
            row.innerHTML = `
                <input type="text" class="spec-key" placeholder="Tên thông số" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="">
                <input type="text" class="spec-value" placeholder="Giá trị" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" value="">
            `;
            container.insertBefore(row, addBtn);
        });
        container.appendChild(addBtn);
    }

    // Set hidden input initial value
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(existingSpecs || {});
    }
};

ProductManager.prototype.serializeSpecifications = function(formPrefix) {
    const containerId = formPrefix === 'product' ? 'addSpecificationsContainer' : (formPrefix + 'SpecificationsContainer');
    const inputId = formPrefix === 'product' ? 'addSpecificationsInput' : (formPrefix + 'SpecificationsInput');
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(inputId);
    if (!container || !hiddenInput) return;

    const obj = {};
    // template-based: inputs have class spec-input and data-spec-key
    const specInputs = container.querySelectorAll('.spec-input');
    if (specInputs.length) {
        specInputs.forEach(inp => {
            const key = inp.getAttribute('data-spec-key') || '';
            const val = inp.value || '';
            if (key) obj[key] = val;
        });
    } else {
        // generic key/value
        const keys = container.querySelectorAll('.spec-key');
        const vals = container.querySelectorAll('.spec-value');
        for (let i = 0; i < keys.length; i++) {
            const k = keys[i].value.trim();
            const v = vals[i].value.trim();
            if (k !== '') obj[k] = v;
        }
    }

    hiddenInput.value = JSON.stringify(obj);
};

// Initialize when DOM is loaded
let productManager;
document.addEventListener('DOMContentLoaded', function() {
    productManager = new ProductManager();
});

// Global functions for backwards compatibility
window.editProduct = function(productId, productName) {
    if (productManager) {
        productManager.showEditModal(productId);
    }
};

window.deleteProduct = function(productId, productName) {
    if (productManager) {
        productManager.deleteProduct(productId, productName);
    }
};
