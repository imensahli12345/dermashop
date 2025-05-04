<?php
// partials/header.php

// 1) Bootstrapping
require_once __DIR__ . '/../includes/config.php'; // adjust path if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Compute dynamic cart count
$cartCount = 0;
if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Find (or create) active panier
    $stmt = $connexion->prepare("
        SELECT id 
        FROM paniers 
        WHERE user_id = :uid 
          AND status = 'active'
        LIMIT 1
    ");
    $stmt->execute([':uid' => $user_id]);
    $panier = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($panier) {
        $stmt = $connexion->prepare("
            SELECT SUM(quantity) AS cnt
            FROM panier_items
            WHERE panier_id = :pid
        ");
        $stmt->execute([':pid' => $panier['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $cartCount = $row['cnt'] ? (int)$row['cnt'] : 0;
    }
}
?>
<!-- ! HEADER START -->
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
              <li class="menu-list-item"><a href="index.php" class="menu-link active">Home</a></li>
              <li class="menu-list-item"><a href="shop.php" class="menu-link">Shop</a></li>
              <li class="menu-list-item"><a href="blog.html" class="menu-link">Blog</a></li>
              <li class="menu-list-item"><a href="contact.html" class="menu-link">Contact</a></li>
              <li class="menu-list-item"><a href="account.php" class="menu-link">Account</a></li>
              <?php if (!empty($_SESSION['logged_in'])): ?>
                <li class="menu-list-item"><a href="includes/logout.php" class="menu-link">Logout</a></li>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <li class="menu-list-item"><a href="admin/products.php" class="menu-link">Admin</a></li>
                <?php endif; ?>
              <?php endif; ?>
            </ul>
          </nav>
          <i class="bi-x-circle" id="close-sidebar"></i>
        </div>
        <div class="header-right">
          <div class="header-right-links">
            <button class="search-button"><i class="bi bi-search"></i></button>
            <a href="wishlist.php"><i class="bi bi-heart"></i></a>
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
<!-- ! HEADER END -->
