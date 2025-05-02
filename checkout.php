<?php
session_start();
include 'includes/config.php';
include 'includes/cart_functions.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['error'] = 'Please log in to complete checkout.';
    header('Location: account.php');
    exit;
}

// Check if cart is empty
$cart = getCart();
if (count($cart['items']) == 0) {
    $_SESSION['error'] = 'Your cart is empty.';
    header('Location: cart.php');
    exit;
}

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $shipping_address = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
    
    if (empty($shipping_address)) {
        $error = 'Shipping address is required.';
    } else {
        try {
            // Begin transaction
            $connexion->beginTransaction();
            
            // Create order
            $stmt = $connexion->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $cart['total'],
                $shipping_address
            ]);
            
            $order_id = $connexion->lastInsertId();
            
            // Add order items
            $stmt = $connexion->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart['items'] as $item) {
                $stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update product stock
                $update_stock = $connexion->prepare("
                    UPDATE products
                    SET stock = stock - ?
                    WHERE id = ?
                ");
                $update_stock->execute([$item['quantity'], $item['id']]);
            }
            
            // Commit transaction
            $connexion->commit();
            
            // Clear cart
            clearCart();
            
            // Set success message
            $_SESSION['success'] = 'Your order has been placed successfully! Order #' . $order_id;
            
            // Redirect to order confirmation
            header('Location: order_confirmation.php?order_id=' . $order_id);
            exit;
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $connexion->rollBack();
            $error = 'Error processing your order: ' . $e->getMessage();
        }
    }
}

// Get user information
try {
    $stmt = $connexion->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error fetching user data: ' . $e->getMessage();
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
    <title>DermaShop | Checkout</title>
</head>

<body>
    <!-- header start -->
    <header>
        <div class="global-notification">
            <div class="container">
                <p>
                    SPECIAL SALE FOR SKINCARE PRODUCTS - UP TO 30% OFF! <a href="shop.php">SHOP NOW</a>
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
                                    <a href="index.php" class="menu-link">Home</a>
                                </li>
                                <li class="menu-list-item">
                                    <a href="shop.php" class="menu-link">Shop</a>
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
                            <a href="account.php">
                                <i class="bi bi-person"></i>
                            </a>
                            <button class="search-button">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="cart.php" class="header-cart-link">
                                <i class="bi bi-bag"></i>
                                <span class="header-cart-count"><?php echo $cart['item_count']; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header end -->

    <!-- checkout start -->
    <section class="checkout-page">
        <div class="container">
            <div class="checkout-form-wrapper">
                <h2>Checkout</h2>
                <?php if (isset($error)): ?>
                    <div class="alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="checkout.php" method="POST">
                    <div class="form-section">
                        <h3>Order Summary</h3>
                        <div class="order-summary">
                            <div class="cart-summary">
                                <table class="summary-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart['items'] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2">Total:</th>
                                            <td>$<?php echo $cart['total']; ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Shipping Information</h3>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address*</label>
                            <textarea id="shipping_address" name="shipping_address" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                <label for="credit_card">Credit Card</label>
                            </div>
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">PayPal</label>
                            </div>
                        </div>
                        
                        <div class="credit-card-fields">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="**** **** **** ****">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="***">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-actions">
                        <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        <button type="submit" class="btn btn-red btn-lg">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- checkout end -->

    <!-- policy start -->
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
    <!-- policy end -->

    <!-- footer start -->
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
                                    <input type="text" placeholder="Enter your email address" />
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
                            <h3 class="contact-title">Need help? <br>
                                (+90) 123 456 78 90
                            </h3>
                            <p class="contact-desc">We are available 8:00am – 7:00pm
                            </p>
                        </div>
                        <div class="footer-contact-bottom">
                            <div class="download-app">
                                <a href="#">
                                    <img src="img/footer/app-store.png" alt="">
                                </a>
                                <a href="#">
                                    <img src="img/footer/google-play.png" alt="">
                                </a>
                            </div>
                            <p class="privacy-text">
                                <strong>Shopping App:</strong> Try our View in Your Room feature, manage registries and
                                save payment info.
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
                        <div class="footer-logo">
                            <a href="index.php" class="logo">DermaShop</a>
                        </div>
                        <div class="footer-desc">
                            Your trusted source for premium skincare products. Discover the best solutions for your skin's needs.
                        </div>
                        <div class="footer-contact">
                            <p>
                                <a href="tel:123456789">(+800) 1234 5678 90</a> -
                                <a href="mailto:info@dermashop.com">info@dermashop.com</a>
                            </p>
                        </div>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Information</h4>
                        <ul class="menu-list">
                            <li>
                                <a href="#">About Us</a>
                            </li>
                            <li>
                                <a href="#">Privacy Policy</a>
                            </li>
                            <li>
                                <a href="#">Returns Policy</a>
                            </li>
                            <li>
                                <a href="#">Shipping Policy</a>
                            </li>
                            <li>
                                <a href="#">Dropshipping</a>
                            </li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Account</h4>
                        <ul class="menu-list">
                            <li>
                                <a href="account.php">Dashboard</a>
                            </li>
                            <li>
                                <a href="#">My Orders</a>
                            </li>
                            <li>
                                <a href="#">My Wishlist</a>
                            </li>
                            <li>
                                <a href="#">Account details</a>
                            </li>
                            <li>
                                <a href="#">Track My Orders</a>
                            </li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Shop</h4>
                        <ul class="menu-list">
                            <li>
                                <a href="#">Affiliate</a>
                            </li>
                            <li>
                                <a href="#">Bestsellers</a>
                            </li>
                            <li>
                                <a href="#">Discount</a>
                            </li>
                            <li>
                                <a href="#">Latest Products</a>
                            </li>
                            <li>
                                <a href="#">Sale Products</a>
                            </li>
                        </ul>
                    </div>
                    <div class="widget-nav-menu">
                        <h4>Categories</h4>
                        <ul class="menu-list">
                            <li>
                                <a href="#">Facial Cleansers</a>
                            </li>
                            <li>
                                <a href="#">Moisturizers</a>
                            </li>
                            <li>
                                <a href="#">Serums</a>
                            </li>
                            <li>
                                <a href="#">Sunscreens</a>
                            </li>
                            <li>
                                <a href="#">Acne Treatments</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright-row">
            <div class="container">
                <div class="footer-copyright">
                    <div class="site-copyright">
                        <p>
                            Copyright 2023 © DermaShop. All rights reserved.
                        </p>
                    </div>
                    <a href="#">
                        <img src="img/footer/cards.png" alt="">
                    </a>
                    <div class="footer-menu">
                        <ul class="footer-menu-list">
                            <li class="list-item">
                                <a href="#">Privacy Policy</a>
                            </li>
                            <li class="list-item">
                                <a href="#">Terms and Conditions</a>
                            </li>
                            <li class="list-item">
                                <a href="#">Returns Policy</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- footer end -->

    <!-- scripts start -->
    <script src="js/main.js" type="module"></script>
    <script>
        // Toggle payment method fields
        document.addEventListener('DOMContentLoaded', function() {
            const creditCardRadio = document.getElementById('credit_card');
            const paypalRadio = document.getElementById('paypal');
            const creditCardFields = document.querySelector('.credit-card-fields');
            
            creditCardRadio.addEventListener('change', function() {
                if (this.checked) {
                    creditCardFields.style.display = 'block';
                }
            });
            
            paypalRadio.addEventListener('change', function() {
                if (this.checked) {
                    creditCardFields.style.display = 'none';
                }
            });
        });
    </script>
    <!-- scripts end -->
</body>

</html> 