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
    <meta name="description" content="Purrfect Paws - Cat gallery.">
    <title>Gallery - Purrfect Paws</title>
    <link rel="stylesheet" href="css/kumi.css">
    <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gallery.css">
       
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navdiv">
            <div class="logo"><a href="index.php">Purrfect Paws</a></div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="product.php">Shop</a></li>
                <li><a href="gallery.php" class="active">Gallery</a></li>
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

    <!-- Gallery Page -->
    <section class="gallery-page">
        <h2>Gallery</h2>
         <div class="gallery-logo">
            <img src="images/cat_gallery-removebg-preview.png" alt="cat">
                </div>
        <div class="gallery-strip-wrapper">
            <div class="gallery-strip">
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme1.jpg" alt="Cat 1" data-desc="A curious kitty giving you the sweetest stare.">
                    </div>
                    <div class="gallery-caption">"Beluga"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme2.jpg" alt="Cat 2" data-desc="Tiny orange baby cat sitting like a model.">
                    </div>
                    <div class="gallery-caption">"Munchkin kitten"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme3.jpg" alt="Cat 3" data-desc="Goofy cat with tongue out having the time of its life.">
                    </div>
                    <div class="gallery-caption">"Rigby"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme4.jpg" alt="Cat 4" data-desc="Wet angry cat that clearly did not order a bath.">
                    </div>
                    <div class="gallery-caption">"Lykoi"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme5.jpg" alt="Cat 5" data-desc="Zoomed-in boopable nose ready for cuddles.">
                    </div>
                    <div class="gallery-caption">"Rob"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme6.jpg" alt="Cat 6" data-desc="Messy little gremlin cat enjoying its food.">
                    </div>
                    <div class="gallery-caption">"Black Cat"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme7.jpg" alt="Cat 7" data-desc="Sleepy kitten ready to nap after a long day.">
                    </div>
                    <div class="gallery-caption">"Eepy Cat"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme8.jpg" alt="Cat 8" data-desc="Fluffy cloud cat showing off its chubby cheeks.">
                    </div>
                    <div class="gallery-caption">"Smirk Cat"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme9.jpg" alt="Cat 9" data-desc="Drama queen cat looking up like a superstar.">
                    </div>
                    <div class="gallery-caption">"Rigby"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/mixie1.png" alt="Cat 10" data-desc="Cute cat with a heart accessory on its head.">
                    </div>
                    <div class="gallery-caption">"Mixie"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/mixie2.png" alt="Cat 11" data-desc="Sleepy kitty dreaming of treats and toys.">
                    </div>
                    <div class="gallery-caption">"Mixie"</div>
                </div>
                <div class="gallery-card">
                    <div class="gallery-card-inner">
                        <img src="images/meme10.jpg" alt="Cat 12" data-desc="Playful baby cat exploring the world.">
                    </div>
                    <div class="gallery-caption">"German Cat"</div>
                </div>
            </div>
        </div>
    </section>

    <div class="lightbox-overlay" id="lightboxOverlay">
        <div class="lightbox-content">
            <div class="lightbox-image-wrapper">
                <img src="" alt="Preview" id="lightboxImage">
            </div>
            <div class="lightbox-description">
                <h3 id="lightboxTitle">Cat</h3>
                <p id="lightboxText">Cute cat description goes here.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
                    <li><a href="#">Contact Us</a></li>
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
        (function () {
            const overlay = document.getElementById('lightboxOverlay');
            const imgEl = document.getElementById('lightboxImage');
            const titleEl = document.getElementById('lightboxTitle');
            const textEl = document.getElementById('lightboxText');
            const strip = document.querySelector('.gallery-strip');
            const cards = strip ? Array.from(strip.querySelectorAll('.gallery-card')) : [];
            if (!overlay || !imgEl || !titleEl || !textEl) return;

            function openLightbox(src, name, desc) {
                imgEl.src = src;
                imgEl.alt = name || '';
                titleEl.textContent = name || 'Cat';
                textEl.textContent = desc || '';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            if (strip) {
                strip.addEventListener('click', (e) => {
                    const img = e.target.closest('.gallery-card-inner img');
                    if (!img) return;
                    img.style.cursor = 'pointer';
                    const card = img.closest('.gallery-card');
                    const captionEl = card ? card.querySelector('.gallery-caption') : null;
                    const name = captionEl ? captionEl.textContent.replace(/"/g, '').trim() : (img.alt || 'Cat');
                    openLightbox(img.src, name, img.getAttribute('data-desc'));
                });
            }

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeLightbox();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeLightbox();
                }
            });

            // Simple auto-slide show: go through each cat then wrap back to first
            if (strip && cards.length > 1) {
                let currentIndex = 0;
                const cardWidth = cards[0].offsetWidth + 32; // width + gap
                const maxIndex = cards.length - 1;

                function goTo(index) {
                    currentIndex = index;
                    if (currentIndex > maxIndex) {
                        currentIndex = 0;
                    }
                    const offset = -currentIndex * cardWidth;
                    strip.style.transform = `translateX(${offset}px)`;
                }

                setInterval(() => {
                    goTo(currentIndex + 1);
                }, 2500);
            }
        })();
    </script>
</body>
</html>
