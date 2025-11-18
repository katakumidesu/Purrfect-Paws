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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />




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
            <li><a href="../html/gallery.php">Gallery</a></li>
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

             <a href= ../html/gallery.php><img src="images/meme1.jpg" alt="Cat 1"></a>
             <a href= ../html/gallery.php><img src="images/meme2.jpg" alt="Cat 2"></a>
             <a href= ../html/gallery.php><img src="images/meme3.jpg" alt="Cat 3"></a>
             <a href= ../html/gallery.php><img src="images/meme4.jpg" alt="Cat 4"></a>
             <a href= ../html/gallery.php><img src="images/meme5.jpg" alt="Cat 5"></a>
             <a href= ../html/gallery.php><img src="images/meme6.jpg" alt="Cat 6"></a>
             <a href= ../html/gallery.php><img src="images/meme7.jpg" alt="Cat 7"></a>
             <a href= ../html/gallery.php><img src="images/meme8.jpg" alt="Cat 8"></a>
             <a href= ../html/gallery.php><img src="images/meme9.jpg" alt="Cat 9"></a>
             <a href= ../html/gallery.php><img src="images/mixie1.png" alt="Cat 10"></a>
             <a href= ../html/gallery.php><img src="images/mixie2.png" alt="Cat 11"></a>
             <a href= ../html/gallery.php><img src="images/meme10.jpg" alt="Cat 12"></a>
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
          <li><a href="../html/about.php">About</a></li>
          <li><a href="../html/contact.php">Contact Us</a></li>
          <li><a href="../html/gallery.php">Blogs</a></li>
        </ul>
      </div>

        <div class="footer-col">
            <h4>Shopping</h4>
            <ul>
                <li><a href="../html/product.php">Products</a></li>
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

  // Apply global ratings (from database) to static homepage product stars
  document.addEventListener('DOMContentLoaded', async function () {
    try {
      const res = await fetch('../crud/crud.php?action=get_products&available_only=1&_=' + Date.now());
      const data = await res.json();
      if (!Array.isArray(data)) return;

      const ratingByName = {};
      const priceByName = {};
      data.forEach(p => {
        const name = (p.name || '').trim();
        if (!name) return;
        const key = name.toLowerCase();
        const r = p.rating != null ? Number(p.rating) : 5;
        if (isFinite(r) && r > 0) {
          ratingByName[key] = Math.max(0, Math.min(5, r));
        }
        const price = p.price != null ? Number(p.price) : null;
        if (isFinite(price) && price >= 0) {
          priceByName[key] = price;
        }
      });

      const cards = document.querySelectorAll('.shop-container > div');
      cards.forEach(card => {
        const titleEl = card.querySelector('h4');
        const ratingEl = card.querySelector('.rating');
        if (!titleEl || !ratingEl) return;
        const name = titleEl.textContent.trim();
        const key = name.toLowerCase();
        const avg = ratingByName[key];
        const price = priceByName[key];

        // Apply rating stars if we have a rating from DB
        if (avg != null) {
          const stars = ratingEl.querySelectorAll('i');
          stars.forEach((star, idx) => {
            const i = idx + 1;
            star.classList.remove('fa-solid', 'fa-regular', 'fa-star', 'fa-star-half-stroke');
            if (avg >= i) {
              star.classList.add('fa-solid', 'fa-star');
            } else if (avg >= i - 0.5) {
              star.classList.add('fa-solid', 'fa-star-half-stroke');
            } else {
              star.classList.add('fa-regular', 'fa-star');
            }
          });
        }

        // Apply price from DB if available
        if (price != null) {
          const priceEl = card.querySelector('p strong');
          if (priceEl) {
            priceEl.textContent = '\u20b1 ' + Number(price).toFixed(2);
          }
        }
      });
    } catch (e) {
      console.error('Failed to apply homepage ratings', e);
    }
  });
  // Click-to-expand testimonials (simple modal)
  document.addEventListener('DOMContentLoaded', function(){
    const cards = document.querySelectorAll('.testimonial-card');
    if (!cards.length) return;

    let modal = document.getElementById('testimonialModal');
    if (!modal){
      modal = document.createElement('div');
      modal.id = 'testimonialModal';
      modal.style.position = 'fixed';
      modal.style.inset = '0';
      modal.style.background = 'rgba(15,23,42,0.55)';
      modal.style.display = 'none';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.style.zIndex = '9999';
      modal.innerHTML = `
        <div style="background:#fff;border-radius:10px;max-width:360px;width:90%;padding:32px 28px;box-shadow:0 18px 45px rgba(15,23,42,.35);position:relative;text-align:center;">
          <button type="button" id="tmClose" style="position:absolute;top:10px;right:12px;border:none;background:transparent;font-size:20px;cursor:pointer;line-height:1;">&times;</button>
          <i class="fa-solid fa-quote-left" style="font-size:1.6rem;color:#FFD700;margin-bottom:14px;display:block;"></i>
          <div id="tmQuote" style="font-size:14px;color:#333;margin-bottom:14px;min-height:80px;"></div>
          <div id="tmRating" style="color:#facc15;margin-bottom:14px;font-size:18px;"></div>
          <img id="tmAvatar" src="" alt="" style="width:70px;height:70px;border-radius:50%;object-fit:cover;margin-bottom:10px;display:none;">
          <div id="tmName" style="font-weight:600;color:#111;font-size:15px;"></div>
        </div>`;
      document.body.appendChild(modal);
      modal.addEventListener('click', function(e){ if (e.target === modal) modal.style.display='none'; });
      modal.querySelector('#tmClose').addEventListener('click', function(){ modal.style.display='none'; });
    }

    const quoteEl = modal.querySelector('#tmQuote');
    const nameEl = modal.querySelector('#tmName');
    const ratingEl = modal.querySelector('#tmRating');
    const avatarEl = modal.querySelector('#tmAvatar');

    cards.forEach(card => {
      card.style.cursor = 'pointer';
      card.addEventListener('click', function(){
        const text = card.querySelector('p')?.textContent || '';
        const name = card.querySelector('h4, h3')?.textContent || '';
        const stars = card.querySelector('.rating')?.innerHTML || '';
        const img = card.querySelector('img');
        if (quoteEl) quoteEl.textContent = text;
        if (nameEl) nameEl.textContent = name;
        if (ratingEl) ratingEl.innerHTML = stars;
        if (avatarEl){
          if (img && img.src){
            avatarEl.src = img.src;
            avatarEl.style.display = 'inline-block';
          } else {
            avatarEl.style.display = 'none';
          }
        }
        modal.style.display = 'flex';
      });
    });
  });

</script>
<script src="../js/cart.js?v=user-ns"></script>
</body>

</html>