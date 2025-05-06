<?php
require 'includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Ensure user is logged in
if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// 2) Handle updates/removals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['update_cart']) && !empty($_POST['quantities'])) {
      foreach ($_POST['quantities'] as $item_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $connexion->prepare("UPDATE panier_items SET quantity = :qty WHERE id = :id");
        $stmt->execute([':qty'=>$qty, ':id'=>$item_id]);
      }
    }
    if (!empty($_POST['remove_item'])) {
      $stmt = $connexion->prepare("DELETE FROM panier_items WHERE id = :id");
      $stmt->execute([':id'=>(int)$_POST['remove_item']]);
    }
    header('Location: cart.php');
    exit;
}

// 3) Get or create the active panier
$stmt = $connexion->prepare("
  SELECT id FROM paniers 
   WHERE user_id = :uid AND status = 'active' LIMIT 1
");
$stmt->execute([':uid'=>$user_id]);
$panier = $stmt->fetch(PDO::FETCH_ASSOC);
if ($panier) {
  $panier_id = $panier['id'];
} else {
  $stmt = $connexion->prepare("INSERT INTO paniers (user_id) VALUES (:uid)");
  $stmt->execute([':uid'=>$user_id]);
  $panier_id = $connexion->lastInsertId();
}

// 4) Fetch items
$stmt = $connexion->prepare("
  SELECT pi.id AS item_id, pi.quantity,
         p.id AS product_id, p.name, p.price, p.image, p.stock
    FROM panier_items pi
    JOIN products p ON pi.product_id = p.id
   WHERE pi.panier_id = :pid
");
$stmt->execute([':pid'=>$panier_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) Subtotal & totals
$subtotal = array_reduce($items, fn($sum,$i)=>$sum + $i['price']*$i['quantity'], 0.0);
$shipping_methods = ['standard'=>0.00, 'fast'=>15.00];
$selectedShipping = $_POST['shipping_method'] ?? 'standard';
$shipping_cost     = $shipping_methods[$selectedShipping] ?? 0.00;
$total             = $subtotal + $shipping_cost;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css" />
  <link rel="stylesheet" href="css/main.css" />
  <title>DermaShop | Your Cart</title>

  <style>
  /* === Cart Page Styles === */
  .cart-page {
    padding: 4rem 0;
    background: #f4f5f7;
    color: #333;
  }
  .cart-page .container { max-width: 1200px; margin: 0 auto; }
  .cart-page h2 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    font-weight: 700;
    text-transform: uppercase;
  }
  .empty-cart {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  .empty-cart a {
    display: inline-block;
    margin-top: 1rem;
    padding: .6rem 1.2rem;
    background: #007bff;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
  }

  .shop-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
  }
  .shop-table thead { background: #e9ecef; }
  .shop-table th,
  .shop-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
  }
  .shop-table tbody tr:nth-child(odd) { background: #f9f9f9; }
  .shop-table img {
    max-width: 80px;
    border-radius: 4px;
    object-fit: cover;
  }
  .quantity-input {
    width: 60px;
    padding: .4rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-align: center;
  }

  .btn {
    display: inline-block;
    padding: .6rem 1.2rem;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
  }
  .btn-primary {
    background: #007bff;
    color: #fff;
  }
  .btn-primary:hover {
    background: #0069d9;
  }
  .btn-danger {
    background: #dc3545;
    color: #fff;
  }
  .btn-danger:hover {
    background: #c82333;
  }

  .cart-actions {
    margin-bottom: 2rem;
  }

  .cart-summary-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 1.5rem;
  }
  .cart-totals {
    background: #fff;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex: 0 0 360px;
  }
  .cart-totals h3 {
    margin-bottom: 1rem;
    font-size: 1.25rem;
    text-transform: uppercase;
  }
  .cart-totals table {
    width: 100%;
    margin-bottom: 1rem;
  }
  .cart-totals th {
    text-align: left;
    padding-bottom: .5rem;
  }
  .cart-totals td {
    padding-bottom: .5rem;
  }
  .cart-totals table tr:last-child th,
  .cart-totals table tr:last-child td {
    font-size: 1.1rem;
    font-weight: 700;
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
  }
  .proceed-btn {
    display: block;
    text-align: center;
    margin-top: 1rem;
  }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="cart-page">
    <div class="container">
      <h2>Your Shopping Cart</h2>

      <?php if (empty($items)): ?>
        <div class="empty-cart">
          <p>Your cart is currently empty.</p>
          <a href="shop.php">Continue Shopping</a>
        </div>
      <?php else: ?>
        <form action="cart.php" method="post">
          <div class="table-responsive">
            <table class="shop-table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($items as $it): ?>
  <tr>
    <td>
      <img
        src="get_image.php?id=<?= $it['product_id'] ?>"
        class="product-image"
        alt="<?= htmlspecialchars($it['name'], ENT_QUOTES) ?>"
        width="80" height="80"
      />
    </td>
    <td><?= htmlspecialchars($it['name'], ENT_QUOTES) ?></td>
    <td>$<?= number_format($it['price'],2) ?></td>
    <td>
      <input
        class="quantity-input"
        type="number"
        name="quantities[<?= $it['item_id'] ?>]"
        value="<?= $it['quantity'] ?>"
        min="1"
        max="<?= $it['stock'] ?>"
      >
    </td>
    <td>$<?= number_format($it['price'] * $it['quantity'],2) ?></td>
    <td>
      <button
        type="submit"
        name="remove_item"
        value="<?= $it['item_id'] ?>"
        class="btn btn-danger"
      >&times;</button>
    </td>
  </tr>
<?php endforeach; ?>

              </tbody>
            </table>
          </div>

          <div class="cart-actions">
            <button type="submit" name="update_cart" class="btn btn-primary">
              Update Cart
            </button>
          </div>

          <div class="cart-summary-wrapper">
            <div class="cart-totals">
              <h3>Cart Totals</h3>
              <table>
                <tr>
                  <th>Subtotal</th>
                  <td>$<?= number_format($subtotal,2) ?></td>
                </tr>
                <tr>
                  <th>Shipping</th>
                  <td>
                    <label>
                      <input type="radio"
                             name="shipping_method"
                             value="standard"
                             <?= $selectedShipping==='standard'?'checked':'' ?>>
                      Free Standard
                    </label><br>
                    <label>
                      <input type="radio"
                             name="shipping_method"
                             value="fast"
                             <?= $selectedShipping==='fast'?'checked':'' ?>>
                      Fast Cargo ($15.00)
                    </label>
                  </td>
                </tr>
                <tr>
                  <th>Total</th>
                  <td>$<?= number_format($total,2) ?></td>
                </tr>
              </table>
              <a href="checkout.php" class="btn btn-primary proceed-btn">
                Proceed to Checkout
              </a>
            </div>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
