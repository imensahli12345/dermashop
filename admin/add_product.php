<?php
session_start();
include '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'You need admin access to view this page.';
    header('Location: ../account.php');
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || $price <= 0) {
        $message = 'Name and a valid price are required!';
    } else {
        // Handle image upload if provided
        $image_name = null;
        if (!empty($_FILES['image']['name'])) {
            $image_temp = $_FILES['image']['tmp_name'];
            $image_name = time() . '_' . $_FILES['image']['name'];
            $upload_dir = '../img/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($image_temp, $upload_dir . $image_name)) {
                // Image uploaded successfully
            } else {
                $message = 'Error uploading image.';
                $image_name = null;
            }
        }
        
        if (empty($message)) {
            try {
                $stmt = $connexion->prepare('
                    INSERT INTO products (name, description, price, stock, category_id, is_featured, image, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ');
                $stmt->execute([$name, $description, $price, $stock, $category_id, $is_featured, $image_name]);
                
                $_SESSION['success'] = 'Product added successfully.';
                header('Location: products.php');
                exit;
            } catch (PDOException $e) {
                $message = 'Error adding product: ' . $e->getMessage();
            }
        }
    }
}

// Get all categories
try {
    $stmt = $connexion->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching categories: ' . $e->getMessage();
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DermaShop Admin - Add Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .form-section {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #4CAF50;
            outline: none;
        }
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        .checkbox-control {
            margin-top: 5px;
        }
        .checkbox-control input {
            margin-right: 8px;
        }
        .btn-admin {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #607D8B;
            color: white;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .admin-sidebar {
            display: inline-block;
            vertical-align: top;
            width: 250px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            margin-right: 20px;
        }
        .admin-sidebar-title {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .admin-menu-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .admin-menu-item {
            margin-bottom: 10px;
        }
        .admin-menu-link {
            display: flex;
            align-items: center;
            padding: 8px 10px;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        .admin-menu-link:hover, .admin-menu-link.active {
            background-color: #e9e9e9;
        }
        .admin-menu-link i {
            margin-right: 10px;
        }
        .admin-content {
            display: inline-block;
            vertical-align: top;
            width: calc(100% - 275px);
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            text-decoration: none;
            color: #333;
        }
        .back-link i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Header section -->
    <header>
        <div class="header-row">
            <div class="container">
                <div class="header-wrapper">
                    <div class="header-left">
                        <a href="../index.php" class="logo">DermaShop</a>
                    </div>
                    <div class="header-right">
                        <nav class="navigation">
                            <ul class="menu-list">
                                <li class="menu-list-item">
                                    <a href="../index.php" class="menu-link">Home</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="products.php" class="menu-link active">Products</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="categories.php" class="menu-link">Categories</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="../includes/logout.php" class="menu-link">Logout</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Add New Product</h1>
            <a href="products.php" class="btn-admin btn-secondary">Back to Products</a>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="admin-sidebar">
            <h3 class="admin-sidebar-title">Admin Navigation</h3>
            <ul class="admin-menu-list">
                <li class="admin-menu-item">
                    <a href="dashboard.php" class="admin-menu-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="products.php" class="admin-menu-link active">
                        <i class="bi bi-box-seam"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="categories.php" class="admin-menu-link">
                        <i class="bi bi-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="orders.php" class="admin-menu-link">
                        <i class="bi bi-bag-check"></i>
                        <span>Orders</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="admin-content">
            <a href="products.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
            
            <div class="form-section">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo isset($_POST['stock']) ? intval($_POST['stock']) : '10'; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-control">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small>Recommended size: 600x600 pixels. Max size: 2MB.</small>
                    </div>
                    
                    <div class="form-group checkbox-control">
                        <label>
                            <input type="checkbox" name="is_featured" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                            Feature this product on the homepage
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-admin btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 