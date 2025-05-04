<?php
require 'includes/config.php';    // connexion PDO
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Récupérer tous les produits
$stmt = $connexion->prepare("
    SELECT id, name, price, image, stock
    FROM products
    ORDER BY created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- !bootstrap icon -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="css/main.css" />
  <!-- Custom CSS for Product Section -->
  <style>
    /* Products Section Styles */
    .products-section {
      padding: 60px 0;
      background-color: #f9f9f9;
    }
    .products-section .section-title {
      text-align: center;
      margin-bottom: 40px;
    }
    .products-section .section-title h2 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #333;
    }
    .products-section .section-title p {
      font-size: 1rem;
      color: #666;
    }
    .products-section .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }
    .products-section .product-card {
      display: block;
      background-color: #fff;
      border: 1px solid #eaeaea;
      border-radius: 5px;
      overflow: hidden;
      text-decoration: none;
      color: inherit;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .products-section .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }
    .products-section .product-card img {
      width: 100%;
      height: auto;
      display: block;
    }
    .products-section .product-info {
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .products-section .product-title {
      font-size: 1rem;
      margin: 0 0 5px;
      font-weight: 500;
      color: #333;
    }
    .products-section .product-price {
      font-size: 1.1rem;
      font-weight: bold;
      color: #000;
    }
    .products-section .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      text-transform: uppercase;
    }
    .products-section .badge-success {
      background-color: #28a745;
      color: #fff;
    }
    .products-section .badge-danger {
      background-color: #dc3545;
      color: #fff;
    }
    @media (max-width: 576px) {
      .products-section .products-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <!-- !Glide.js Css CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/css/glide.core.min.css" />
  <title>DermaShop | Premium Skincare Products</title>
</head>

<body>
  <!-- ! header start -->
  <header>
    <div class="global-notification">
      <div class="container">
        <p>
          SUMMER SALE FOR ALL DERMATOLOGICAL PRODUCTS AND FREE EXPRESS INTERNATIONAL
          DELIVERY - OFF 50%! <a href="shop.php">SHOP NOW</a>
        </p>
      </div>
    </div>
    <div class="header-row">
      <div class="container">
        <div class="header-wrapper">
          <div class="header-mobile">
            <i class="bi bi-list" id="btn-menu"></i>
          </div>
          <div class="header-left">
            <a href="index.php" class="logo">DermaShop</a>
          </div>
          <div class="header-center" id="sidebar">
            <nav class="navigation">
              <ul class="menu-list">
                <li class="menu-list-item">
                  <a href="index.php" class="menu-link active">Home
                    <i class="bi bi-chevron-down"></i>
                  </a>
                  <div class="menu-dropdown-wrapper">
                    <ul class="menu-dropdown-content">
                      <li><a href="#">Home Clean</a></li>
                      <li><a href="#">Home Collection</a></li>
                      <li><a href="#">Home Minimal</a></li>
                      <li><a href="#">Home Modern</a></li>
                      <li><a href="#">Home Parallax</a></li>
                      <li><a href="#">Home Strong</a></li>
                      <li><a href="#">Home Style</a></li>
                      <li><a href="#">Home Unique</a></li>
                      <li><a href="#">Home RTL</a></li>
                    </ul>
                  </div>
                </li>
                <li class="menu-list-item megamenu-wrapper">
                  <a href="shop.php" class="menu-link">Shop
                    <i class="bi bi-chevron-down"></i>
                  </a>
                  <div class="menu-dropdown-wrapper">
                    <div class="menu-dropdown-megamenu">
                      <div class="megamenu-links">
                        <div class="megamenu-products">
                          <h3 class="megamenu-product-title">Shop Style</h3>
                          <ul class="megamenu-menu-list">
                            <li><a href="#">Shop Standart</a></li>
                            <li><a href="#">Shop Full</a></li>
                            <li><a href="#">Shop Only Categories</a></li>
                            <li><a href="#">Shop Image Categories</a></li>
                            <li><a href="#">Shop Sub Categories</a></li>
                            <li><a href="#">Shop List</a></li>
                            <li><a href="#">Hover Style 1</a></li>
                            <li><a href="#">Hover Style 2</a></li>
                            <li><a href="#">Hover Style 3</a></li>
                          </ul>
                        </div>
                        <div class="megamenu-products">
                          <h3 class="megamenu-product-title">
                            Filter Layout
                          </h3>
                          <ul class="megamenu-menu-list">
                            <li><a href="#">Sidebar</a></li>
                            <li><a href="#">Filter Side Out</a></li>
                            <li><a href="#">Filter Dropdown</a></li>
                            <li><a href="#">Filter Drawer</a></li>
                          </ul>
                        </div>
                        <div class="megamenu-products">
                          <h3 class="megamenu-product-title">Shop Loader</h3>
                          <ul class="megamenu-menu-list">
                            <li><a href="#">Shop Pagination</a></li>
                            <li><a href="#">Shop Infinity</a></li>
                            <li><a href="#">Shop Load More</a></li>
                            <li><a href="#">Cart Modal</a></li>
                            <li><a href="#">Cart Drawer</a></li>
                            <li><a href="#">Cart Page</a></li>
                          </ul>
                        </div>
                      </div>
                      <div class="megamenu-single">
                        <a href="#">
                          <img src="img/mega-menu.jpg" alt="" />
                        </a>
                        <h3 class="megamenu-single-title">
                          JOIN THE LAYERING GANG
                        </h3>
                        <h4 class="megamenu-single-subtitle">
                          Suspendisse faucibus nunc et pellentesque
                        </h4>
                        <a href="#" class="megamenu-single-button btn btn-sm">Shop Now</a>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="menu-list-item">
                  <a href="blog.html" class="menu-link">Blog
                  </a>
                </li>
                <li class="menu-list-item">
                  <a href="contact.html" class="menu-link">Contact</a>
                </li>
                <li class="menu-list-item">
                  <a href="account.php" class="menu-link">Account</a>
                </li>
                <?php if (isset($_SESSION['logged_in'])): ?>
                <li class="menu-list-item">
                  <a href="includes/logout.php" class="menu-link">Logout</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="menu-list-item">
                  <a href="admin/products.php" class="menu-link">Admin</a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
              </ul>
            </nav>
            <i class="bi-x-circle" id="close-sidebar"></i>
          </div>
          <div class="header-right">
            <div class="header-right-links">
              <button class="search-button">
                <i class="bi bi-search"></i>
              </button>
              <a href="wishlist.php">
                <i class="bi bi-heart"></i>
              </a>
              <div class="header-cart">
                <a href="cart.php" class="header-cart-link">
                  <i class="bi bi-bag"></i>
                  <span class="header-cart-count">0</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  <!-- ! header end -->

  <!-- ! modal search start -->
  <div class="modal-search">
    <div class="modal-wrapper">
      <h3 class="modal-title">Search for products</h3>
      <p class="modal-text">
        Start typing to see products you are looking for.
      </p>
      <form action="search.php" method="GET">
        <div class="search">
          <input type="text" name="q" placeholder="Search a product" />
          <button type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>
      <i class="bi bi-x-circle" id="close-modal-search"></i>
    </div>
  </div>
  <!-- ! modal search end -->
  <!-- ! modal dialog start -->
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-close">
        <i class="bi bi-x"></i>
      </div>
      <div class="modal-image">
        <img src="img/modal-dialog.jpg" alt="modal">
      </div>
      <div class="popup-wrapper">
        <div class="popup-content">
          <div class="popup-title">
            <h3>NEWSLETTER</h3>
          </div>
          <p class="popup-text">
            Sign up to our newsletter and get exclusive deals you won find any where else straight to your inbox!
          </p>
          <form class="popup-form">
            <input type="text" placeholder="Enter Email Address Here">
            <button class="btn btn-primary">SUBSCRIBE</button>
            <label>
              <input type="checkbox">
              <span>Don't show this popup again</span>
            </label>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- ! modal dialog end -->

  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
  <!-- ! Admin Sidebar start -->
  <div class="admin-sidebar">
    <div class="admin-sidebar-wrapper">
      <h3 class="admin-sidebar-title">Admin Panel</h3>
      <ul class="admin-menu-list">
        <li class="admin-menu-item">
          <a href="admin/products.php" class="admin-menu-link">
            <i class="bi bi-box-seam"></i>
            <span>Products Management</span>
          </a>
        </li>
        <li class="admin-menu-item">
          <a href="admin/categories.php" class="admin-menu-link">
            <i class="bi bi-tags"></i>
            <span>Categories Management</span>
          </a>
        </li>
        <li class="admin-menu-item">
          <a href="admin/dashboard.php" class="admin-menu-link">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="admin-menu-item">
          <a href="admin/orders.php" class="admin-menu-link">
            <i class="bi bi-bag-check"></i>
            <span>Orders Management</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
  <!-- ! Admin Sidebar end -->

  <style>
    .admin-sidebar {
      position: fixed;
      left: 0;
      top: 120px;
      width: 250px;
      height: calc(100vh - 120px);
      background-color: #333;
      z-index: 100;
      overflow-y: auto;
      transition: all 0.3s ease;
    }
    .admin-sidebar-wrapper {
      padding: 20px 15px;
    }
    .admin-sidebar-title {
      color: #fff;
      font-size: 20px;
      padding-bottom: 15px;
      margin-bottom: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .admin-menu-list {
      list-style-type: none;
      padding: 0;
      margin: 0;
    }
    .admin-menu-item {
      margin-bottom: 5px;
    }
    .admin-menu-link {
      display: flex;
      align-items: center;
      padding: 10px;
      color: #ddd;
      text-decoration: none;
      border-radius: 4px;
      transition: all 0.2s;
    }
    .admin-menu-link:hover {
      background-color: rgba(255,255,255,0.1);
      color: #fff;
    }
    .admin-menu-link i {
      margin-right: 10px;
      font-size: 18px;
    }
    
    /* Adjust main content when admin sidebar is present */
    .admin-mode .slider,
    .admin-mode .categories,
    .admin-mode .products,
    .admin-mode .campaigns,
    .admin-mode .blogs,
    .admin-mode .brands,
    .admin-mode .campaign-single,
    .admin-mode .policy,
    .admin-mode .footer {
      margin-left: 250px;
      width: calc(100% - 250px);
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add admin-mode class to body when admin is logged in
      document.body.classList.add('admin-mode');
    });
  </script>
  <?php endif; ?>

  <!-- ! slider start -->
  <section class="slider">
    <div class="slider-elements">
      <div class="slider-item fade">
        <div class="slider-image">
          <img src="img/slider/slider1.jpg" class="img-fluid" alt="" />
        </div>
        <div class="container">
          <p class="slider-title">SUMMER 2022</p>
          <h2 class="slider-heading">Save up to 70%</h2>
          <a href="#" class="btn btn-lg btn-primary">Explore Now</a>
        </div>
      </div>
      <div class="slider-item fade">
        <div class="slider-image">
          <img src="img/slider/slider2.jpg" class="img-fluid" alt="" />
        </div>
        <div class="container">
          <p class="slider-title">SUMMER 2022</p>
          <h2 class="slider-heading">Save up to 70%</h2>
          <a href="#" class="btn btn-lg btn-primary">Explore Now</a>
        </div>
      </div>
      <div class="slider-item fade">
        <div class="slider-image">
          <img src="img/slider/slider3.jpg" class="img-fluid" alt="" />
        </div>
        <div class="container">
          <p class="slider-title">SUMMER 2022</p>
          <h2 class="slider-heading">Save up to 70%</h2>
          <a href="#" class="btn btn-lg btn-primary">Explore Now</a>
        </div>
      </div>
      <div class="slider-buttons">
        <button onclick="plusSlide(-1)">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button onclick="plusSlide(1)">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
      <div class="slider-dots">
        <button class="slider-dot active" onclick="currentSlide(1)">
          <span></span>
        </button>
        <button class="slider-dot" onclick="currentSlide(2)">
          <span></span>
        </button>
        <button class="slider-dot" onclick="currentSlide(3)">
          <span></span>
        </button>
      </div>
    </div>
  </section>
  <!-- ! slider end -->

  <!-- ! category start -->
  <section class="categories">
    <div class="container">
      <div class="section-title">
        <h2>All Categories</h2>
        <p>Summer Collection New Modern Design</p>
      </div>

      <ul class="category-list">
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories1.png" alt="" class="category-image" />
            <span class="category-title">Smartphone</span>
          </a>
        </li>
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories2.png" alt="" class="category-image" />
            <span class="category-title">Watches</span>
          </a>
        </li>
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories3.png" alt="" class="category-image" />
            <span class="category-title">Electronics</span>
          </a>
        </li>
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories4.png" alt="" class="category-image" />
            <span class="category-title">Furnitures</span>
          </a>
        </li>
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories5.png" alt="" class="category-image" />
            <span class="category-title">Collections</span>
          </a>
        </li>
        <li class="category-item">
          <a href="#">
            <img src="img/categories/categories6.png" alt="" class="category-image" />
            <span class="category-title">Fashion</span>
          </a>
        </li>
      </ul>
    </div>
  </section>
  <!-- ! category end-->

  <!-- ! PRODUCTS SECTION START -->
 
<!-- PRODUCTS SECTION START -->
<section class="products-section">
  <div class="container">
    <div class="section-title">
      <h2>All Products</h2>
      <p>Découvrez tous nos produits</p>
    </div>
    <div class="products-grid">
      <?php if ($products): ?>
        <?php foreach ($products as $prod): ?>
          <a href="product.php?id=<?= (int)$prod['id'] ?>" class="product-card">
            <!-- fetch image binary from DB via a separate endpoint -->
            <img
              src="get_image.php?id=<?= (int)$prod['id'] ?>"
              alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>"
              loading="lazy"
            />
            <div class="product-info">
              <div>
                <h3 class="product-title">
                  <?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>
                </h3>
                <div class="product-price">
                  $<?= number_format($prod['price'], 2) ?>
                </div>
              </div>
              <?php if ($prod['stock'] > 0): ?>
                <span class="badge badge-success">En stock</span>
              <?php else: ?>
                <span class="badge badge-danger">Rupture de stock</span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Aucun produit disponible pour le moment.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<!-- PRODUCTS SECTION END -->

<style>
  /* Force uniform image sizing */
  .products-section .product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
  }
