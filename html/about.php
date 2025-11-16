<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="Purrfect Paws, cats, Cagayan de Oro City, about us">
  <meta name="description" content="Learn more about Purrfect Paws, our story, mission, and love for cats.">
  <title>About Us | Purrfect Paws</title>
  <link rel="stylesheet" href="css/kumi.css">
  <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar">
    <div class="navdiv">
      <div class="logo"><a href="index.php">Purrfect Paws</a></div>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="product.php">Shop</a></li>
        <li><a href="gallery.php">Gallery</a></li>
      </ul>

      <div class="nav-right">
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php
          $profileImage = isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])
            ? "../HTML/uploads/" . htmlspecialchars($_SESSION['profile_image'])
            : "../HTML/uploads/default.png";
          ?>
          <div class="user-menu">
            <img src="<?= $profileImage ?>" alt="User" class="user-icon">
            <span class="username"><?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
            <div class="dropdown">
              <a href="../profile_php/profile.php">My Account</a>
              <a href="../profile_php/profile.php#purchases">My Purchase</a>
              <a href="../login_register/logout.php">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
        <?php endif; ?>

        <a href="cart.php" class="cart-icon" style="position:relative;">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge" style="display:none;">0</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- About Us Content -->
  <main class="about-page" style="max-width:1000px;margin:40px auto 60px;padding:0 16px;line-height:1.6;">
    <h1 style="font-size:32px;margin-bottom:24px;">About Us</h1>

    <h2 style="font-size:20px;margin:18px 0 8px;">What is Purrfect Paws?</h2>
    <p>
      Purrfect Paws is a cozy cat-focused shop based in Cagayan de Oro City. We provide handpicked
      products made especially for cats and the humans who adore them from comfy beds and toys to
      everyday essentials that keep your feline friends happy, healthy, and entertained.
    </p>

    <h2 style="font-size:20px;margin:18px 0 8px;">Our Story</h2>
    <p>
      Purrfect Paws started with a simple idea: cats give us so much comfort and joy, so we wanted to
      create a place that gives the same back to them. What began as a small passion project quickly
      grew into a full shop, built on love for animals, friendly service, and products we would proudly
      use for our own pets.
    </p>

    <h2 style="font-size:20px;margin:18px 0 8px;">Our Mission</h2>
    <p>
      Our mission is to make life easier and happier for both cats and cat owners. We aim to offer:
    </p>
    <ul style="margin-left:20px;margin-bottom:10px;">
      <li>Quality products that are safe, comfortable, and fun for cats.</li>
      <li>Helpful guidance for new and experienced fur-parents.</li>
      <li>Support for local rescues and responsible pet ownership.</li>
    </ul>

    <h2 style="font-size:20px;margin:18px 0 8px;">Why Shop at Purrfect Paws?</h2>
    <p>
      We carefully select items that balance quality and affordability, so you can spoil your cat
      without worry. Whether you are looking for toys to keep them active, cozy spots for them to nap,
      or basics like bowls and litter essentials, we aim to be your one-stop cat shop.
    </p>

    <p>
      Every visit, every purchase, and every shared story from our customers helps us grow a small
      community built around one thing: making life more purrfect, one paw at a time.
    </p>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-logo">
      <img src="images/da458a49866cd4f697e076e5d2e2099f-removebg-preview.png" alt="cat">
    </div>

    <div class="footer-container">
      <div class="footer-col">
        <h4>Information</h4>
        <ul>
          <li><a href="about.php">About</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="gallery.php">Blogs</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Shopping</h4>
        <ul>
          <li><a href="product.php">Products</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact</h4>
        <p style="font-weight:bold;">431 Captain E. Jabulin St<br>
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

  <script>
    window.PURR_USER_ID = <?= json_encode((string)($_SESSION['user_id'] ?? 'anon')) ?>;
  </script>
  <script src="../js/cart.js?v=user-ns"></script>
</body>
</html>
