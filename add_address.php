<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data and save to database
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $region = $_POST['region'] ?? '';
    $zipcode = $_POST['zipcode'] ?? '';
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Save to database here
    
    // Redirect back to addresses page
    header('Location: addresses.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Address - Purrfect Paws</title>

    <!-- Site styles to match existing layout/colors -->
    <link rel="stylesheet" href="html/css/kumi.css">
    <link rel="stylesheet" href="html/css/profile.css">
    <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Light page-specific layout only (uses site palette) -->
    <style>
      body { background: #f5f5f5; }
      .form-card { background:#fff;border-radius:8px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,.06);max-width:860px;margin:30px auto }
      .page-title { font-size:22px;margin:0 0 16px 0;color:#333 }
      .form-row { display:grid;grid-template-columns:1fr 1fr;gap:16px }
      .form-group { margin-bottom:14px }
      .checkbox-group { display:flex;align-items:center;gap:10px;margin-top:6px }
      .form-actions { display:flex;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #eef2f5 }
      @media (max-width:640px){ .form-row{ grid-template-columns:1fr } }
    </style>
</head>
<body>

  <!-- Navigation Bar (same as site pages, paths adjusted) -->
  <nav class="navbar">
    <div class="navdiv">
      <div class="logo"><a href="html/index.php">Purrfect Paws</a></div>
      <ul>
        <li><a href="html/index.php">Home</a></li>
        <li><a href="html/product.php">Shop</a></li>
        <li><a href="#gallery">Gallery</a></li>
        <li><a href="#contact">Contact Us</a></li>
      </ul>

      <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php
            $profileImage = isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])
              ? "html/uploads/" . htmlspecialchars($_SESSION['profile_image'])
              : "html/uploads/default.png";
          ?>
          <div class="user-menu">
            <img src="<?= $profileImage ?>" alt="User" class="user-icon">
            <span class="username"><?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
            <div class="dropdown">
              <a href="profile_php/profile.php">My Account</a>
              <a href="/my_purchases.php">My Purchase</a>
              <a href="login_register/logout.php">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
        <?php endif; ?>

        <a href="html/cart.php" class="cart-icon" style="position:relative;">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge" style="display:none;">0</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Page content -->
  <div class="form-card">
    <h1 class="page-title">Add New Address</h1>
    <form method="POST" action="">
      <div class="form-row">
        <div class="form-group">
          <label class="required">Full Name</label>
          <input type="text" name="name" required>
        </div>
        <div class="form-group">
          <label class="required">Phone Number</label>
          <input type="tel" name="phone" required placeholder="(+63) 912 345 6789">
        </div>
      </div>

      <div class="form-group">
        <label>Label (Home, Office, etc.)</label>
        <input type="text" name="label" placeholder="Home">
      </div>

      <div class="form-group">
        <label class="required">Street Address</label>
        <input type="text" name="street" required placeholder="House No., Building, Street Name">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required">Barangay</label>
          <input type="text" name="barangay" required>
        </div>
        <div class="form-group">
          <label class="required">City</label>
          <input type="text" name="city" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required">Province/Region</label>
          <input type="text" name="region" required>
        </div>
        <div class="form-group">
          <label class="required">Zip Code</label>
          <input type="text" name="zipcode" required>
        </div>
      </div>

      <div class="checkbox-group">
        <input type="checkbox" name="is_default" id="is_default">
        <label for="is_default" style="cursor:pointer;font-size:14px;">Set as default address</label>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">Save Address</button>
        <button type="button" class="btn-secondary" onclick="window.location.href='addresses.php'">Cancel</button>
      </div>
    </form>
  </div>

  <!-- Footer (same as site pages, paths adjusted) -->
  <footer class="footer">
    <div class="footer-logo">
      <img src="html/images/da458a49866cd4f697e076e5d2e2099f-removebg-preview.png" alt="cat">
    </div>
    <div class="footer-container">
      <div class="footer-col">
        <h4>Information</h4>
        <ul>
          <li><a href="#">About</a></li>
          <li><a href="#">FAQ</a></li>
          <section class="contact" id="contact">
            <li><a href="#">Contact Us</a></li>
          </section>
          <li><a href="#">Blogs</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Shopping</h4>
        <ul>
          <li><a href="#">Products</a></li>
          <li><a href="#">Terms of Sale</a></li>
          <li><a href="#">Trade Enquiries</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <p>431 Captain E. Jabulin St<br>
          ppaws1027@gmail.com<br>
          0961 9400 663<br>
        </p>
      </div>
      <div class="footer-col">
        <h4>Follow Us</h4>
        <div class="social-icons">
          <a href="https://www.instagram.com/ppaws1027/"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-facebook"></i></a>
          <a href="https://x.com/PurrfectPaw1027"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <script src="js/cart.js"></script>
  <script src="js/product-details.js"></script>
</body>
</html>
