<?php
session_start();
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
    <link rel="stylesheet" href="../css/kumi.css">
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

   <!-- Home Section -->
    <section class="hero" id="home">
    </section>

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
                <a href="my_purchases.php">My Purchase</a>
                <a href="../login_register/logout.php">Logout</a>
            </div>
        </div>
    <?php else: ?>
        <a href="purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
    <?php endif; ?>

    <!-- 🛒 Cart icon -->
    <a href="cart.php" class="cart-icon"><i class="fa-solid fa-cart-shopping"></i></a>
</div>
    </div>
</nav>

        <!-- Shop Section -->

    <div class="row row-2">
        <h2>All Products</h2>
        <select id="sortSelect" onchange="sortProducts()">
            <option value="default">Default Sorting</option>
            <option value="price-low">Sort by Price (Low to High)</option>
            <option value="price-high">Sort by Price (High to Low)</option>
            <option value="rating">Sort by Rating</option>
        </select>
    </div>


    <section class="shop" id="shop">
        <div class="shop-container" id="productContainer">
            <div class="cat1 product-item" data-price="100.00" data-rating="4">
            <a href="product-detail.php?name=Cat Scratch Post"><img src="images/scratchcat.jpg" alt="Cat Scratch Post"></a>
            <h4>Cat Scratch Post</h4>
            <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 100.00 </strong></p>
            <a href="product-detail.php?name=Cat Scratch Post">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat2 product-item" data-price="10.00" data-rating="4">
            <a href="product-detail.php?name=Cat Mouse Toy"><img src="images/catmouse.jpg" alt="Cat Mouse Toy"></a>
            <h4>Cat Mouse Toy</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 10.00 </strong></p>
            <a href="product-detail.php?name=Cat Mouse Toy">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat3 product-item" data-price="45.00" data-rating="4.5">
            <a href="product-detail.php?name=Cat Bed"><img src="images/catbed.jpg" alt="Cat Bed"></a>
            <h4>Cat Bed</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <p><strong>$ 45.00 </strong></p>
            <a href="product-detail.php?name=Cat Bed">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat4 product-item" data-price="200.00" data-rating="4">
           <a href="product-detail.php?name=Cat Tree"><img src="images/cattree.jpg" alt="Cat Tree"></a>
            <h4>Cat Tree</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 200.00 </strong></p>
            <a href="product-detail.php?name=Cat Tree">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat5 product-item" data-price="15.00" data-rating="3.5">
            <a href="product-detail.php?name=Wiggly Worm Cat Teaser Wand"><img src="images/wiggly worm cat teaser wand.jpg" alt="Wiggly Worm Cat Teaser Wand"></a>
            <h4>Wiggly Worm Cat Teaser Wand</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 15.00 </strong></p>
            <a href="product-detail.php?name=Wiggly Worm Cat Teaser Wand">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat6 product-item" data-price="35.00" data-rating="4">
            <a href="product-detail.php?name=Cat Food Bowl"><img src="images/catfoodbowl.jpg" alt="Cat Food Bowl"></a>
            <h4>Cat Food Bowl</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 35.00 </strong></p>
            <a href="product-detail.php?name=Cat Food Bowl">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat7 product-item" data-price="99.00" data-rating="4">
            <a href="product-detail.php?name=Cat Litter Box"><img src="images/litterbox.jpg" alt="Cat Litter Box"></a>
            <h4>Cat Litter Box</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 99.00 </strong></p>
            <a href="product-detail.php?name=Cat Litter Box">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat8 product-item" data-price="160.20" data-rating="4">
            <a href="product-detail.php?name=Cat Carrier"><img src="images/catcarrier.jpg" alt="Cat Carrier"></a>
            <h4>Cat Carrier</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 160.20 </strong></p>
           <a href="product-detail.php?name=Cat Carrier">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat9 product-item" data-price="40.00" data-rating="5">
            <a href="product-detail.php?name=Cute Cartoon Ceramic Cat Bowl"><img src="images/cute cartoon ceramic cat bowl with high stand.jpg" alt="Cat Ceramic Bowl"></a>
            <h4>Cute Cartoon Ceramic Cat Bowl</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>$ 40.00 </strong></p>
            <a href="product-detail.php?name=Cute Cartoon Ceramic Cat Bowl">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

              <div class="cat10 product-item" data-price="32.00" data-rating="4">
            <a href="product-detail.php?name=Flower Shaped Cat Bed"><img src="images/ed10f9b63f00da532aeae7a698b1a931.jpg" alt="Flower Shaped Cat Bed"></a>
            <h4>Flower Shaped Cat Bed</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>    
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 32.00 </strong></p>
            <a href="product-detail.php?name=Flower Shaped Cat Bed">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

               <div class="cat11 product-item" data-price="32.00" data-rating="4.5">
            <a href="product-detail.php?name=Banana Cat Bed"><img src="images/banana cat bed.jpg" alt="Banana Cat Bed"> </a>  
            <h4>Banana Cat Bed</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <p><strong>$ 32.00 </strong></p>
            <a href="product-detail.php?name=Banana Cat Bed">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

                  <div class="cat12 product-item" data-price="120.00" data-rating="4">
            <a href="product-detail.php?name=Three Tier Flower Cat Tree"><img src="images/three tier flower cat tree.jpg" alt="Three Tier Flower Cat Tree"></a>
            <h4>Three Tier Flower Cat Tree</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 120.00 </strong></p>
            <a href="product-detail.php?name=Three Tier Flower Cat Tree">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

    </section>

            <div class="page-btn">
                <span>1</span>
                <a href="product-1.php"><span>2</span></a>
              <a href="product-1.php"><span>&#8594;</span> </a>   
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

