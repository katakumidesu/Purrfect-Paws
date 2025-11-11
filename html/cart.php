<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_register/purdex.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Purrfect cats, cats, Cagayan de Oro City">
    <meta name="description" content="Purrfect Paws - Your one-stop shop for all things cat-related in Cagayan de Oro City.">
    <title>Shop Products - Purrfect Paws</title>
    <link rel="stylesheet" href="css/kumi.css">
    <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/
    lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">



    <style>
        img {
            width: 250px;
            border-radius: 10px;
        }

        p {
            font-size: 20px;
            font-family: "Times New Roman";
            font-weight: bold;
        }
    </style>
</head>



<body>

      <!-- Home Section -->
    <section class="hero" id="home">
    </section>

    <!-- Navigation Bar -->
    <nav class="navbar">
    <div class="navdiv">
        <div class="logo"><a href="index.php">Purrfect Paws</a></div>  
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="product.php">Shop</a></li>
            <li><a href="#gallery">Gallery</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
        
<div class="nav-right">
    <!-- 🧑‍💼 User dropdown OR Login -->
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
                <a href="my_purchases.php">My Purchase</a>
                <a href="../login_register/logout.php">Logout</a>
            </div>
        </div>
    <?php else: ?>
        <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
    <?php endif; ?>

    <!-- 🛒 Cart icon -->
    <a href="cart.php" class="cart-icon" style="position:relative;">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-badge" style="display:none;">0</span>
    </a>
</div>
        </div>
    </div>
</nav>

<!----cart---->

<div class="small-container cart-page">
    <h2 style="text-align: center; margin: 30px 0;">Shopping Cart</h2>
    <table>
        <tr>
            <th style="width:42px;text-align:center;"><input type="checkbox" id="checkAll"></th>
            <th>Product</th>
            <th style="width:120px;text-align:center;">Unit Price</th>
            <th style="width:160px;text-align:center;">Quantity</th>
            <th style="width:120px;text-align:center;">Total Price</th>
            <th style="width:110px;text-align:center;">Actions</th>
        </tr>
        <tbody id="cartItems">
            <!-- Cart items loaded dynamically via JavaScript -->
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #aaa;">
                    <i class="fa fa-spinner fa-spin" style="font-size: 48px;"></i>
                    <p style="margin-top: 20px;">Loading cart...</p>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="total-price" id="cartTotals">
        <!-- Totals calculated dynamically via JavaScript -->
    </div>
    <!-- Sticky checkout bar like Shopee -->
    <div id="checkoutBar" class="checkout-bar" style="display:none;"></div>
</div>
 
    
<!-----footer----->
<footer class="footer">
    <div class="footer-logo">
        <img src="images/da458a49866cd4f697e076e5d2e2099f-removebg-preview.png" alt="cat">
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
<script>
  // Hide cart icon on cart page
  document.addEventListener('DOMContentLoaded', () => {
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
      cartIcon.style.display = 'none';
    }
  });
</script>
</body>

</html>
