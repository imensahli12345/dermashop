<?php
session_start();
include '../includes/config.php';

// Ensure the `image` column in `products` is a BLOB (e.g. LONGBLOB) in your database:
// ALTER TABLE products MODIFY image LONGBLOB;

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'You need admin access to view this page.';
    header('Location: ../account.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']);
    $description  = trim($_POST['description']);
    $price        = floatval($_POST['price']);
    $stock        = intval($_POST['stock']);
    $category_id  = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $is_featured  = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($name) || $price <= 0) {
        $message = 'Name and a valid price are required!';
    } else {
        // Read the uploaded image into a PHP string
        $image_data = null;
        if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
            if ($image_data === false) {
                $message = 'Error reading uploaded image.';
            }
        }

        if (empty($message)) {
            try {
                $stmt = $connexion->prepare('
                    INSERT INTO products
                      (name, description, price, stock, category_id, is_featured, image, created_at)
                    VALUES
                      (:name, :description, :price, :stock, :category_id, :is_featured, :image, NOW())
                ');
                $stmt->bindParam(':name',         $name);
                $stmt->bindParam(':description',  $description);
                $stmt->bindParam(':price',        $price);
                $stmt->bindParam(':stock',        $stock,       PDO::PARAM_INT);
                $stmt->bindParam(':category_id',  $category_id, PDO::PARAM_INT);
                $stmt->bindParam(':is_featured',  $is_featured, PDO::PARAM_INT);
                $stmt->bindParam(':image',        $image_data,  PDO::PARAM_LOB);
                $stmt->execute();

                $_SESSION['success'] = 'Product added successfully.';
                header('Location: products.php');
                exit;
            } catch (PDOException $e) {
                $message = 'Error adding product: ' . $e->getMessage();
            }
        }
    }
}

// Fetch categories for the dropdown
try {
    $stmt       = $connexion->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message    = 'Error fetching categories: ' . $e->getMessage();
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DermaShop Admin – Add Product</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../css/main.css">
  <style>
    /* … your existing CSS from admin page … */
    .form-control { /* … */ }
    .btn-admin.btn-primary { background-color: #4CAF50; color: #fff; }
    .message.error { background-color: #f2dede; color: #a94442; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    /* etc. */
  </style>
</head>
<body>
  <header>
    <!-- … your header/nav markup … -->
  </header>

  <div class="admin-container">
    <div class="admin-header">
      <h1>Add New Product</h1>
      <a href="products.php" class="btn-admin btn-secondary">Back to Products</a>
    </div>

    <?php if ($message): ?>
      <div class="message error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="admin-sidebar">
      <!-- … admin nav links … -->
    </div>

    <div class="admin-content">
      <a href="products.php" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to Products
      </a>
      <div class="form-section">
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name"
                   class="form-control"
                   value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                   required>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"
                      class="form-control"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
          </div>

          <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price"
                   class="form-control" step="0.01" min="0.01"
                   value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>"
                   required>
          </div>

          <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock"
                   class="form-control" min="0"
                   value="<?= isset($_POST['stock']) ? intval($_POST['stock']) : '0' ?>">
          </div>

          <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control">
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                  <?= isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image"
                   class="form-control" accept="image/*">
            <small>Max file size: 2MB.</small>
          </div>

          <div class="form-group checkbox-control">
            <label>
              <input type="checkbox" name="is_featured"
                <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
              Feature on homepage
            </label>
          </div>

          <div class="form-group">
            <button type="submit" class="btn-admin btn-primary">
              Add Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
