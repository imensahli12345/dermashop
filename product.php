<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Validation et récupération de l’ID
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de produit invalide.");
}
$id = (int)$_GET['id'];

// 2) Récupération du produit
$stmt = $connexion->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    die("Produit introuvable.");
}

// 3) Vérifier si liké (pour cœur)
$isLiked = false;
if (!empty($_SESSION['user_id'])) {
    $chk = $connexion->prepare("
      SELECT 1 FROM liked_products 
      WHERE user_id = ? AND product_id = ?
    ");
    $chk->execute([$_SESSION['user_id'], $id]);
    $isLiked = (bool)$chk->fetchColumn();
}

// 4) Nombre d’articles dans le panier actif
$cartCount = 0;
if (!empty($_SESSION['user_id'])) {
    $getCart = $connexion->prepare("
      SELECT id FROM paniers 
      WHERE user_id = ? AND status = 'active'
    ");
    $getCart->execute([$_SESSION['user_id']]);
    $panierId = $getCart->fetchColumn();
    if ($panierId) {
        $cnt = $connexion->prepare("
          SELECT COALESCE(SUM(quantity),0) FROM panier_items
          WHERE panier_id = ?
        ");
        $cnt->execute([$panierId]);
        $cartCount = (int)$cnt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($product['name']) ?> | DermaShop</title>
    <!-- !bootstrap icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
    <!-- !Glide.js Css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.6.0/css/glide.core.min.css" />
    <link rel="stylesheet" href="css/main.css" />
    <style>
      /* highlight the filled heart */
      .text-danger { color: #e60023 !important; }
    </style>
</head>

<body>
    <!-- ! header start -->
    <header>
        <div class="global-notification">
            <div class="container">
                <p>
                    SUMMER SALE FOR ALL SWIM SUITS AND FREE EXPRESS INTERNATIONAL
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
                        <a href="/" class="logo">LOGO</a>
                    </div>
                    <div class="header-center" id="sidebar">
                        <nav class="navigation">
                            <ul class="menu-list">
                                <li class="menu-list-item">
                                    <a href="/" class="menu-link">Home
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
                                    <a href="shop.html" class="menu-link active">Shop
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
                                                    <h3 class="megamenu-product-title">Filter Layout</h3>
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
                                    <a href="blog.html" class="menu-link">Blog</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="contact.html" class="menu-link">Contact</a>
                                </li>
                            </ul>
                        </nav>
                        <i class="bi-x-circle" id="close-sidebar"></i>
                    </div>
                    <div class="header-right">
                        <div class="header-right-links">
                            <a href="account.php"><i class="bi bi-person"></i></a>
                            <button class="search-button"><i class="bi bi-search"></i></button>
                            <button id="wishlist-btn" class="btn btn-link p-0 mx-2" type="button">
                                <i id="wishlist-icon" class="bi <?= $isLiked ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
                            </button>
                            <div class="header-cart">
                                <a href="cart.php" class="header-cart-link">
                                    <i class="bi bi-bag"></i>
                                    <span class="header-cart-count"><?= $cartCount ?></span>
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
            <p class="modal-text">Start typing to see products you are looking for.</p>
            <div class="search">
                <input type="text" placeholder="Search a product" />
                <button><i class="bi bi-search"></i></button>
            </div>
            <div class="search-result">
                <div class="search-heading"><h3>RESULT FROM PRODUCT</h3></div>
                <div class="results"></div>
            </div>
            <i class="bi bi-x-circle" id="close-modal-search"></i>
        </div>
    </div>
    <!-- ! modal search end -->

    <!-- ! single product start -->
    <section class="single-product">
        <div class="container">
            <div class="single-product-wrapper">
                <!-- breadcrumb start -->
                <div class="single-topbar">
                    <nav class="breadcrumb">
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <?php if ($product['category_name']): ?>
                            <li><a href="shop.php?category=<?= $product['category_id'] ?>">
                              <?= htmlspecialchars($product['category_name']) ?></a></li>
                            <?php endif; ?>
                            <li><?= htmlspecialchars($product['name']) ?></li>
                        </ul>
                    </nav>
                </div>
                <!-- breadcrumb end -->

                <!-- site main start -->
                <div class="single-content">
                    <main class="site-main">
                        <div class="product-gallery">
                            <div class="single-image-wrapper">
                            <?php if (!empty($product) && isset($product['id'])): ?>
  <img src="get_image.php?id=<?= $product['id'] ?>"
       alt="<?= htmlspecialchars($product['name'],ENT_QUOTES) ?>">
<?php endif; ?>

                            </div>
                            <div class="product-thumb">
                                <div class="glide__track" data-glide-el="track">
                                    <ol class="gallery-thumbs glide_slides"></ol>
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
                        <div class="product-info">
                            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                            <div class="product-review">
                                <ul class="product-star">
                                    <li><i class="bi bi-star-fill"></i></li>
                                    <li><i class="bi bi-star-fill"></i></li>
                                    <li><i class="bi bi-star-fill"></i></li>
                                    <li><i class="bi bi-star-fill"></i></li>
                                    <li><i class="bi bi-star-half"></i></li>
                                </ul>
                                <span>0 reviews</span>
                            </div>
                            <div class="product-price">
                                <strong class="new-price">$<?= number_format($product['price'],2) ?></strong>
                            </div>
                            <p class="product-description">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </p>
                            <form class="variations-form">
                                <div class="variations">
                                    <div class="colors">
                                        <div class="colors-label"><span>Color</span></div>
                                        <div class="colors-wrapper">
                                            <div class="color-wrapper">
                                                <label class="blue-color">
                                                    <input type="radio" name="product-color">
                                                </label>
                                            </div>
                                            <div class="color-wrapper">
                                                <label class="red-color">
                                                    <input type="radio" name="product-color">
                                                </label>
                                            </div>
                                            <div class="color-wrapper">
                                                <label class="yellow-color">
                                                    <input type="radio" name="product-color">
                                                </label>
                                            </div>
                                            <div class="color-wrapper">
                                                <label class="green-color">
                                                    <input type="radio" name="product-color">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="values">
                                        <div class="values-labe"><span>Size</span></div>
                                        <div class="values-list">
                                            <span>XS</span>
                                            <span>S</span>
                                            <span>L</span>
                                            <span>M</span>
                                            <span>XL</span>
                                            <span>XXL</span>
                                        </div>
                                    </div>
                                    <div class="cart-button d-flex align-items-center">
                                        <input id="quantity" type="number" value="1" min="1"
                                               max="<?= $product['stock'] ?>" class="form-control me-2" style="width:80px;">
                                        <button id="add-to-cart" class="btn btn-lg btn-primary" type="button">
                                            Add to cart
                                        </button>
                                    </div>
                                    <div class="product-extra-buttons mt-3">
                                        <a href="#">
                                            <i class="bi bi-globe"></i>
                                            <span>Size Guide</span>
                                        </a>
                                        <a href="#" id="wishlist-btn-2" class="add-to-wishlist"
                                           data-product-id="<?= $product['id'] ?>">
                                            <i id="wishlist-icon-2" class="bi <?= $isLiked ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
                                            <span>Add to Wishlist</span>
                                        </a>
                                        <a href="share.php?product=<?= $product['id'] ?>">
                                            <i class="bi bi-share"></i>
                                            <span>Share this Product</span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <div class="divider"></div>
                            <div class="product-meta">
                                <div class="product-sku">
                                    <span>SKU:</span>
                                    <a href="#"><?= htmlspecialchars($product['id']) ?></a>
                                </div>
                                <div class="product-categories">
                                    <span>Categories:</span>
                                    <?php if ($product['category_name']): ?>
                                    <a href="shop.php?category=<?= $product['category_id'] ?>">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </a>
                                    <?php else: ?>&mdash;<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
                <!-- tabs start -->
                <div class="single-tabs">
                    <ul class="tab-list">
                        <li><a href="#" class="tab-button" data-id="desc">Descripton</a></li>
                        <li><a href="#" class="tab-button" data-id="info">Additional information</a></li>
                        <li><a href="#" class="tab-button" data-id="reviews">Reviews</a></li>
                    </ul>
                    <div class="tab-panel">
                        <div class="tab-panel-descriptions content" id="desc">
                            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>
                        <div class="tab-panel-information content" id="info">
                            <h3>Additional information</h3>
                            <table>
                                <tbody>
                                    <tr><th>Color</th><td>Various</td></tr>
                                    <tr><th>Size</th><td>All sizes</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-panel-reviews content" id="reviews">
                            <h3>0 reviews</h3>
                            <p>No reviews yet.</p>
                        </div>
                    </div>
                </div>
                <!-- tabs end -->
            </div>
        </div>
    </section>
    <!-- ! single product end -->

    <!-- ! campaign single start -->
    <section class="campaign-single">
        <div class="container">
            <div class="campaign-wrapper">
                <h2>New Season Sale</h2>
                <strong>40% OFF</strong>
                <span></span>
                <a href="#" class="btn btn-lg">
                    SHOP NOW
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    <!-- ! campaign single end -->

    <!-- ! policy start -->
    <section class="policy">
        <div class="container">
            <ul class="policy-list">
                <li class="policy-item">
                    <i class="bi bi-truck"></i>
                    <div class="policy-texts">
                        <strong>FREE DELIVERY</strong>
                        <span>From $59.89</span>
                    </div>
                </li>
                <li class="policy-item">
                    <i class="bi bi-headset"></i>
                    <div class="policy-texts">
                        <strong>SUPPORT 24/7</strong>
                        <span>Online 24 hours</span>
                    </div>
                </li>
                <li class="policy-item">
                    <i class="bi bi-arrow-clockwise"></i>
                    <div class="policy-texts">
                        <strong>30 DAYS RETURN</strong>
                        <span>Simply return it within 30 days</span>
                    </div>
                </li>
                <li class="policy-item">
                    <i class="bi bi-credit-card"></i>
                    <div class="policy-texts">
                        <strong>PAYMENT METHOD</strong>
                        <span>Secure Payment</span>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <!-- ! policy end -->

    <!-- ! footer start -->
    <section class="footer">
        <div class="subscribe-contact-row">
            <div class="container">
                <div class="subscribe-contact-wrapper">
                    <div class="subscribe-wrapper">
                        <div class="footer-subscribe">
                            <div class="footer-subscribe-top">
                                <h3 class="subscribe-title">
                                    Get our emails for info on new items, sales and more.
                                </h3>
                                <p class="subscribe-desc">
                                    We'll email you a voucher worth $10 off your first order
                                    over $50.
                                </p>
                            </div>
                            <div class="footer-subscribe-bottom">
                                <form>
                                    <input type="text" placeholder="enter your email address" />
                                    <button class="btn">Subscribe</button>
                                </form>
                                <p class="privacy-text">
                                    By subscribing you agree to our
                                    <a href="#">Terms & Conditions and Privacy & Cookies Policy.</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="contact-wrapper">
                        <div class="footer-contact-top">
                            <h3 class="contact-title">Need help? <br>(+90) 123 456 78 90</h3>
                            <p class="contact-desc">We are available 8:00am – 7:00pm</p>
                        </div>
                        <div class="footer-contact-bottom">
                            <div class="download-app">
                                <a href="#"><img src="img/footer/app-store.png" alt=""></a>
                                <a href="#"><img src="img/footer/google-play.png" alt=""></a>
                            </div>
                            <p class="privacy-text">
                                <strong>Shopping App:</strong> Try our View in Your Room feature, manage registries and save payment info.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="widgets-row">
            <div class="container">
                <div class="footer-widgets">
                    <div class="brand-info">
                        <div class="footer-logo"><a href="/" class="logo">LOGO</a></div>
                        <div class="footer-desc">
                            Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel facilisis in termapol.
                        </div>
                        <div class="footer-contact">
                            <p><a href="tel:123456789">(+800) 1234 5678 90</a> - <a href="mailto:info@example.com">info@example.com</a></p>
                        </div>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Information</h4>
                        <ul class="menu-list">
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Returns Policy</a></li>
                            <li><a href="#">Shipping Policy</a></li>
                            <li><a href="#">Dropshipping</a></li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Account</h4>
                        <ul class="menu-list">
                            <li><a href="#">Dashboard</a></li>
                            <li><a href="#">My Orders</a></li>
                            <li><a href="#">My Wishlist</a></li>
                            <li><a href="#">Account details</a></li>
                            <li><a href="#">Track My Orders</a></li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Shop</h4>
                        <ul class="menu-list">
                            <li><a href="#">Affiliate</a></li>
                            <li><a href="#">Bestsellers</a></li>
                            <li><a href="#">Discount</a></li>
                            <li><a href="#">Latest Products</a></li>
                            <li><a href="#">Sale Products</a></li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Categories</h4>
                        <ul class="menu-list">
                            <li><a href="#">Women</a></li>
                            <li><a href="#">Men</a></li>
                            <li><a href="#">Bags</a></li>
                            <li><a href="#">Outerwear</a></li>
                            <li><a href="#">Shoes</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-row">
            <div class="container">
                <div class="footer-copyright">
                    <p>Copyright <?= date('Y') ?> © E-Commerce Theme. All rights reserved. Powered By Sinan Sarıçayır.</p>
                    <a href="#"><img src="img/footer/cards.png" alt=""></a>
                    <div class="footer-menu">
                        <ul class="footer-menu-list">
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms and Conditions</a></li>
                            <li><a href="#">Returns Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ! footer end -->

    <script src="js/main.js" type="module"></script>
    <script src="js/single-product.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"></script>
    <script src="js/glide.js" type="module"></script>
    <script>
    (function(){
      const userId = <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null' ?>;
      const prodId = <?= $id ?>;

      // Wishlist header button
      document.getElementById('wishlist-btn').addEventListener('click', ()=>{
        if (!userId) return alert('Veuillez vous connecter pour vos favoris.');
        fetch('toggle_wishlist.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ id: prodId })
        })
        .then(r=>r.json()).then(data=>{
          document.getElementById('wishlist-icon').className =
            data.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
        });
      });

      // Wishlist in product-extra-buttons
      document.getElementById('wishlist-btn-2').addEventListener('click', e=>{
        e.preventDefault();
        if (!userId) return alert('Veuillez vous connecter pour vos favoris.');
        fetch('toggle_wishlist.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ id: prodId })
        })
        .then(r=>r.json()).then(data=>{
          document.getElementById('wishlist-icon-2').className =
            data.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
        });
      });

      // Add to Cart AJAX
      document.getElementById('add-to-cart').addEventListener('click', ()=>{
        if (!userId) return alert('Veuillez vous connecter pour ajouter au panier.');
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        fetch('add_to_cart.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ id: prodId, qty })
        })
        .then(r=>r.json()).then(data=>{
          if (data.success) {
            document.querySelector('.header-cart-count').textContent = data.cartCount;
            // Optional: show bootstrap toast…
          } else {
            alert(data.error || 'Erreur lors de l\'ajout au panier.');
          }
        });
      });
    })();
    </script>
</body>
</html>
