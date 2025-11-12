<?php
session_start();
require_once '../HTML/config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/purdex.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// ✅ Fix image path - use session if available, otherwise use database
$profileImage = (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image']))
    ? '../HTML/uploads/' . htmlspecialchars($_SESSION['profile_image'])
    : (!empty($user['profile_image'])
        ? '../HTML/uploads/' . htmlspecialchars($user['profile_image'])
        : '../HTML/uploads/default.png');
        
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="Purrfect Paws, cats, Cagayan de Oro City">
  <meta name="description" content="Purrfect Paws - Your one-stop shop for all things cat-related in Cagayan de Oro City.">
  <title>Purrfect Paws | Profile</title>
  <link rel="stylesheet" href="../HTML/css/kumi.css">
  <link rel="stylesheet" href="../HTML/css/profile.css?v=to-pay-cancel">
  <link rel="stylesheet" href="../HTML/css/address-modal.css">
  <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <script>window.PURR_USER_ID = <?= json_encode((string)$user_id) ?>;</script>

</head>

<body>


<!-- 🐾 Navigation Bar -->
<nav class="navbar">
  <div class="navdiv">
      <div class="logo"><a href="../HTML/index.php">Purrfect Paws</a></div>
      <ul>
          <li><a href="../HTML/index.php">Home</a></li>
          <li><a href="../HTML/product.php">Shop</a></li>
          <li><a href="#gallery">Gallery</a></li>
          <li><a href="#contact">Contact Us</a></li>
      </ul>

      <div class="nav-right">
          <!-- 🧑‍💼 User dropdown OR Login -->
          <?php if (isset($_SESSION['user_id'])): ?>
              <div class="user-menu">
                  <img src="<?= $profileImage ?>" alt="User" class="user-icon">
                  <span class="username"><?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                  <div class="dropdown">
                      <a href="profile.php">My Account</a>
                      <a href="profile.php#purchases">My Purchase</a>
                      <a href="../login_register/logout.php">Logout</a>
                  </div>
              </div>
          <?php else: ?>
              <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
          <?php endif; ?>

          <!-- 🛒 Cart icon with badge -->
          <a href="../HTML/cart.php" class="cart-icon" style="position:relative;">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge" style="display:none;">0</span>
          </a>
      </div>
  </div>
</nav>
<!-- ✅ Toast Notification -->
<?php if (isset($_SESSION['success'])): ?>
  <div class="toast show"><?= $_SESSION['success']; ?></div>
  <?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])): ?>
  <div class="toast error show"><?= $_SESSION['error']; ?></div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- 🐾 Profile Section -->
