<?php
// thank_you.php
require 'includes/config.php';  // your PDO $connexion
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Ensure user is logged in
if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// 2) Validate and fetch order
if (empty($_GET['order_id']) || !ctype_digit($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}
$order_id = (int)$_GET['order_id'];

$stmt = $connexion->prepare("
    SELECT total_amount, shipping_address, order_date
    FROM orders
    WHERE id = :oid
      AND user_id = :uid
    LIMIT 1
");
$stmt->execute([
    ':oid' => $order_id,
    ':uid' => $user_id
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    // Order not found or doesn't belong to this user
    header('Location: index.php');
    exit;
}

// 3) Fetch order items
$stmt = $connexion->prepare("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :oid
");
$stmt->execute([':oid' => $order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DermaShop | Thank You</title>
  <link rel="stylesheet" href="css/main.css" />
  <style>
    /* === Thank You Page Styles === */
    .thank-you-page { padding: 4rem 0; }
    .thank-you-page .container { max-width: 700px; margin: 0 auto; text-align: center; }
    .thank-you-page h2 { font-size: 2rem; margin-bottom: 1rem; }
    .thank-you-page p { margin-bottom: 1.5rem; }
    .shop-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2rem;
    }
    .shop-table th, .shop-table td {
      padding: .75rem;
      border: 1px solid #dee2e6;
    }
    .shop-table thead { background-color: #f8f9fa; }
    .shop-table tfoot th { text-align: right; }
    .btn-primary {
      display: inline-block;
      padding: .6rem 1.2rem;
      background-color: #007bff;
      border: none;
      border-radius: .25rem;
      color: #fff;
      text-decoration: none;
      font-size: 1rem;
      cursor: pointer;
    }
    .btn-primary:hover {
      background-color: #0069d9;
    }
    .shipping-address {
      text-align: left;
      border: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: .25rem;
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <section class="thank-you-page">
    <div class="container">
      <h2>Thank You for Your Order!</h2>
      <p>
        Your order <strong>#<?= htmlspecialchars($order_id) ?></strong>
        was placed on
        <strong><?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?></strong>.
      </p>

      <h3>Order Details</h3>
      <table class="shop-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td><?= (int)$it['quantity'] ?></td>
            <td>$<?= number_format($it['price'], 2) ?></td>
            <td>$<?= number_format($it['price'] * $it['quantity'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3">Total</th>
            <th>$<?= number_format($order['total_amount'], 2) ?></th>
          </tr>
        </tfoot>
      </table>

      <h3>Shipping Address</h3>
      <div class="shipping-address">
        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
      </div>

      <a href="index.php" class="btn-primary">Continue Shopping</a>
    </div>
  </section>

  <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
