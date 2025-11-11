<?php
session_start();
require_once 'config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login_register/purdex.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Purrfect Paws</title>
  <link rel="stylesheet" href="css/kumi.css">
  <script src="https://kit.fontawesome.com/df5d6157cf.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root{--brand:#9bd8f7;--brand-strong:#5cbfef;--brand-text:#003a57}
    body{background:#f5f5f5}
    .container{max-width:1000px;margin:24px auto;padding:0 16px}
    .checkout-grid{display:grid;grid-template-columns:2fr 1fr;gap:16px}
    .card{background:#fff;border:1px solid #e5e5e5;border-radius:8px}
    .card h3{margin:0 0 8px 0}
    .card .body{padding:14px}
    .delivery-address{background:#fff;color:#222;border-radius:8px;margin:0 0 16px;border:1px solid #e5e5e5}
    .delivery-address .da-head{padding:12px 14px;border-bottom:1px dashed #e9eef2;font-weight:700;display:flex;align-items:center;gap:8px;color:#003a57}
    .delivery-address .da-body{padding:12px 14px;position:relative}
    .delivery-address .da-line1 .muted{color:#6b8897;font-weight:500}
    .delivery-address .badge{display:inline-block;background:#fff0f0;color:#c92a2a;border:1px solid #ff6b6b;padding:2px 8px;border-radius:6px;font-size:12px;margin-left:8px}
    .delivery-address .da-change{position:absolute;right:14px;top:12px;color:#1a73e8;text-decoration:none}
    .delivery-address .da-change:hover{text-decoration:underline}
    .shop-row{display:flex;align-items:center;gap:10px;padding:10px 14px}
    .shop-row img{width:60px;height:60px;border-radius:6px;object-fit:cover;border:1px solid #eee}
.shop-row .title{font-weight:700;color:#222;text-decoration:none}
    .shop-row .muted{color:#6b8897;font-size:12px}
    .right{margin-left:auto;color:#333}
    .summary table{width:100%;border-collapse:collapse}
    .summary td{padding:8px 0}
    .summary tr:last-child td{font-weight:700;border-top:1px solid #eee}
    .place-order{width:100%;padding:12px;background:var(--brand-strong);color:#003044;border:none;border-radius:6px;font-weight:700;cursor:pointer}
    .place-order:hover{background:#3fb2ea}
    @media(max-width: 880px){.checkout-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
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
    </div>
</nav>

  <div class="container">
    <div id="addressBox" class="delivery-address">
      <div class="da-head"><i class="fa fa-location-dot"></i> Delivery Address</div>
      <div class="da-body">Loading address...</div>
    </div>

    <div class="checkout-grid">
      <div>
        <div class="card">
          <div class="body">
            <h3>Products Ordered</h3>
          </div>
          <div id="orderItems"></div>
        </div>
      </div>
      <div>
        <div class="card">
          <div class="body summary">
            <h3>Order Summary</h3>
            <button id="placeOrder" class="place-order" style="margin-top:12px"><i class="fa fa-check-circle"></i> Place Order</button>
          </div>
        </div>
      </div>
    </div>
  </div>

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

  <script src="../js/cart.js?v=no-tax"></script>
  <script>
    // Namespace orders per logged-in user so browser sessions don't mix
    window.PURR_USER_ID = <?= json_encode((string)($_SESSION['user_id'] ?? 'anon')) ?>;
    const ORDERS_KEY = () => `purrfectOrders:${window.PURR_USER_ID||'anon'}`;
    async function fetchDefaultAddress(){
      try{ const res = await fetch('../profile_php/addresses.php?action=list');
        const items = await res.json();
        if(!Array.isArray(items)) return null;
        return items.find(a => String(a.is_default)==='1') || items[0] || null;
      }catch(e){ return null; }
    }

    function esc(s){ const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

    async function renderAddress(){
      const box = document.getElementById('addressBox');
      const a = await fetchDefaultAddress();
      if(!a){
        box.innerHTML = '<div class="da-head"><i class="fa fa-location-dot"></i> Delivery Address</div><div class="da-body">No saved address yet. <a class="da-change" href="../profile_php/profile.php#addresses">Add address</a></div>';
        return;
      }
      box.innerHTML = `
        <div class="da-head"><i class="fa fa-location-dot"></i> Delivery Address</div>
        <div class="da-body">
          <div class="da-line1"><strong>${esc(a.fullname||'')}</strong> <span class="muted">(${esc(a.phone||'')})</span> ${String(a.is_default)==='1'?'<span class="badge">Default</span>':''}</div>
          <div class="da-line2">${esc(a.address_line||'')}${a.barangay? ', '+esc(a.barangay):''}${a.city? ', '+esc(a.city):''}${a.province? ', '+esc(a.province):''}${a.postal_code? ' '+esc(a.postal_code):''}</div>
          <a class="da-change" href="../profile_php/profile.php#addresses">Change</a>
        </div>`;
    }

    function renderCartToCheckout(){
      const all = getCart();
      const selected = (()=>{ try { return JSON.parse(sessionStorage.getItem('purrfectSelected')||'[]'); } catch(e){ return []; } })();
      const items = selected.length ? all.filter(it => selected.includes(it.name)) : all;
      const wrap = document.getElementById('orderItems');
      if(!items || items.length===0){
        wrap.innerHTML = '<div class="body">Your cart is empty. <a href="product.php">Go shopping</a>.</div>';
        return;
      }
      wrap.innerHTML = items.map(it=>`
        <div class="shop-row">
          <img src="${it.image}" alt="${esc(it.name)}" onerror="this.src='images/catbed.jpg'">
          <div>
            <div class="title">${esc(it.name)}</div>
            <div class="muted">Qty: ${it.quantity}</div>
          </div>
          <div class="right">$${(it.price*it.quantity).toFixed(2)}</div>
        </div>
      `).join('');

      const sums = calculateTotals(items);
    }

    document.getElementById('placeOrder').addEventListener('click', function(){
      const all = getCart();
      const selected = (()=>{ try { return JSON.parse(sessionStorage.getItem('purrfectSelected')||'[]'); } catch(e){ return []; } })();
      const items = selected.length ? all.filter(it => selected.includes(it.name)) : all;
      if(!items || items.length===0){ alert('Your cart is empty'); return; }
      const sums = calculateTotals(items);
      const orders = JSON.parse(sessionStorage.getItem(ORDERS_KEY()) || '[]');
      orders.push({ items, total: sums.total, date: new Date().toISOString(), status: 'to_pay' });
      sessionStorage.setItem(ORDERS_KEY(), JSON.stringify(orders));
      // Remove only purchased items from cart if a subset was selected
      if (selected.length){
        const remaining = all.filter(it => !selected.includes(it.name));
        sessionStorage.setItem('purrfectCart', JSON.stringify(remaining));
        sessionStorage.removeItem('purrfectSelected');
      } else {
        sessionStorage.removeItem('purrfectCart');
      }
      if (typeof updateCartBadge === 'function') updateCartBadge();
      window.location.href = '../profile_php/profile.php#purchases:to_pay';
    });

    // Init
    renderAddress();
    renderCartToCheckout();
  </script>
</body>
</html>
