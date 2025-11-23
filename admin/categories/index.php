<?php 
$pageTitle = 'ShopNets';
$currentPage = 'categories';
$baseUrl = '../';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<link rel="stylesheet" href="../assets/css/pages/categories.css">

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

  <section class="content categories-page">
      <div class="content-header">
        <h1>Quản Lý Danh Mục</h1>
        <button class="btn btn-primary" id="addCategoryBtn">
          <img src="../assets/images/icons/add.png" alt="Thêm">
          Thêm Danh Mục Mới
        </button>
      </div>

      <div class="card filter-card">
        <div class="filter-header">Tìm Kiếm Danh Mục</div>
        <div class="filters-row">
          <div class="search-box">
            <form method="get" action="index.php">
              <input type="text" name="q" value="<?php echo isset($_GET['q'])?htmlspecialchars($_GET['q']):''; ?>" placeholder="Tìm kiếm danh mục...">
              <button type="submit" style="display: none;"></button>
            </form>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2>Tất Cả Danh Mục</h2>
        </div>
        
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Hình Ảnh</th>
                <th>Tên Danh Mục</th>
                <th>Mô Tả</th>
                <th>Số Sản Phẩm</th>
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
              
              try {
                $whereConditions = [];
                $params = [];
                
                if ($q !== '') {
                  $whereConditions[] = "(name LIKE :search OR description LIKE :search)";
                  $params[':search'] = "%$q%";
                }
                
                $whereClause = '';
                if (!empty($whereConditions)) {
                  $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
                }
                
                $sql = "SELECT * FROM categories $whereClause ORDER BY id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
              } catch (Exception $e) {
                $categories = [];
              }

              if (!empty($categories)) {
                foreach ($categories as $c) {
                  $id = $c['id'];
                  $name = $c['name'];
                  $description = $c['description'] ?? '';
                  $image = $c['image'] ?? '';

                  try {
                    $countStmt = $pdo->prepare('SELECT COUNT(*) as count FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name = :category_name');
                    $countStmt->execute([':category_name' => $name]);
                    $productCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
                  } catch (Exception $e) {
                    $productCount = 0;
                  }

                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($id) . "</td>";
                  
                  // Display image
                  echo "<td style=\"text-align: center;\">";
                  if ($image) {
                    echo "<img src=\"../assets/images/uploads/categories/" . htmlspecialchars($image) . "\" alt=\"" . htmlspecialchars($name) . "\" style=\"width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;\">";
                  } else {
                    echo "<span style=\"color: #999; font-size: 12px;\">Không có ảnh</span>";
                  }
                  echo "</td>";
                  
                  echo "<td>" . htmlspecialchars($name) . "</td>";
                  echo "<td>" . htmlspecialchars($description) . "</td>";
                  echo "<td style=\"text-align: center;\">" . $productCount . "</td>";
                  echo "<td style=\"text-align: left;\" class=\"actions-cell\">";
                  echo "<button class=\"btn-icon btn-edit\" type=\"button\" onclick=\"editCategory(" . $id . ", '" . htmlspecialchars($name, ENT_QUOTES) . "')\" style=\"position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;\">";
                  echo "<img src=\"../assets/images/icons/edit.png\" alt=\"Edit\" style=\"width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;\">";
                  echo "</button>";
                  
                  echo "<button class=\"btn-icon btn-delete\" type=\"button\" onclick=\"deleteCategory(" . $id . ", '" . htmlspecialchars($name, ENT_QUOTES) . "')\" style=\"position: relative; padding: 8px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; transition: all 0.3s ease; margin: 0 3px; overflow: hidden;\">";
                  echo "<img src=\"../assets/images/icons/delete.png\" alt=\"Delete\" style=\"width: 18px; position: relative; z-index: 1; transition: all 0.3s ease;\">";
                  echo "</button>";
                  echo "</td>";
                  echo "</tr>";
                }
              } else {
                $noResultsMessage = 'Không tìm thấy danh mục nào.';
                if ($q !== '') {
                  $noResultsMessage = "Không tìm thấy danh mục cho từ khóa: " . htmlspecialchars($q);
                }
                echo "<tr><td colspan=\"6\" style=\"text-align:center;\">$noResultsMessage</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Thêm Danh Mục Mới</h2>
      <span class="close">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addCategoryForm" enctype="multipart/form-data">
        <div class="form-group">
          <label for="categoryName">Tên Danh Mục <span class="required">*</span></label>
          <input type="text" id="categoryName" name="name" required>
        </div>
        <div class="form-group">
          <label for="categoryDescription">Mô Tả</label>
          <textarea id="categoryDescription" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="categoryImage">Hình Ảnh</label>
          <input type="file" id="categoryImage" name="image" accept="image/*">
          <div id="imagePreview" style="margin-top: 10px; display: none;">
            <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
          </div>
        </div>
        <div class="error-messages" id="errorMessages"></div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <span class="btn-text">Lưu Danh Mục</span>
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

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Chỉnh Sửa Danh Mục</h2>
      <span class="close" id="editModalClose">&times;</span>
    </div>
    <div class="modal-body">
      <form id="editCategoryForm" enctype="multipart/form-data">
        <input type="hidden" id="editCategoryId" name="id">
        <div class="form-group">
          <label for="editCategoryName">Tên Danh Mục <span class="required">*</span></label>
          <input type="text" id="editCategoryName" name="name" required>
        </div>
        <div class="form-group">
          <label for="editCategoryDescription">Mô Tả</label>
          <textarea id="editCategoryDescription" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="editCategoryImage">Hình Ảnh</label>
          <input type="file" id="editCategoryImage" name="image" accept="image/*">
          <div id="editImagePreview" style="margin-top: 10px;">
            <img id="editPreviewImg" src="" alt="Current Image" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0; display: none;">
          </div>
        </div>
        <div class="error-messages" id="editErrorMessages"></div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <span class="btn-text">Cập Nhật Danh Mục</span>
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

<script src="../assets/js/category.js"></script>

<?php include '../includes/footer.php'; ?>
