<?php
// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'You need admin access to view this page.';
    header('Location: ../account.php');
    exit;
}

// Handle product operations
$message = '';

// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Get product info to delete image if exists
        $img_stmt = $connexion->prepare('SELECT image FROM products WHERE id = ?');
        $img_stmt->execute([$id]);
        $product = $img_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete product from database
        $stmt = $connexion->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        
        // Delete product image if exists
        if ($product && $product['image'] && file_exists('../' . $product['image'])) {
            unlink('../' . $product['image']);
        }
        
        $message = 'Product deleted successfully.';
    } catch (PDOException $e) {
        $message = 'Error deleting product: ' . $e->getMessage();
    }
}

// Get all categories for the filter
try {
    $cat_stmt = $connexion->query('SELECT * FROM categories ORDER BY name');
    $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching categories: ' . $e->getMessage();
    $categories = [];
}

// Handle filtering
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on filters
$query = 'SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE 1=1';
$params = [];

if ($category_filter > 0) {
    $query .= ' AND p.category_id = ?';
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $query .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$query .= ' ORDER BY p.name';

// Get products
try {
    $stmt = $connexion->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching products: ' . $e->getMessage();
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DermaShop Admin - Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="../css/main.css">
    <style>
        /* Admin Panel Styling */
        .global-notification {
            background-color: #1367ef;
            color: white;
            padding: 14px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
        
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
        
        .admin-header h1 {
            color: #1367ef;
            font-weight: 600;
            font-size: 28px;
        }
        
        .admin-layout {
            display: flex;
            gap: 20px;
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #f5f5f5;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: fit-content;
        }
        
        .admin-sidebar-title {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee0ea;
            color: #1367ef;
            font-weight: 600;
            font-size: 18px;
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
            padding: 12px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .admin-menu-link:hover {
            background-color: #e9e9e9;
            color: #1367ef;
        }
        
        .admin-menu-link.active {
            background-color: #1367ef;
            color: #fff;
        }
        
        .admin-menu-link i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .admin-content {
            flex: 1;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1367ef;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee0ea;
        }
        
        /* Filter Section */
        .filter-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        /* Tables */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        
        .product-table th, .product-table td {
            padding: 12px 15px;
            border: 1px solid #dee0ea;
            text-align: left;
        }
        
        .product-table th {
            background-color: #1367ef;
            color: white;
            font-weight: 500;
        }
        
        .product-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .product-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        /* Forms */
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #dee0ea;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #1367ef;
            outline: none;
        }
        
        /* Buttons */
        .btn-admin {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #1367ef;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d5ad6;
        }
        
        .btn-warning {
            background-color: #FF9800;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e68900;
        }
        
        .btn-danger {
            background-color: #ee403d;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #d63a35;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            display: inline-block;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-danger {
            background-color: #ee403d;
        }
        
        /* Add Product Button */
        .add-product-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Header section -->
    <header>
        <div class="global-notification">
            <div class="container">
                <p>
                    ADMIN DASHBOARD - MANAGE YOUR DERMASHOP STORE
                </p>
            </div>
        </div>
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
            <h1>Product Management</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="admin-layout">
            <div class="admin-sidebar">
                <h3 class="admin-sidebar-title">Admin Navigation</h3>
                <ul class="admin-menu-list">
                    <li class="admin-menu-item">
                        <a href="indexadmin.php" class="admin-menu-link">
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
                            <i class="bi bi-cart-check"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="admin-menu-item">
                        <a href="users.php" class="admin-menu-link">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="admin-content">
                <!-- Add Product Button -->
                <div class="add-product-btn">
                    <a href="add_product.php" class="btn-admin btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Product
                    </a>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" action="" style="width: 100%; display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                        <div class="filter-group">
                            <label for="category">Filter by Category:</label>
                            <select name="category" id="category" class="form-control">
                                <option value="0">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="search">Search Products:</label>
                            <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search by name or description">
                        </div>
                        
                        <button type="submit" class="btn-admin btn-primary">
                            <i class="bi bi-filter"></i> Apply Filters
                        </button>
                        
                        <?php if ($category_filter > 0 || !empty($search_query)): ?>
                            <a href="products.php" class="btn-admin btn-danger">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Products List -->
                <h2 class="section-title">Product List</h2>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No products found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php if (!empty($product['image']) && file_exists('../' . $product['image'])): ?>
                                            <img src="<?php echo '../' . $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                        <?php else: ?>
                                            <div class="product-img" style="display: flex; align-items: center; justify-content: center; background-color: #f1f1f1;">
                                                <i class="bi bi-image" style="font-size: 24px; color: #666;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?: 'No Category'); ?></td>
                                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <?php 
                                            $stock = $product['stock'];
                                            if ($stock > 10) {
                                                echo '<span class="badge badge-success">' . $stock . '</span>';
                                            } elseif ($stock > 0) {
                                                echo '<span class="badge badge-warning">' . $stock . '</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Out of stock</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-admin btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn-admin btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 