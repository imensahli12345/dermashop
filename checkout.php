<?php
// checkout.php
require 'includes/config.php';  // your PDO $connexion
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1) Redirect guests
if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// 2) Fetch (or create) active panier
$stmt = $connexion->prepare("
    SELECT id FROM paniers
    WHERE user_id = :uid AND status = 'active'
    LIMIT 1
");
$stmt->execute([':uid' => $user_id]);
$panier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$panier) {
    // No items → send back to shop
    header('Location: shop.php');
    exit;
}
$panier_id = $panier['id'];

// 3) Load panier items
$stmt = $connexion->prepare("
    SELECT pi.quantity, p.id AS product_id, p.name, p.price
    FROM panier_items pi
    JOIN products p ON pi.product_id = p.id
    WHERE pi.panier_id = :pid
");
$stmt->execute([':pid' => $panier_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($items) === 0) {
    header('Location: cart.php');
    exit;
}

// 4) Compute subtotal
$subtotal = 0;
foreach ($items as $it) {
    $subtotal += $it['price'] * $it['quantity'];
}

// 5) Load user address + existing credit-card (if any)
$stmt = $connexion->prepare("SELECT address FROM users WHERE id = :uid");
$stmt->execute([':uid' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$address = $user['address'] ?? '';

$stmt = $connexion->prepare("
    SELECT credit_card_number, credit_card_expiry, credit_card_cvv
    FROM client_profiles
    WHERE user_id = :uid
");
$stmt->execute([':uid' => $user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// 6) Handle submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Shipping
    $shipping_method = $_POST['shipping_method'] ?? 'standard';
    $shipping_cost = $shipping_method === 'fast' ? 15.00 : 0.00;

    // Address
    $ship_addr = trim($_POST['shipping_address'] ?? '');
    if ($ship_addr === '') {
        $errors[] = "Shipping address is required.";
    }

    // Credit card fields
    $cc_num    = preg_replace('/\D/', '', $_POST['cc_number'] ?? '');
    $cc_exp    = $_POST['cc_expiry'] ?? '';
    $cc_cvv    = preg_replace('/\D/', '', $_POST['cc_cvv'] ?? '');

    if (strlen($cc_num) < 13 || strlen($cc_num) > 19) {
        $errors[] = "Enter a valid credit card number.";
    }
    if (!preg_match('/^\d{2}-\d{2}$/', $cc_exp)) {
        $errors[] = "Expiry must be MM-YY.";
    }
    if (strlen($cc_cvv) < 3 || strlen($cc_cvv) > 4) {
        $errors[] = "Enter a valid CVV.";
    }

    if (empty($errors)) {
        $total_amount = $subtotal + $shipping_cost;

        try {
            $connexion->beginTransaction();

            // a) Upsert client_profiles
            if ($profile) {
                $upd = $connexion->prepare("
                    UPDATE client_profiles
                    SET credit_card_number = :num,
                        credit_card_expiry = STR_TO_DATE(:exp, '%m-%y'),
                        credit_card_cvv    = :cvv
                    WHERE user_id = :uid
                ");
                $upd->execute([
                    ':num' => $cc_num,
                    ':exp' => $cc_exp,
                    ':cvv' => $cc_cvv,
                    ':uid' => $user_id
                ]);
            } else {
                $ins = $connexion->prepare("
                    INSERT INTO client_profiles
                    (user_id, credit_card_number, credit_card_expiry, credit_card_cvv)
                    VALUES
                    (:uid, :num, STR_TO_DATE(:exp, '%m-%y'), :cvv)
                ");
                $ins->execute([
                    ':uid' => $user_id,
                    ':num' => $cc_num,
                    ':exp' => $cc_exp,
                    ':cvv' => $cc_cvv
                ]);
            }

            // b) Insert order
            $ord = $connexion->prepare("
                INSERT INTO orders
                (user_id, total_amount, shipping_address)
                VALUES
                (:uid, :total, :addr)
            ");
            $ord->execute([
                ':uid'   => $user_id,
                ':total' => $total_amount,
                ':addr'  => $ship_addr
            ]);
            $order_id = $connexion->lastInsertId();

            // c) Insert order_items
            $oi = $connexion->prepare("
                INSERT INTO order_items
                (order_id, product_id, quantity, price)
                VALUES
                (:oid, :pid, :qty, :price)
            ");
            foreach ($items as $it) {
                $oi->execute([
                    ':oid'   => $order_id,
                    ':pid'   => $it['product_id'],
                    ':qty'   => $it['quantity'],
                    ':price' => $it['price']
                ]);
            }

            // d) Mark panier checked_out
            $updPan = $connexion->prepare("
                UPDATE paniers
                SET status = 'checked_out'
                WHERE id = :pid
            ");
            $updPan->execute([':pid' => $panier_id]);

            $connexion->commit();

            // Redirect to a thank-you page
            header("Location: thank_you.php?order_id={$order_id}");
            exit;

        } catch (Exception $e) {
            $connexion->rollBack();
            $errors[] = "Order failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DermaShop | Checkout</title>
  <link rel="stylesheet" href="css/main.css" />
  <style>
    /* === Checkout Page Styles === */
    .checkout-page { padding: 4rem 0; }
    .checkout-page .container { max-width: 800px; margin: 0 auto; }
    .checkout-page h2 { font-size: 2rem; margin-bottom: 1rem; }
    .checkout-page h3 { font-size: 1.5rem; margin-top: 2rem; margin-bottom: .75rem; }
    .alert.alert-danger {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      padding: 1rem;
      border-radius: .25rem;
      margin-bottom: 1.5rem;
    }
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
    .form-group { margin-bottom: 1.5rem; }
    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: .5rem;
    }
    .form-group input[type="text"],
    .form-group input[type="password"],
    textarea {
      width: 100%;
      padding: .5rem;
      border: 1px solid #ced4da;
      border-radius: .25rem;
    }
    textarea { resize: vertical; }
    .btn-lg.btn-success {
      display: inline-block;
      padding: .75rem 1.5rem;
      background-color: #28a745;
      border: none;
      border-radius: .25rem;
      color: #fff;
      text-decoration: none;
      font-size: 1rem;
      cursor: pointer;
    }
    .btn-lg.btn-success:hover {
      background-color: #218838;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <section class="checkout-page">
    <div class="container">
      <h2>Checkout</h2>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" novalidate>
        <h3>Order Summary</h3>
        <table class="shop-table">
          <thead>
            <tr>
              <th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
              <td><?= htmlspecialchars($it['name']) ?></td>
              <td><?= $it['quantity'] ?></td>
              <td>$<?= number_format($it['price'],2) ?></td>
              <td>$<?= number_format($it['price']*$it['quantity'],2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3">Subtotal</th>
              <td>$<?= number_format($subtotal,2) ?></td>
            </tr>
          </tfoot>
        </table>

        <h3>Shipping Method</h3>
        <?php $sel = $_POST['shipping_method'] ?? 'standard'; ?>
        <label>
          <input type="radio" name="shipping_method" value="standard"
            <?= $sel==='standard'?'checked':'' ?>> Free Standard
        </label><br>
        <label>
          <input type="radio" name="shipping_method" value="fast"
            <?= $sel==='fast'?'checked':'' ?>> Fast Cargo ($15.00)
        </label>

        <h3>Shipping Address</h3>
        <textarea name="shipping_address" rows="3" required><?= htmlspecialchars($_POST['shipping_address'] ?? $address) ?></textarea>

        <h3>Payment Information</h3>
        <div class="form-group">
          <label>Card Number</label>
          <input
            type="text"
            name="cc_number"
            maxlength="19"
            placeholder="1234 5678 9012 3456"
            value="<?= htmlspecialchars($_POST['cc_number'] ?? ($profile['credit_card_number'] ?? '')) ?>"
            required
          >
        </div>
        <div class="form-group">
          <label>Expiry (MM-YY)</label>
          <input
            type="text"
            name="cc_expiry"
            maxlength="5"
            placeholder="MM-YY"
            value="<?= htmlspecialchars($_POST['cc_expiry'] ?? (isset($profile['credit_card_expiry']) ? date('m-y', strtotime($profile['credit_card_expiry'])) : '')) ?>"
            required
          >
        </div>
        <div class="form-group">
          <label>CVV</label>
          <input
            type="password"
            name="cc_cvv"
            maxlength="4"
            placeholder="123"
            value="<?= htmlspecialchars($_POST['cc_cvv'] ?? ($profile['credit_card_cvv'] ?? '')) ?>"
            required
          >
        </div>

        <button type="submit" class="btn btn-lg btn-success">Place Order</button>
      </form>
    </div>
  </section>


</body>
</html>
