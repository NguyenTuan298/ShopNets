# ShopNets Admin Panel - Structure Guide

## Cấu trúc mới với UI có thể tái sử dụng

### Files Include (Reusable Components)

1. **includes/header.php** - Chứa phần `<head>` và các meta tags, CSS links
2. **includes/sidebar.php** - Chứa sidebar menu và topbar (navigation)
3. **includes/footer.php** - Chứa phần đóng `</main>`, scripts và `</body>`

### Cách sử dụng trong mỗi trang:

```php
<?php 
$pageTitle = 'Page Name | ShopNets';  // Tiêu đề trang
$currentPage = 'page-name';            // Tên trang hiện tại (để highlight menu)
$baseUrl = '../';                      // Đường dẫn base URL (tùy theo vị trí file)
include '../includes/header.php';
include '../includes/sidebar.php';
?>

    <!-- Nội dung trang của bạn ở đây -->
    <section class="content">
      <!-- Your content -->
    </section>

<?php include '../includes/footer.php'; ?>
```

### Biến quan trọng:

- **$pageTitle**: Tiêu đề hiển thị trên tab trình duyệt
- **$currentPage**: Giá trị để highlight menu item đang active
  - 'dashboard' cho trang Dashboard
  - 'products' cho trang Products
  - 'categories' cho trang Categories
  - 'orders' cho trang Orders
  - 'users' cho trang Users
- **$baseUrl**: Đường dẫn relative để load assets
  - `''` (empty) cho các file ở thư mục admin/
  - `'../'` cho các file ở thư mục con (products/, categories/, etc.)

### Các trang đã được setup:

1. ✅ Dashboard (`admin/index.php`)
2. ✅ Products (`admin/products/index.php`)
3. ✅ Categories (`admin/categories/index.php`)
4. ✅ Orders (`admin/orders/index.php`)
5. ✅ Users (`admin/users/index.php`)
6. ✅ Settings (`admin/settings/index.php`)

### Navigation Links đã được cấu hình:

- Tất cả menu items trong sidebar đã có href đúng
- Menu item sẽ tự động được highlight khi `$currentPage` khớp
- Logout link: `login/logout.php`

### CSS Structure:

- Main styles: `assets/css/admin.css`
- Page-specific styles: `assets/css/pages/*.css`
- Products page styles đã được import vào `admin.css`

## Ưu điểm của cấu trúc mới:

1. **DRY (Don't Repeat Yourself)**: Không cần copy-paste HTML cho header, sidebar
2. **Easy Maintenance**: Sửa menu một chỗ, áp dụng cho tất cả trang
3. **Consistent UI**: Đảm bảo giao diện nhất quán trên toàn bộ admin panel
4. **Easy to Add New Pages**: Chỉ cần copy template và thay đổi nội dung chính
