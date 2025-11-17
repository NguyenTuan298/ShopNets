<?php 
$pageTitle = 'ShopNets';
$currentPage = 'products';
$baseUrl = '../';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<link rel="stylesheet" href="../assets/css/pages/products.css">

<style>
.btn-edit:hover {
    background: linear-gradient(135deg, #007bff, #0056b3) !important;
    border-color: rgba(0, 123, 255, 0.3) !important;
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
}
.btn-edit:hover img {
    transform: scale(1.1) rotate(5deg) !important;
}
.btn-delete:hover {
    background: linear-gradient(135deg, #dc3545, #c82333) !important;
    border-color: rgba(220, 53, 69, 0.3) !important;
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4) !important;
    animation: deleteShake 0.5s ease-in-out !important;
}
.btn-delete:hover img {
    transform: scale(1.1) rotate(-5deg) !important;
}
@keyframes deleteShake {
    0%, 100% { transform: translateY(-2px) scale(1.05) rotate(0deg); }
    25% { transform: translateY(-2px) scale(1.05) rotate(-1deg); }
    75% { transform: translateY(-2px) scale(1.05) rotate(1deg); }
}
.btn-icon:active {
    transform: translateY(-1px) scale(1.02) !important;
}
</style>

  <section class="content products-page">
    <h1>Quản Lý Sản Phẩm</h1>
      <div class="content-header">
        <span>Quản lý danh mục sản phẩm của bạn</span>
        <span>
            <button class="btn btn-primary" id="addProductBtn">
              <img src="../assets/images/icons/add.png" alt="Thêm" style="width: 20px;">
              Thêm Sản Phẩm Mới
            </button>
        </span>
      </div>

      <div class="card filter-card">
        <div class="filter-header">Tìm Kiếm & Lọc Sản Phẩm</div>
        <div class="filters-row">
          <div class="search-box">
            <form method="get" action="index.php">
              <?php if (isset($_GET['category']) && $_GET['category'] !== ''): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
              <?php endif; ?>
              <input type="text" name="q" value="<?php echo isset($_GET['q'])?htmlspecialchars($_GET['q']):''; ?>" placeholder="Tìm kiếm sản phẩm...">
              <button type="submit" style="display: none;"></button>
            </form>
          </div>
          <div class="select">
            <?php
            require_once __DIR__ . '/../includes/db_connect.php';
            $currentCategory = isset($_GET['category']) ? $_GET['category'] : '';
            $currentSearch = isset($_GET['q']) ? $_GET['q'] : '';
            
            try {
              $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
              $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (Exception $e) {
              $categories = [];
            }
            ?>
            <select onchange="location = this.value;">
              <?php
              $allCategoriesUrl = 'index.php';
              if ($currentSearch) {
                $allCategoriesUrl .= '?q=' . urlencode($currentSearch);
              }
              ?>
              <option value="<?php echo $allCategoriesUrl; ?>" <?php echo ($currentCategory === '') ? 'selected' : ''; ?>>Tất Cả Danh Mục</option>
              
              <?php foreach ($categories as $cat): ?>
                <?php
                $categoryUrl = 'index.php?category=' . urlencode($cat);
                if ($currentSearch) {
                  $categoryUrl .= '&q=' . urlencode($currentSearch);
                }
                ?>
                <option value="<?php echo $categoryUrl; ?>" <?php echo ($currentCategory === $cat) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($cat); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Tất Cả Sản Phẩm</h2>
        </div>
        
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Hình Ảnh</th>
                <th>Tên Sản Phẩm</th>
                <th>Danh Mục</th>
                <th>Giá</th>
                <th>Tồn Kho</th>
                <th>Hành Động</th>
              </tr>
            </thead>
            <tbody>
              <?php
              if (isset($_GET['msg'])) {
                  echo '<div class="alert alert-success">' . htmlspecialchars($_GET['msg']) . '</div>';
              }
              
              require_once __DIR__ . '/../includes/db_connect.php';

              $q = isset($_GET['q']) ? trim($_GET['q']) : '';
              $category = isset($_GET['category']) ? trim($_GET['category']) : '';
              
              try {
                $whereConditions = [];
                $params = [];
                
                if ($q !== '') {
                  $whereConditions[] = "(name LIKE :search OR category LIKE :search)";
                  $params[':search'] = "%$q%";
                }
                
                if ($category !== '') {
                  $whereConditions[] = "category = :category";
                  $params[':category'] = $category;
                }
                
                $whereClause = '';
                if (!empty($whereConditions)) {
                  $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
                }
                
                $sql = "SELECT * FROM products $whereClause ORDER BY id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
              } catch (Exception $e) {
                $products = [];
              }

              if (!empty($products)) {
                foreach ($products as $p) {
                  $id = isset($p['id']) ? $p['id'] : (isset($p['product_id']) ? $p['product_id'] : '');
                  $name = isset($p['name']) ? $p['name'] : (isset($p['product_name']) ? $p['product_name'] : '');
                  $category = isset($p['category']) ? $p['category'] : (isset($p['cat']) ? $p['cat'] : '');
                  $price = isset($p['price']) ? $p['price'] : (isset($p['unit_price']) ? $p['unit_price'] : '');
                  $inventory = isset($p['inventory']) ? $p['inventory'] : (isset($p['stock']) ? $p['stock'] : '');
                  $image = isset($p['image']) ? $p['image'] : '';

                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($id) . "</td>";
                  echo "<td style=\"padding-left: 12px;\">";
                  if ($image && file_exists("../assets/images/uploads/" . $image)) {
                    echo "<img src=\"../assets/images/uploads/" . htmlspecialchars($image) . "\" alt=\"Product Image\" style=\"width: 50px; height: 50px; object-fit: cover; border-radius: 4px;\">";
                  } else {
                    echo "<span style=\"color: #999; font-size: 12px;\">Không Có Hình</span>";
                  }
                  echo "</td>";
                  echo "<td>" . htmlspecialchars($name) . "</td>";
                  echo "<td>" . htmlspecialchars($category) . "</td>";
                  echo "<td>" . ($price !== '' ? number_format((float)$price) . ' VNĐ' : '') . "</td>";
                  echo "<td style=\"padding-left: 45px;\">" . htmlspecialchars($inventory) . "</td>";
                  echo "<td style=\"text-align: left;\" class=\"actions-cell\">";
                  echo "<button class=\"btn-icon btn-edit\" type=\"button\" onclick=\"editProduct(" . $id . ", '" . htmlspecialchars($name, ENT_QUOTES) . "')\" style=\"position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;\">";
                  echo "<img src=\"../assets/images/icons/edit.png\" alt=\"Edit\" style=\"width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;\">";
                  echo "</button>";
                  
                  echo "<button class=\"btn-icon btn-delete\" type=\"button\" onclick=\"deleteProduct(" . $id . ", '" . htmlspecialchars($name, ENT_QUOTES) . "')\" style=\"position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;\">";
                  echo "<img src=\"../assets/images/icons/delete.png\" alt=\"Delete\" style=\"width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;\">";
                  echo "</button>";
                  echo "</td>";
                  echo "</tr>";
                }
              } else {
                $noResultsMessage = 'Không tìm thấy sản phẩm nào.';
                if ($category !== '') {
                  $noResultsMessage = "Không tìm thấy sản phẩm trong danh mục: " . htmlspecialchars($category);
                } elseif ($q !== '') {
                  $noResultsMessage = "Không tìm thấy sản phẩm cho từ khóa: " . htmlspecialchars($q);
                }
                echo "<tr><td colspan=\"7\" style=\"text-align:center;\">$noResultsMessage</td></tr>";
              }

              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

<!-- Thêm Sản Phẩm Modal -->
<div id="addProductModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Thêm Sản Phẩm Mới</h2>
      <span class="close">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addProductForm" enctype="multipart/form-data">
        <div class="form-group">
          <label for="productName">Tên Sản Phẩm <span class="required">*</span></label>
          <input type="text" id="productName" name="name" required>
        </div>
        <div class="form-group">
          <label for="productCategory">Danh Mục <span class="required">*</span></label>
          <select id="productCategory" name="category" required>
            <option value="">Chọn danh mục</option>
            <?php
            try {
              $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
              $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
              foreach ($categories as $cat) {
                echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
              }
            } catch (Exception $e) {
              echo '<option value="">Lỗi tải danh mục</option>';
            }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label for="productPrice">Giá <span class="required">*</span></label>
          <input type="number" id="productPrice" name="price" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label for="productInventory" >Số Lượng Tồn <span class="required">*</span></label>
          <input type="number" id="productInventory" name="inventory" min="0">
        </div>
        <div class="form-group">
          <label for="productImage">Hình Ảnh Sản Phẩm</label>
          <input type="file" id="productImage" name="image" accept="image/*">
          <small style="color: #666; font-size: 12px;">Hỗ trợ định dạng: JPG, PNG, GIF (Tối đa: 5MB)</small>
        </div>
        <div class="form-group">
          <label for="productDescription">Mô Tả</label>
          <textarea id="productDescription" name="description" rows="3"></textarea>
        </div>
        <div class="error-messages" id="errorMessages"></div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <span class="btn-text">Lưu Sản Phẩm</span>
            <span class="loading-spinner" style="display: none;">
              <img src="../assets/images/icons/loading.gif" alt="Đang tải..." style="width: 16px;">
            </span>
          </button>
          <button type="button" class="btn btn-secondary" id="cancelBtn">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Chỉnh Sửa Sản Phẩm Modal -->
<div id="editProductModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Chỉnh Sửa Sản Phẩm</h2>
      <span class="close" id="editModalClose">&times;</span>
    </div>
    <div class="modal-body">
      <form id="editProductForm" enctype="multipart/form-data">
        <input type="hidden" id="editProductId" name="id">
        <div class="form-group">
          <label for="editProductName">Tên Sản Phẩm <span class="required">*</span></label>
          <input type="text" id="editProductName" name="name" required>
        </div>
        <div class="form-group">
          <label for="editProductCategory">Danh Mục <span class="required">*</span></label>
          <select id="editProductCategory" name="category" required>
            <option value="">Chọn danh mục</option>
            <?php
            try {
              $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
              $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
              foreach ($categories as $cat) {
                echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
              }
            } catch (Exception $e) {
              echo '<option value="">Lỗi tải danh mục</option>';
            }
            ?>
          </select>
        </div>
        <div class="form-group">
          <label for="editProductPrice">Giá <span class="required">*</span></label>
          <input type="number" id="editProductPrice" name="price" step="0.01" min="0">
        </div>
        <div class="form-group">
          <label for="editProductInventory">Số Lượng Tồn <span class="required">*</span></label>
          <input type="number" id="editProductInventory" name="inventory" min="0">
        </div>
        <div class="form-group">
          <label for="editProductImage">Hình Ảnh Sản Phẩm</label>
          <input type="file" id="editProductImage" name="image" accept="image/*">
          <small style="color: #666; font-size: 12px;">Bỏ trống để giữ hình hiện tại</small>
          <div id="currentImagePreview" style="margin-top: 10px;"></div>
        </div>
        <div class="form-group">
          <label for="editProductDescription">Mô Tả</label>
          <textarea id="editProductDescription" name="description" rows="3"></textarea>
        </div>
        <div class="error-messages" id="editErrorMessages"></div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <span class="btn-text">Cập Nhật Sản Phẩm</span>
            <span class="loading-spinner" style="display: none;">
              <img src="../assets/images/icons/loading.gif" alt="Đang tải..." style="width: 16px;">
            </span>
          </button>
          <button type="button" class="btn btn-secondary" id="editCancelBtn">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/js/product.js"></script>

<?php include '../includes/footer.php'; ?>
