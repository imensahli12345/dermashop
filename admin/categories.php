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

// Handle category operations
$message = '';

// Delete category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Check if category has products
        $check_stmt = $connexion->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $check_stmt->execute([$id]);
        $product_count = $check_stmt->fetchColumn();
        
        if ($product_count > 0) {
            $message = 'Cannot delete category: it has ' . $product_count . ' products associated with it.';
        } else {
            $stmt = $connexion->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Category deleted successfully.';
        }
    } catch (PDOException $e) {
        $message = 'Error deleting category: ' . $e->getMessage();
    }
}

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Simple validation
    if (empty($name)) {
        $message = 'Category name is required!';
    } else {
        try {
            $stmt = $connexion->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
            $stmt->execute([$name, $description]);
            $message = 'Category added successfully.';
        } catch (PDOException $e) {
            $message = 'Error adding category: ' . $e->getMessage();
        }
    }
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Simple validation
    if (empty($name)) {
        $message = 'Category name is required!';
    } else {
        try {
            $stmt = $connexion->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $description, $id]);
            $message = 'Category updated successfully.';
        } catch (PDOException $e) {
            $message = 'Error updating category: ' . $e->getMessage();
        }
    }
}

// Get all categories
try {
    $stmt = $connexion->query('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY c.name');
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
    <title>DermaShop Admin - Categories</title>
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
        
        /* Tables */
        .category-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        
        .category-table th, .category-table td {
            padding: 12px 15px;
            border: 1px solid #dee0ea;
            text-align: left;
        }
        
        .category-table th {
            background-color: #1367ef;
            color: white;
            font-weight: 500;
        }
        
        .category-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .category-table tr:hover {
            background-color: #f1f1f1;
        }
        
        /* Forms */
        .form-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
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
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
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
        
        .badge-primary {
            background-color: #1367ef;
        }
        
        /* Edit Form */
        .edit-form {
            display: none;
            margin-top: 20px;
            border-top: 1px solid #dee0ea;
            padding-top: 20px;
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
                                    <a href="categories.php" class="menu-link active">Categories</a>
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
            <h1>Category Management</h1>
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
                        <a href="products.php" class="admin-menu-link">
                            <i class="bi bi-box-seam"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="admin-menu-item">
                        <a href="categories.php" class="admin-menu-link active">
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
                <!-- Category List -->
                <h2 class="section-title">Category List</h2>
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No categories found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description'] ?: 'No description'); ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $category['product_count']; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn-admin btn-warning edit-btn" 
                                               data-id="<?php echo $category['id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($category['name']); ?>" 
                                               data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        
                                        <?php if ($category['product_count'] == 0): ?>
                                            <a href="?delete=<?php echo $category['id']; ?>" class="btn-admin btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <button class="btn-admin btn-danger" disabled title="Cannot delete: category has products">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Add Category Form -->
                <div class="form-section">
                    <h2 class="section-title">Add New Category</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Category Name:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" name="add_category" class="btn-admin btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Category
                        </button>
                    </form>
                </div>

                <!-- Edit Category Form (hidden by default) -->
                <div class="form-section edit-form" id="edit-form">
                    <h2 class="section-title">Edit Category</h2>
                    <form method="POST" action="">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="form-group">
                            <label for="edit-name">Category Name:</label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-description">Description:</label>
                            <textarea id="edit-description" name="description" class="form-control"></textarea>
                        </div>
                        
                        <button type="submit" name="edit_category" class="btn-admin btn-primary">
                            <i class="bi bi-check-circle"></i> Update Category
                        </button>
                        <button type="button" class="btn-admin btn-danger cancel-edit">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit button click
            const editButtons = document.querySelectorAll('.edit-btn');
            const editForm = document.getElementById('edit-form');
            const cancelButton = document.querySelector('.cancel-edit');
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const description = this.getAttribute('data-description');
                    
                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-name').value = name;
                    document.getElementById('edit-description').value = description;
                    
                    editForm.style.display = 'block';
                    
                    // Scroll to edit form
                    editForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // Cancel edit
            cancelButton.addEventListener('click', function() {
                editForm.style.display = 'none';
            });
        });
    </script>
</body>
</html> 