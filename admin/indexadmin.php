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

// Get some basic stats for the dashboard
try {
    // Total products
    $product_stmt = $connexion->query('SELECT COUNT(*) FROM products');
    $total_products = $product_stmt->fetchColumn();
    
    // Total categories
    $cat_stmt = $connexion->query('SELECT COUNT(*) FROM categories');
    $total_categories = $cat_stmt->fetchColumn();
    
    // Low stock products
    $low_stock_stmt = $connexion->query('SELECT COUNT(*) FROM products WHERE stock <= 5 AND stock > 0');
    $low_stock = $low_stock_stmt->fetchColumn();
    
    // Out of stock products
    $out_stock_stmt = $connexion->query('SELECT COUNT(*) FROM products WHERE stock = 0');
    $out_of_stock = $out_stock_stmt->fetchColumn();
    
    // Recent products
    $recent_stmt = $connexion->query('SELECT p.*, c.name as category_name FROM products p
                                     LEFT JOIN categories c ON p.category_id = c.id
                                     ORDER BY p.id DESC LIMIT 5');
    $recent_products = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = 'Error fetching dashboard data: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DermaShop Admin - Dashboard</title>
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
        
        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(19, 103, 239, 0.1);
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: #1367ef;
            font-size: 24px;
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1367ef;
        }
        
        .stat-label {
            font-size: 14px;
            color: #777;
        }
        
        /* Recent Products */
        .recent-products {
            margin-bottom: 30px;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
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
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .quick-link-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
        }
        
        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(19, 103, 239, 0.1);
            color: #1367ef;
        }
        
        .quick-link-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #1367ef;
        }
        
        .quick-link-title {
            font-size: 16px;
            font-weight: 500;
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
        
        /* Welcome Section */
        .welcome-section {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 5px solid #1367ef;
        }
        
        .welcome-title {
            font-size: 24px;
            color: #1367ef;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .welcome-text {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
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
                                    <a href="products.php" class="menu-link">Products</a>
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
            <h1>Admin Dashboard</h1>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="admin-layout">
            <div class="admin-sidebar">
                <h3 class="admin-sidebar-title">Admin Navigation</h3>
                <ul class="admin-menu-list">
                    <li class="admin-menu-item">
                        <a href="indexadmin.php" class="admin-menu-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="admin-menu-item">
                        <a href="products.php" class="admin-menu-link">
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
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h2 class="welcome-title">Welcome to DermaShop Admin Panel</h2>
                    <p class="welcome-text">
                        From here you can manage your store's products, categories, track orders, and more. 
                        Use the dashboard to get a quick overview of your store's performance.
                    </p>
                </div>
                
                <!-- Stats Section -->
                <h2 class="section-title">Store Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $total_products; ?></div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo $total_categories; ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #ffc107; background-color: rgba(255, 193, 7, 0.1);">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" style="color: #ffc107;"><?php echo $low_stock; ?></div>
                            <div class="stat-label">Low Stock Items</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="color: #ee403d; background-color: rgba(238, 64, 61, 0.1);">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value" style="color: #ee403d;"><?php echo $out_of_stock; ?></div>
                            <div class="stat-label">Out of Stock</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Access Links -->
                <h2 class="section-title">Quick Access</h2>
                <div class="quick-links">
                    <a href="add_product.php" class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="bi bi-plus-circle"></i>
                        </div>
                        <div class="quick-link-title">Add New Product</div>
                    </a>
                    
                    <a href="categories.php" class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="bi bi-folder-plus"></i>
                        </div>
                        <div class="quick-link-title">Manage Categories</div>
                    </a>
                    
                    <a href="../index.php" class="quick-link-card">
                        <div class="quick-link-icon">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="quick-link-title">View Store</div>
                    </a>
                </div>
                
                <!-- Recent Products -->
                <h2 class="section-title">Recent Products</h2>
                <div class="recent-products">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_products)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No products found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($product['image']) && file_exists('../' . $product['image'])): ?>
                                                <img src="<?php echo '../' . $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                            <?php else: ?>
                                                <div class="product-img" style="display: flex; align-items: center; justify-content: center; background-color: #f1f1f1;">
                                                    <i class="bi bi-image" style="font-size: 16px; color: #666;"></i>
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
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn-admin btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="bi bi-pencil"></i> Edit
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
    </div>
</body>
</html> 