// Dynamically load products from the admin inventory (DB)
const API_URL = '../crud/crud.php';
// Store original order for default sorting
let originalOrder = [];

function getStars(rating) {
  // Render full/half/empty stars while preserving half-stroke icon
  let html = '';
  for (let i = 1; i <= 5; i++) {
    if (rating >= i) html += '<i class="fa-solid fa-star"></i>';
    else if (rating >= i - 0.5) html += '<i class="fa-solid fa-star-half-stroke"></i>';
    else html += '<i class="fa-regular fa-star"></i>';
  }
  return html;
}

function safeImg(src) {
  return (src && src.trim()) ? src : 'images/catbed.jpg';
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str || '';
  return div.innerHTML;
}

function computeRating(p) {
  // Use DB value if present; otherwise derive a stable half-step rating from id/name
  if (p && p.rating != null && p.rating !== '') {
    const r = Math.max(0, Math.min(5, parseFloat(p.rating)));
    return Math.round(r * 2) / 2; // clamp to .5 steps
  }
  const base = (p && p.product_id) ? Number(p.product_id) : String(p.name || '').split('').reduce((s,c)=>s+c.charCodeAt(0),0);
  const choices = [3.5, 4, 4.5, 5];
  return choices[base % choices.length];
}

function renderProducts(list) {
  const container = document.getElementById('productContainer');
  if (!container) return;
  if (!Array.isArray(list) || list.length === 0) {
    container.innerHTML = '<p style="color:#aaa;">No products found.</p>';
    return;
  }

  container.innerHTML = list.map(p => {
    const price = parseFloat(p.price || 0);
   const rating = computeRating(p);
    const name = escapeHtml(p.name || '');
    const img = safeImg(p.image_url);
    return `
      <div class="product-item" data-price="${price.toFixed(2)}" data-rating="${rating}">
        <a href="product-detail.php?name=${encodeURIComponent(name)}">
          <img src="${img}" alt="${name}" onerror="this.src='images/catbed.jpg'">
        </a>
        <h4>${name}</h4>
        <div class="rating">${getStars(rating)}</div>
        <p><strong>$ ${price.toFixed(2)}</strong></p>
        <a href="product-detail.php?name=${encodeURIComponent(name)}">
          <button class="purchase-btn">Purchase</button>
        </a>
      </div>
    `;
  }).join('');

  // Remember original DOM order for "Default" sorting
  const items = Array.from(container.querySelectorAll('.product-item'));
  originalOrder = items.map((el, idx) => ({ element: el, originalIndex: idx }));
}

async function loadProducts() {
  try {
    const res = await fetch(`${API_URL}?action=get_products`);
    const data = await res.json();
    if (data && !data.error) {
      renderProducts(data);
    } else {
      console.error('Error loading products:', data.error);
    }
  } catch (e) {
    console.error('Error loading products:', e);
  }
}

document.addEventListener('DOMContentLoaded', loadProducts);

function sortProducts() {
    const select = document.getElementById('sortSelect');
  const sortValue = select.value;
  const container = document.getElementById('productContainer');
  if (!container) return;

  const products = Array.from(container.querySelectorAll('.product-item'));

  if (sortValue === 'default') {
    products.sort((a, b) => {
      const indexA = originalOrder.findIndex(item => item.element === a);
      const indexB = originalOrder.findIndex(item => item.element === b);
      return indexA - indexB;
    });
  } else if (sortValue === 'price-low') {
    products.sort((a, b) => parseFloat(a.dataset.price || '0') - parseFloat(b.dataset.price || '0'));
  } else if (sortValue === 'price-high') {
    products.sort((a, b) => parseFloat(b.dataset.price || '0') - parseFloat(a.dataset.price || '0'));
  } else if (sortValue === 'rating') {
    products.sort((a, b) => parseFloat(b.dataset.rating || '0') - parseFloat(a.dataset.rating || '0'));
  }

  products.forEach(p => container.appendChild(p));
}


products.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price'));
            const priceB = parseFloat(b.getAttribute('data-price'));
            return priceB - priceA;
        });
    else if (sortValue === 'rating') {
        // Sort by rating: high to low
        products.sort((a, b) => {
            const ratingA = parseFloat(a.getAttribute('data-rating'));
            const ratingB = parseFloat(b.getAttribute('data-rating'));
            return ratingB - ratingA;
        });
    }
    
    // Re-append products in sorted order
    products.forEach(product => container.appendChild(product));
}
</script>

</body>

</html>