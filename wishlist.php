<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Vérifier la connexion
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];

// 2) Récupérer les produits likés par l’utilisateur
$stmt = $connexion->prepare("
    SELECT p.id, p.name, p.image, p.price, p.stock, c.name AS category_name
    FROM liked_products lp
    JOIN products p ON lp.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE lp.user_id = ?
    ORDER BY lp.liked_at DESC
");
$stmt->execute([$userId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3) Compter les articles dans le panier actif
$cartCount = 0;
$getCart = $connexion->prepare("
    SELECT id FROM paniers 
    WHERE user_id = ? AND status = 'active'
");
$getCart->execute([$userId]);
$panierId = $getCart->fetchColumn();
if ($panierId) {
    $cnt = $connexion->prepare("
        SELECT COALESCE(SUM(quantity),0) FROM panier_items
        WHERE panier_id = ?
    ");
    $cnt->execute([$panierId]);
    $cartCount = (int)$cnt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ma Wishlist — DermaShop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/main.css">
  <style>
    .card-img-top { object-fit: cover; height: 200px; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="index.php">DermaShop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navMenu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Boutique</a></li>
        <li class="nav-item"><a class="nav-link active" href="wishlist.php">Wishlist</a></li>
      </ul>
    </div>
    <div class="d-flex align-items-center">
      <a href="cart.php" class="position-relative text-dark me-3">
        <i class="bi bi-bag fs-4"></i>
        <?php if ($cartCount > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
            <?= $cartCount ?>
          </span>
        <?php endif; ?>
      </a>
      <a href="#" class="text-dark">
        <i class="bi bi-heart-fill text-danger fs-4"></i>
      </a>
    </div>
  </div>
</nav>

<!-- WISHLIST CONTENT -->
<main class="container py-5">
  <h1 class="mb-4">Ma liste de souhaits</h1>

  <?php if ($products): ?>
    <div class="row g-4">
      <?php foreach ($products as $prod): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($prod['image']) ?>"
                 class="card-img-top" alt="<?= htmlspecialchars($prod['name']) ?>">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($prod['name']) ?></h5>
              <p class="text-danger fs-5">$<?= number_format($prod['price'],2) ?></p>
              <?php if ($prod['stock'] > 0): ?>
                <span class="badge bg-success mb-2">En stock</span>
              <?php else: ?>
                <span class="badge bg-danger mb-2">Rupture</span>
              <?php endif; ?>
              <div class="mt-auto d-flex justify-content-between">
                <button class="btn btn-outline-danger btn-sm remove-wishlist"
                        data-id="<?= $prod['id'] ?>">
                  <i class="bi bi-heart-break"></i>
                </button>
                <a href="product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm">
                  Voir
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="fs-5 text-muted">Vous n'avez aucun produit dans votre wishlist.</p>
  <?php endif; ?>
</main>

<!-- FOOTER -->
<footer class="bg-light py-4 mt-5">
  <div class="container text-center text-muted">
    &copy; <?= date('Y') ?> DermaShop. Tous droits réservés.
  </div>
</footer>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.remove-wishlist').forEach(btn => {
    btn.addEventListener('click', () => {
      const prodId = btn.dataset.id;
      fetch('toggle_wishlist.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id: prodId })
      })
      .then(r => r.json())
      .then(data => {
        if (!data.liked) {
          btn.closest('.col-6').remove();
        }
      })
      .catch(console.error);
    });
  });
</script>
</body>
</html>
