<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Purrfect Paws, cats, Cagayan de Oro City">
    <meta name="description" content="Purrfect Paws - Your one-stop shop for all things cat-related in Cagayan de Oro City.">
    <title>Purrfect Paws</title>
    <link rel="stylesheet" href="css/kumi.css">
    <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/
    lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />




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
                <a href="../profile_php/profile.php#purchases">My Purchase</a>
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
</nav>

    <!-- Home Section -->
    <section class="hero" id="home">
   
    </section>

    <!-- Image Slider -->    
    <section class ="slider">
      <div class="slider">
      <figure>
        <div class="negrobanner">
          <img src="images/catba.jpg" alt="cat.jpg">
      </figure>
    </div>
   

</section>
    <!-- About Us Section -->

    <section class="about" id="about"> 
        <div class="about-container">
            <div class="about-img">
                <img src="https://i.pinimg.com/736x/32/40/9c/32409c308250536f63af83b79d9e198b.jpg" width="21312" height="350px"
                    alt="About Us Image">
            </div>
            <div class="about-text">
                <h2>Purrfect Paws</h2>
                <p> At Purrfect Paws, we believe cats make life better — and we’re here to return the favor. From cozy essentials to playful accessories, our shop is made for cats and the people who love them.</p>
                <p> More than just a store, we’re on a mission to help cats in need by supporting shelters, fostering, and promoting adoption. Every purchase helps us spread a little more love, comfort, and care — one paw at a time. 🐾 </p>
              </div>
        </div>

        <!-- Shop Section -->

    <section class="shop" id="shop">
        <h2>Shop Products</h2>
        <div class="shop-container">
            
            
            <div class cat1>
            <a href="product-detail.php?name=Cat Scratch Post"><img src="images/scratchcat.jpg" alt="Scratch Cat"></a>
            <h4>Cat Scratch Post</h4>
            <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 100.00 </strong></p>
            <a href="product-detail.php?name=Cat Scratch Post">   
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat2>
            <a href="product-detail.php?name=Cat Mouse Toy"><img src="images/catmouse.jpg" alt="mousetoy.jpg"></a>
            <h4>Cat Mouse Toy</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 10.00 </strong></p>
            <a href="product-detail.php?name=Cat Mouse Toy">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat3>
           <a href="product-detail.php?name=Cat Bed"><img src="images/catbed.jpg" alt="catbed.jpg"></a>
            <h4>Cat Bed</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 45.00 </strong></p>
            <a href="product-detail.php?name=Cat Bed">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat4>
            <a href="product-detail.php?name=Cat Tree"><img src="images/cattree.jpg" alt="cattree.jpg"></a>
            <h4>Cat Tree</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 200.00 </strong></p>
            <a href="product-detail.php?name=Cat Tree">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat5>
           <a href="product-detail.php?name=Wiggly Worm Cat Teaser Wand"><img src="images/wiggly worm cat teaser wand.jpg" alt="teaserwand.jpg"></a>
            <h4>Wiggly Worm Cat Teaser Wand</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 15.00 </strong></p>
            <a href="product-detail.php?name=Wiggly Worm Cat Teaser Wand">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat6>
           <a href="product-detail.php?name=Cat Food Bowl"><img src="images/catfoodbowl.jpg" alt="catfoodbowl.jpg"></a>
            <h4>Cat Food Bowl</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 35.00 </strong></p>
            <a href="product-detail.php?name=Cat Food Bowl">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat7>
            <a href="product-detail.php?name=Cat Litter Box"><img src="images/litterbox.jpg" alt="catlitterbox.jpg"></a>
            <h4>Cat Litter Box</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 99.00 </strong></p>
            <a href="product-detail.php?name=Cat Litter Box">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class cat8>
            <a href="product-detail.php?name=Cat Carrier"><img src="images/catcarrier.jpg" alt="catcarrier.jpg"></a>
            <h4>Cat Carrier</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>₱ 160.20 </strong></p>
            <a href="product-detail.php?name=Cat Carrier">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

    </section>


    <!-- Gallery Section -->
     <section class="gallery" id="gallery">
        <h2>Gallery</h2>
        <div class="gallery-container">
            <img src="images/meme1.jpg" alt="Cat 1">
            <img src="images/meme2.jpg" alt="Cat 2">
            <img src="images/meme3.jpg" alt="Cat 3">
            <img src="images/meme4.jpg" alt="Cat 4">
            <img src="images/meme5.jpg" alt="Cat 5">
            <img src="images/meme6.jpg" alt="Cat 6">
            <img src="images/meme7.jpg" alt="Cat 7">
            <img src="images/meme8.jpg" alt="Cat 8">
            <img src="images/meme9.jpg" alt="Cat 9">
            <img src="images/mixie1.png" alt="Cat 10">
            <img src="images/mixie2.png" alt="Cat 11">
            <img src="images/meme10.jpg" alt="Cat 12">
        </div>
        </section>
    
<div class="testimonial"> 
    <h2>What Our Customers Say</h2>
      <div class="testimonial-container">
        <div class="testimonial-card">
          <i class="fa-solid fa-quote-left"></i>
          <p>Purrfect Paws has transformed my cat's life! The toys and accessories are top-notch, and the staff is incredibly knowledgeable and friendly. Highly recommend!</p>
          
          <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
              <img src="images/chaeyoung.jpg">
              <h4>Son Chaeyoung</h4>
        </div>
        <div class="testimonial-card">
          <i class="fa-solid fa-quote-left"></i>
          <p>From toys to essentials, every product I bought at Purrfect Paws is high-quality and worth it. My kitty approves!</p>
          
          <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
      </div>
      <img src="images/karina.jpg">
      <h4>Yu Ji-min</h4>
    </div>

        <div class="testimonial-card">
          <i class="fa-solid fa-quote-left"></i>
          <p>Shopping at Purrfect Paws feels personal — they really care about cats and it shows in their products and service</p>
          
          <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
      </div>
      <img src="images/iu.jpg">
      <h4>Lee Ji-eun</h4>

      </div>

  </div>

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
<script>
  window.PURR_USER_ID = <?= json_encode((string)($_SESSION['user_id'] ?? 'anon')) ?>;
</script>
<script src="../js/cart.js?v=user-ns"></script>
<script src="../js/product-details.js"></script>
</body>

</html>