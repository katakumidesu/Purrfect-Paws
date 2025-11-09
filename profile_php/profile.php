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
  <link rel="stylesheet" href="../HTML/css/profile.css">
  <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

</head>

<body>

<?php if (isset($_SESSION['success'])): ?>
  <div class="toast" style="display:block;"><?= $_SESSION['success']; ?></div>
  <?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])): ?>
  <div class="toast error" style="display:block;"><?= $_SESSION['error']; ?></div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

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
                      <a href="my_purchases.php">My Purchase</a>
                      <a href="../login_register/logout.php">Logout</a>
                  </div>
              </div>
          <?php else: ?>
              <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
          <?php endif; ?>

          <!-- 🛒 Cart icon -->
          <a href="../HTML/cart.php" class="cart-icon"><i class="fa-solid fa-cart-shopping"></i></a>
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
    <button class="account-toggle"><i class="fa-regular fa-user"></i> <span>My Account</span> <i class="fa fa-chevron-down chevron"></i></button>
    <ul class="account-submenu open">
      <li><a href="#profile">Profile</a></li>
      <li><a href="#addresses">Addresses</a></li>
    </ul>
  </aside>
  <div class="profile-content">
    <div class="profile-left" id="profile">
    <h2>My Profile</h2>
    <p>Manage and protect your account</p>

    <form id="profileForm" enctype="multipart/form-data">
      <label>Full Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>

      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

      <label>Phone Number</label>
      <input type="tel" id="phone" name="phone"
             value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
             pattern="^(09\d{9}|\+639\d{9})$"
             title="Please enter a valid PH number (e.g. 09171234567 or +639171234567)" required>

      <label>Gender</label>
      <div class="gender-options">
        <label><input type="radio" name="gender" value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'checked' : '' ?>> Male</label>
        <label><input type="radio" name="gender" value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'checked' : '' ?>> Female</label>
        <label><input type="radio" name="gender" value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'checked' : '' ?>> Other</label>
      </div>

      <label>Date of Birth</label>
      <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">

      <label>Profile Picture</label>
      <div class="profile-img-container">
        <img id="preview" src="<?= $profileImage ?>" alt="Profile Picture" class="profile-preview" width="150">
        <input type="file" name="profile_image" id="upload" accept="image/*">
      </div>

      <button type="submit" class="save-btn">Save Changes</button>
    </form>
  </div>

<

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

<script src="../js/profile.js"></script>

</body>
</html>
