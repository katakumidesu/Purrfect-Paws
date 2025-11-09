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
            <div class="cat13 product-item" data-price="70.00" data-rating="4">
            <a href="product-detail.php?name=Pink Ceramic Raised Cat Bowl"><img src="images/Pink Ceramic Raised Cat Bowl.jpg" alt="Pink Ceramic Raised Cat Bowl"></a>
            <h4>Pink Ceramic Raised Cat Bowl</h4>
            <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 70.00 </strong></p>
            <a href="product-detail.php?name=Pink Ceramic Raised Cat Bowl">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat14 product-item" data-price="60.00" data-rating="4">
            <a href="product-detail.php?name=Cute Mushroom Raised Cat Bowl"><img src="images/Cute Mushroom Raised Cat Bowl.jpg" alt="Cute Mushroom Raised Cat Bowl"></a>
            <h4>Cute Mushroom Raised Cat Bowl</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 60.00 </strong></p>
            <a href="product-detail.php?name=Cute Mushroom Raised Cat Bowl">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat15 product-item" data-price="110.00" data-rating="4.5">
            <a href="product-detail.php?name=Foldable Cat Carrier Bag"><img src="images/Foldable Cat Carrier Bag.jpg" alt="Foldable Cat Carrier Bag"></a>
            <h4>Foldable Cat Carrier Bag</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <p><strong>$ 110.00 </strong></p>
            <a href="product-detail.php?name=Foldable Cat Carrier Bag">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat16 product-item" data-price="300.00" data-rating="5">
            <a href="product-detail.php?name=Exercise Wheel For Cat"><img src="images/Exercise Wheel For Cat.jpg" alt="Exercise Wheel For Cat"></a>
            <h4>Exercise Wheel For Cat</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>$ 300.00 </strong></p>
            <a href="product-detail.php?name=Exercise Wheel For Cat">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat17 product-item" data-price="20.00" data-rating="3.5">
            <a href="product-detail.php?name=Cat Tumbler Ball Toy"><img src="images/Cat Tumbler Boy Toy.jpg" alt="Cat Tumbler Ball Toy"></a>
            <h4>Cat Tumbler Ball Toy</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 20.00 </strong></p>
            <a href="product-detail.php?name=Cat Tumbler Ball Toy">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat18 product-item" data-price="35.00" data-rating="5">
            <a href="product-detail.php?name=Spaceship Litter Box"><img src="images/Spaceship Litter Box.jpg" alt="Spaceship Litter Box"></a>
            <h4>Spaceship Litter Box</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <p><strong>$ 35.00 </strong></p>
            <a href="product-detail.php?name=Spaceship Litter Box">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat19 product-item" data-price="9.00" data-rating="4">
            <a href="product-detail.php?name=Hollow Plastic Ball"><img src="images/Hollow Plastic Ball.webp" alt="Hollow Plastic Ball"></a>
            <h4>Hollow Plastic</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 9.00 </strong></p>
            <a href="product-detail.php?name=Hollow Plastic Ball">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat20 product-item" data-price="130.20" data-rating="4">
            <a href="product-detail.php?name=Mushroom Cat Scratcher"><img src="images/Mushroom Cat Scratcher.jpg" alt="Mushroom Cat Scratcher"></a>
            <h4>Mushroom Cat Scratcher</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 130.20 </strong></p>
            <a href="product-detail.php?name=Mushroom Cat Scratcher">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

            <div class="cat21 product-item" data-price="150.00" data-rating="4">
            <a href="product-detail.php?name=Cupcake Cat Tree"><img src="images/cupcake cat tree.jpg" alt="Cupcake Cat Tree"></a>
            <h4>Cupcake Cat Tree</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 150.00 </strong></p>
            <a href="product-detail.php?name=Cupcake Cat Tree">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

              <div class="cat22 product-item" data-price="16.00" data-rating="4">
           <a href="product-detail.php?name=Cat Frog Bed"><img src="images/c40339c1a8de417f0b4ea5d968799846.jpg" alt="Cat Frog Bed"></a> 
            <h4>Cat Frog Bed</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 16.00 </strong></p>
            <a href="product-detail.php?name=Cat Frog Bed">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

               <div class="cat23 product-item" data-price="60.00" data-rating="4.5">
            <a href="product-detail.php?name=3-in-1 Interactive Cat Toy"><img src="images/3n1 Interactive Toy With Fluttering Butterfly Led Light Automatic Cat Toy.jpg" alt="3-in-1 Interactive Cat Toy"></a>
            <h4>3-in-1 Interactive Cat Toy</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star-half-stroke"></i>
            </div>
            <p><strong>$ 60.00 </strong></p>
            <a href="product-detail.php?name=3-in-1 Interactive Cat Toy">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

                <div class="cat24 product-item" data-price="132.00" data-rating="4">
            <a href="product-detail.php?name=Flower Cat Tree"><img src="images/Flower Cat Tree.jpg" alt="Flower Cat Tree"></a>
            <h4>Flower Cat Tree</h4>
             <div class="rating">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-regular fa-star"></i>
            </div>
            <p><strong>$ 132.00 </strong></p>
            <a href="product-detail.php?name=Flower Cat Tree">
            <button class="purchase-btn">Purchase</button>
            </a>
            </div>

    </section>

            <div class="page-btn">
                <a href="product.php"><span>&#8592;</span></a>
                <a href="product.php"><span>1</span></a>
                <span>2</span>
                
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
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Terms of Use</a></li>
                <li><a href="#">Privacy Statement</a></li>
                <li><a href="#">Cookie Policy</a></li>
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
// Store original order for default sorting
let originalOrder = [];

// Initialize original order on page load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('productContainer');
    if (container) {
        const products = Array.from(container.querySelectorAll('.product-item'));
        originalOrder = products.map((product, index) => ({
            element: product,
            originalIndex: index
        }));
    }
});

function sortProducts() {
    const select = document.getElementById('sortSelect');
    const sortValue = select.value;
    const container = document.getElementById('productContainer');
    
    if (!container) return;
    
    const products = Array.from(container.querySelectorAll('.product-item'));
    
    if (sortValue === 'default') {
        // Restore original order
        products.sort((a, b) => {
            const indexA = originalOrder.findIndex(item => item.element === a);
            const indexB = originalOrder.findIndex(item => item.element === b);
            return indexA - indexB;
        });
    } else if (sortValue === 'price-low') {
        // Sort by price: low to high
        products.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price'));
            const priceB = parseFloat(b.getAttribute('data-price'));
            return priceA - priceB;
        });
    } else if (sortValue === 'price-high') {
        // Sort by price: high to low
        products.sort((a, b) => {
            const priceA = parseFloat(a.getAttribute('data-price'));
            const priceB = parseFloat(b.getAttribute('data-price'));
            return priceB - priceA;
        });
    } else if (sortValue === 'rating') {
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