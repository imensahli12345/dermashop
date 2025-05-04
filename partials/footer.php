<?php
// partials/footer.php
?>
<!-- ! FOOTER START -->
<section class="footer">
  <div class="subscribe-contact-row">
    <div class="container">
      <div class="subscribe-contact-wrapper">
        <div class="footer-subscribe">
          <h3>Get our emails for info on new items, sales and more.</h3>
          <p>We'll email you a voucher worth $10 off your first order over $50.</p>
          <form>
            <input type="text" placeholder="enter your email address" />
            <button class="btn">Subscribe</button>
          </form>
          <p class="privacy-text">
            By subscribing you agree to our 
            <a href="#">Terms & Conditions</a> and 
            <a href="#">Privacy & Cookies Policy</a>.
          </p>
        </div>
        <div class="footer-contact">
          <h3>Need help?<br>(+90) 123 456 78 90</h3>
          <p>We are available 8:00am – 7:00pm</p>
          <div class="download-app">
            <a href="#"><img src="img/footer/app-store.png" alt="App Store"></a>
            <a href="#"><img src="img/footer/google-play.png" alt="Google Play"></a>
          </div>
          <p>
            <strong>Shopping App:</strong> Try our View in Your Room feature,
            manage registries and save payment info.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="widgets-row">
    <div class="container">
      <div class="footer-widgets">
        <div class="brand-info">
          <a href="index.php" class="logo">DermaShop</a>
          <p>
            Premium dermatological products for healthy skin.
            Scientifically formulated skincare solutions for every skin type.
          </p>
          <p>
            <a href="tel:123456789">(+800) 1234 5678 90</a> -
            <a href="mailto:info@dermashop.com">info@dermashop.com</a>
          </p>
        </div>

        <div class="widget-nav-menu">
          <h4>Information</h4>
          <ul>
            <li><a href="#">About Us</a></li>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Returns Policy</a></li>
            <li><a href="#">Shipping Policy</a></li>
            <li><a href="#">Dropshipping</a></li>
          </ul>
        </div>

        <div class="widget-nav-menu">
          <h4>Account</h4>
          <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">My Orders</a></li>
            <li><a href="#">My Wishlist</a></li>
            <li><a href="#">Account Details</a></li>
            <li><a href="#">Track My Orders</a></li>
          </ul>
        </div>

        <div class="widget-nav-menu">
          <h4>Shop</h4>
          <ul>
            <li><a href="#">Affiliate</a></li>
            <li><a href="#">Bestsellers</a></li>
            <li><a href="#">Discount</a></li>
            <li><a href="#">Latest Products</a></li>
            <li><a href="#">Sale Products</a></li>
          </ul>
        </div>

        <div class="widget-nav-menu">
          <h4>Categories</h4>
          <ul>
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
      <p>&copy; <?= date('Y') ?> DermaShop. All rights reserved.</p>
      <a href="#"><img src="img/footer/cards.png" alt="Payment Methods"></a>
      <ul class="footer-menu-list">
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms and Conditions</a></li>
        <li><a href="#">Returns Policy</a></li>
      </ul>
    </div>
  </div>
</section>
<!-- ! FOOTER END -->
