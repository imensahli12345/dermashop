<?php
session_start();
include 'includes/config.php';

// Get search query
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

// If empty search term, redirect to products page
if (empty($search_term)) {
    header('Location: products.php');
    exit;
}

// Search products
try {
    $stmt = $connexion->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE ? OR p.description LIKE ?
        ORDER BY p.name
    ");
    $search_param = "%$search_term%";
    $stmt->execute([$search_param, $search_param]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

// Get all categories for the filter
try {
    $stmt = $connexion->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="css/main.css" />
    <title>DermaShop | Search Results for "<?php echo htmlspecialchars($search_term); ?>"</title>
    <style>
        .search-header {
            margin-bottom: 30px;
        }
        .search-form {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }
        .category-filter a {
            padding: 8px 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .category-filter a:hover, .category-filter a.active {
            background-color: #e8e8e8;
            color: #000;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        .product-card {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .product-image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .product-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-price {
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .product-actions {
            display: flex;
            justify-content: space-between;
        }
        .btn-view, .btn-add-cart {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-view {
            background-color: #f5f5f5;
            color: #333;
        }
        .btn-add-cart {
            background-color: #4CAF50;
            color: white;
        }
        .no-products {
            text-align: center;
            padding: 50px;
            background-color: #f5f5f5;
            border-radius: 5px;
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
                        <a href="index.php" class="logo">DermaShop</a>
                    </div>
                    <div class="header-center" id="sidebar">
                        <nav class="navigation">
                            <ul class="menu-list">
                                <li class="menu-list-item">
                                    <a href="index.php" class="menu-link">Home</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="products.php" class="menu-link">Products</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="account.php" class="menu-link">Account</a>
                                </li>
                                <?php if (isset($_SESSION['logged_in'])): ?>
                                <li class="menu-list-item">
                                    <a href="includes/logout.php" class="menu-link">Logout</a>
                                </li>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li class="menu-list-item">
                                    <a href="admin/products.php" class="menu-link">Admin</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <div class="header-right">
                        <div class="header-right-links">
                            <button class="search-button">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (isset($_SESSION['logged_in'])): ?>
                            <a href="cart.php" class="header-cart-link">
                                <i class="bi bi-bag"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Search Results section -->
    <section class="container">
        <div class="search-header">
            <h1>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h1>
            <p>Found <?php echo count($products); ?> products</p>
            
            <!-- Search form -->
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" class="search-input" placeholder="Search products...">
                <button type="submit" class="search-button">Search</button>
            </form>
            
            <!-- Category links -->
            <div class="category-filter">
                <a href="products.php">All Products</a>
                <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <h3>No products found matching "<?php echo htmlspecialchars($search_term); ?>"</h3>
                <p>Try different keywords or browse our product categories.</p>
            </div>
        <?php else: ?>
            <!-- Products grid -->
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="img/products/placeholder.jpg" alt="Product image placeholder">
                            <?php endif; ?>
                        </div>
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-category">Category: <?php echo htmlspecialchars($product['category_name']); ?></div>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view">View Details</a>
                            <?php if (isset($_SESSION['logged_in'])): ?>
                            <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="index.php" class="logo">DermaShop</a>
                </div>
                <div class="footer-info">
                    <p>&copy; <?php echo date('Y'); ?> DermaShop. Premium skincare products for healthy skin.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Add JavaScript for interaction -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add to cart functionality (placeholder for Phase 3)
            const addToCartButtons = document.querySelectorAll('.btn-add-cart');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    alert('Product will be added to cart. (Functionality coming soon)');
                });
            });

            // Update search modal to use the search page
            const searchButton = document.querySelector('.search-button');
            const searchModal = document.querySelector('.modal-search');
            
            if (searchButton && searchModal) {
                searchButton.addEventListener('click', function() {
                    searchModal.style.display = 'flex';
                });
            }
        });
    </script>
</body>
</html> 