<div class="profile-container">
  <aside class="account-sidebar">
    <!-- Avatar + username like Shopee -->
    <div class="account-user">
      <a href="#profile" class="au-link" aria-label="Go to profile">
        <img src="<?= $profileImage ?>" alt="Avatar" class="au-avatar">
      </a>
      <div class="au-text">
        <div class="au-name"><?= htmlspecialchars($user['username'] ?? ($user['name'] ?? 'User')) ?></div>
        <a href="#profile" class="au-edit"><i class="fa-solid fa-pen"></i> Edit Profile</a>
      </div>
    </div>

    <ul class="account-menu">
      <li class="account-section">
        <button type="button" class="account-toggle" id="toggle-account" aria-expanded="true">
          <span class="link-with-icon"><i class="fa-regular fa-user icon"></i><span>My Account</span></span>
          <span class="chevron">▾</span>
        </button>
        <ul class="account-submenu open" id="submenu-account">
          <li><a href="#profile">Profile</a></li>
          <li><a href="#addresses">Addresses</a></li>
        </ul>
      </li>
    </ul>

    <ul class="account-menu">
      <li>
        <a href="#purchases" id="link-purchases" class="link-with-icon">
          <i class="fa-regular fa-clipboard icon"></i>
          <span>My Purchase</span>
        </a>
      </li>
    </ul>
  </aside>
  <div class="profile-content">
    <div class="profile-left" id="profile">
    <h2>My Profile</h2>
    <p>Manage and protect your account</p>

    <form id="profileForm" enctype="multipart/form-data">
      <div class="profile-layout">
        <!-- Left side - Form fields -->
        <div class="profile-form-fields">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>

          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>

          <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" id="phone" name="phone"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   pattern="^(09\d{9}|\+639\d{9})$"
                   title="Please enter a valid PH number (e.g. 09171234567 or +639171234567)" required>
          </div>

          <div class="form-group">
            <label>Gender</label>
            <div class="gender-options">
              <label class="radio-label"><input type="radio" name="gender" value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'checked' : '' ?>> Male</label>
              <label class="radio-label"><input type="radio" name="gender" value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'checked' : '' ?>> Female</label>
              <label class="radio-label"><input type="radio" name="gender" value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'checked' : '' ?>> Other</label>
            </div>
          </div>

          <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">
          </div>
        </div>

        <!-- Right side - Profile Picture -->
        <div class="profile-picture-section">
          <div class="profile-img-container">
            <img id="preview" src="<?= $profileImage ?>" alt="Profile Picture" class="profile-preview" role="button" aria-label="Upload profile image">
          </div>
          <input type="file" name="profile_image" id="upload" accept="image/*" style="display:none;">
          <button type="button" class="upload-btn" onclick="document.getElementById('upload').click();">Select Image</button>
          <p class="img-hint">File size: maximum 5 MB<br>File extension: .JPEG, .PNG, .WEBP</p>
        </div>
      </div>

      <button type="submit" class="save-btn">Save Changes</button>
    </form>
  </div>

  <!-- Address Book Section -->
  <div class="address-book" id="addresses" style="display:none;">
    <div class="ab-header">
      <h3 class="address-subtitle">My Addresses</h3>
      <button id="addAddressBtn" class="btn-accent btn-sm"><i class="fa-solid fa-plus"></i> Add New Address</button>
    </div>
    <div id="addressEmpty" class="address-empty">
      <div class="empty-inner">
        <svg viewBox="0 0 64 64" width="80" height="80" aria-hidden="true">
          <g stroke="#cfdbe3" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M32 6c9 0 16 7 16 16 0 11-16 30-16 30S16 33 16 22c0-9 7-16 16-16z"/>
            <circle cx="32" cy="24" r="6"/>
            <path d="M8 54h48"/>
          </g>
        </svg>
        <p class="empty-main">You don't have addresses yet.</p>
      </div>
    </div>
    <div id="addressList" class="address-list rows"></div>
  </div>

  <!-- Purchases Section (history) -->
    <div class="purchases" id="purchases" style="display:none;">
    <div class="p-head">
      <div class="tabs">
        <a href="#" data-tab="all" class="active">All</a>
        <a href="#" data-tab="to_pay">To Pay</a>
        <a href="#" data-tab="to_ship">To Ship</a>
        <a href="#" data-tab="to_receive">To Receive</a>
        <a href="#" data-tab="completed">Completed</a>
        <a href="#" data-tab="cancelled">Cancelled</a>
      </div>
    </div>
    <div class="p-search" style="margin:8px 0 12px;">
      <input id="p-search" placeholder="Search your orders...">
    </div>
    <div id="p-orders" class="orders"></div>
  </div>
  </div>
</div>

<!-- Address Modal -->
<div id="addressModal" class="ab-modal" style="display:none;"></div>

<!-- ✅ Toast -->
<div id="toast" class="toast">
  <i class="fa-solid fa-circle-check"></i> <span id="toastMsg">Saved successfully!</span>
</div>

<!-- 🐾 Footer -->
<footer class="footer">
  <div class="footer-logo">
      <img src="../HTML/images/da458a49866cd4f697e076e5d2e2099f-removebg-preview.png" alt="cat">
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

<script src="../js/cart.js"></script>
<script src="../HTML/js/address-modal.js"></script>
<script src="../HTML/js/profile.js"></script>

</body>
</html>