</style>


  <!-- ! PRODUCTS SECTION END -->

  <!-- ! campaigns start -->
  <section class="campaigns">
    <div class="container">
      <div class="campaigns-wrapper">
        <div class="campaign-item">
          <h3 class="campaign-title">
            Fashion Month <br />
            Ready in Capital <br />
            Shop
          </h3>
          <p class="campaing-desc">
            Lorem ipsum dolor sit amet consectetur adipiscing elit dolor
          </p>
          <a href="#" class="btn btn-primary">
            View All
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="campaign-item">
          <h3 class="campaign-title">
            Fashion Month <br />
            Ready in Capital <br />
            Shop
          </h3>
          <p class="campaing-desc">
            Lorem ipsum dolor sit amet consectetur adipiscing elit dolor
          </p>
          <a href="#" class="btn btn-primary">
            View All
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
      <div class="campaigns-wrapper">
        <div class="campaign-item">
          <h3 class="campaign-title">
            Fashion Month <br />
            Ready in Capital <br />
            Shop
          </h3>
          <p class="campaing-desc">
            Lorem ipsum dolor sit amet consectetur adipiscing elit dolor
          </p>
          <a href="#" class="btn btn-primary">
            View All
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="campaign-item">
          <h3 class="campaign-title">
            Fashion Month <br />
            Ready in Capital <br />
            Shop
          </h3>
          <p class="campaing-desc">
            Lorem ipsum dolor sit amet consectetur adipiscing elit dolor
          </p>
          <a href="#" class="btn btn-primary">
            View All
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </section>
  <!-- ! campaigns end -->

  <!-- ! product start -->
  <section class="products">
    <div class="container">
      <div class="section-title">
        <h2>New Arrivals</h2>
        <p>Summer Collection New Modern Design</p>
      </div>
      <div class="product-wrapper product-carousel2">
        <div class="glide__track" data-glide-el="track">
          <ul class="product-list glide__slides" id="product-list-2">
            <li class="product-item glide__slide">
              <div class="product-image">
                <a href="#">
                  <img src="img/products/product1/1.png" alt="" class="img1" />
                  <img src="img/products/product1/2.png" alt="" class="img2" />
                </a>
              </div>
              <div class="product-info">
                <a href="#" class="product-title"> Analogue Resin Strap </a>
                <ul class="product-star">
                  <li>
                    <i class="bi bi-star-fill"></i>
                  </li>
                  <li>
                    <i class="bi bi-star-fill"></i>
                  </li>
                  <li>
                    <i class="bi bi-star-fill"></i>
                  </li>
                  <li>
                    <i class="bi bi-star-fill"></i>
                  </li>
                  <li>
                    <i class="bi bi-star-half"></i>
                  </li>
                </ul>
                <div class="product-prices">
                  <strong class="new-price">$108.00</strong>
                  <span class="old-price">$165</span>
                </div>
                <span class="product-discount"> -17% </span>
                <div class="product-links">
                  <button>
                    <i class="bi bi-basket-fill"></i>
                  </button>
                  <button>
                    <i class="bi bi-heart-fill"></i>
                  </button>
                  <a href="#">
                    <i class="bi bi-eye-fill"></i>
                  </a>
                  <a href="#">
                    <i class="bi bi-share-fill"></i>
                  </a>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <div class="glide__arrows" data-glide-el="controls">
          <button class="glide__arrow glide__arrow--left" data-glide-dir="<">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button class="glide__arrow glide__arrow--right" data-glide-dir=">">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </section>
  <!-- ! product end -->

  <!-- ! blogs start  -->
  <section class="blogs">
    <div class="container">
      <div class="section-title">
        <h2>From Our Blog</h2>
        <p>Summer Collection New Modern Design</p>
      </div>
      <ul class="blog-list">
        <li class="blog-item">
          <a href="blog.html" class="blog-image">
            <img src="img/blogs/blog1.jpg" alt="" />
          </a>
          <div class="blog-info">
            <div class="blog-info-top">
              <span>25 Feb, 2021</span>
              -
              <span>0 Comments</span>
            </div>
            <div class="blog-info-center">
              <a href="blog.html"> Aliquam hendrerit mi metus </a>
            </div>
            <div class="blog-info-bottom">
              <a href="blog.html">Read More</a>
            </div>
          </div>
        </li>
        <li class="blog-item">
          <a href="blog.html" class="blog-image">
            <img src="img/blogs/blog2.jpg" alt="" />
          </a>
          <div class="blog-info">
            <div class="blog-info-top">
              <span>25 Feb, 2021</span>
              -
              <span>0 Comments</span>
            </div>
            <div class="blog-info-center">
              <a href="blog.html"> Aliquam hendrerit mi metus </a>
            </div>
            <div class="blog-info-bottom">
              <a href="blog.html">Read More</a>
            </div>
          </div>
        </li>
        <li class="blog-item">
          <a href="blog.html" class="blog-image">
            <img src="img/blogs/blog3.jpg" alt="" />
          </a>
          <div class="blog-info">
            <div class="blog-info-top">
              <span>25 Feb, 2021</span>
              -
              <span>0 Comments</span>
            </div>
            <div class="blog-info-center">
              <a href="blog.html"> Aliquam hendrerit mi metus </a>
            </div>
            <div class="blog-info-bottom">
              <a href="blog.html">Read More</a>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </section>
  <!-- ! blogs end -->

  <!-- ! brands start  -->
  <section class="brands">
    <div class="container">
      <ul class="brand-list">
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand1.png" alt="" />
          </a>
        </li>
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand2.png" alt="" />
          </a>
        </li>
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand3.png" alt="" />
          </a>
        </li>
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand4.png" alt="" />
          </a>
        </li>
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand5.png" alt="" />
          </a>
        </li>
        <li class="brand-item">
          <a href="#">
            <img src="img/brands/brand1.png" alt="" />
          </a>
        </li>
      </ul>
    </li></ul></body></html>
