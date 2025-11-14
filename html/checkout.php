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
  <link rel="stylesheet" href="css/address-modal.css">
  <style>
    :root{--brand:#9bd8f7;--brand-strong:#5cbfef;--brand-text:#003a57}
    body{background:#f5f5f5}
    .container{max-width:1000px;margin:24px auto;padding:0 16px}
    .checkout-grid{display:grid;grid-template-columns:1fr;gap:16px}
    .card{background:#fff;border:1px solid #e5e5e5;border-radius:8px}
    .card h3{margin:0 0 8px 0}
    .section-title{font-size:22px; font-weight:800; color:#222}
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
    .order-head{display:flex;align-items:center;justify-content:space-between;padding:6px 14px 0 14px;color:#6b8897}
    .order-head .left-label{flex:1;min-width:0;font-weight:800;color:#000}
    .order-cols{display:flex;gap:40px;justify-content:flex-end;color:#6b8897}
    .order-cols .col{width:120px;text-align:right}
    .order-cols.values{color:#333}
    .summary table{width:100%;border-collapse:collapse}
    .summary td{padding:8px 0}
    .summary tr:last-child td{font-weight:700;border-top:1px solid #eee}
    .place-order{padding:12px 22px;background:var(--brand-strong);color:#003044;border:none;border-radius:6px;font-weight:700;cursor:pointer}
    .place-order:hover{background:#3fb2ea}
    .pm-header{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px dashed #e9eef2}
    .pm-title{font-weight:700;color:#003a57}
    .pm-meta{display:flex;gap:16px;align-items:center;color:#6b8897}
    .pm-change{color:#0b65c2;text-decoration:none;font-weight:500;font-size:16px;}
    .pm-change:hover{text-decoration:underline}
    .pm-body{background:#fffdf8;padding:16px 14px}
    .pm-row{display:flex;align-items:center;justify-content:space-between;padding:6px 0;color:#555}
    .pm-row.total{border-top:1px solid #eee;margin-top:8px;padding-top:12px;font-weight:800}
    .pm-row.total .val{color:#ff3e1f;font-size:20px}
    .pm-footer{display:flex;justify-content:flex-end;padding:14px}
    /* Payment method chooser */
    .pm-modal{position:fixed;inset:0;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;z-index:10000}
    .pm-panel{background:#fff;border:1px solid #e8eef3;border-radius:14px;min-width:420px;max-width:90vw;box-shadow:0 18px 40px rgba(2,8,23,.18);transform:translateY(8px);opacity:0;transition:transform .22s ease,opacity .22s ease}
    .pm-modal.show .pm-panel{transform:translateY(0);opacity:1}
    .pm-panel .hd{padding:14px 16px;border-bottom:1px solid #eef3f6;font-weight:800;color:#003a57;background:linear-gradient(180deg,#fbfdff,transparent)}
    .pm-tabs{display:flex;gap:8px;padding:12px 14px;border-bottom:1px solid #f0f3f7;flex-wrap:wrap;background:#f7fafc;border-top-left-radius:14px;border-top-right-radius:14px}
    .pm-tab{padding:10px 14px;border:1px solid #d9e2ec;border-radius:999px;background:#fff;color:#2c3e50;cursor:pointer;font-weight:700;transition:all .18s ease;box-shadow:inset 0 0 0 0 rgba(92,191,239,.0)}
    .pm-tab:hover{border-color:#bcd3e1;box-shadow:inset 0 0 0 1px rgba(92,191,239,.35)}
    .pm-tab.active{border-color:#5cbfef;background:#eaf6ff;color:#003a57;box-shadow:inset 0 0 0 1px #5cbfef}
    .pm-desc{padding:16px 16px;color:#1f2d3a}
    .pm-actions{display:flex;justify-content:flex-end;gap:10px;padding:12px 14px;border-top:1px solid #eef3f6;background:#fbfdff;border-bottom-left-radius:14px;border-bottom-right-radius:14px}
    .btn-plain{background:none;border:none;color:#1a73e8;cursor:pointer;font-weight:700;padding:8px 10px}
    .btn-plain:hover{color:#0b65c2;text-decoration:underline}
    .btn-accent{background:var(--brand-strong);color:#003044;border:none;padding:10px 16px;border-radius:8px;font-weight:800;cursor:pointer;box-shadow:0 6px 14px rgba(92,191,239,.35);transition:transform .15s ease,box-shadow .15s ease}
    .btn-accent:hover{transform:translateY(-1px);box-shadow:0 10px 18px rgba(92,191,239,.4)}
    .btn-plain:focus,.btn-accent:focus,.pm-tab:focus{outline:3px solid rgba(92,191,239,.35);outline-offset:2px}
    @media(max-width:520px){.pm-panel{min-width:0;width:94vw}}
    @media(max-width: 880px){.checkout-grid{grid-template-columns:1fr}}
    /* Address modal styles are shared via css/address-modal.css */
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
    <!-- User dropdown OR Login -->
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

  <!-- Address Picker Modal (shared profile style) -->
  <div id="addrPicker" class="ab-modal" style="display:none"></div>
        </div>
    <?php else: ?>
        <a href="../login_register/purdex.php"><button><i class="fa-solid fa-cat"></i> Login</button></a>
    <?php endif; ?>

    <!-- Cart icon -->
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
          <div class="body"></div>
          <div id="orderItems"></div>
        </div>
        <div class="card" style="margin-top:16px;">
          <div class="pm-header">
            <div class="pm-title">Payment Method</div>
            <div class="pm-meta">
              <span id="pmCurrentMethod">Cash on Delivery</span>
              <a href="#" id="pmChange" class="pm-change">Change</a>
            </div>
          </div>
          <div class="pm-body">
            <div class="pm-row total">
              <span>Total Payment:</span>
              <span class="val" id="pmTotalPayment">₱0.00</span>
            </div>
          </div>
          <div class="pm-footer">
            <button id="placeOrder" class="place-order"><i class="fa fa-check-circle"></i> Place Order</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Payment method modal -->
    <div id="pmModal" class="pm-modal" aria-hidden="true">
      <div class="pm-panel" role="dialog" aria-modal="true">
        <div class="hd">Payment Method</div>
        <div class="pm-tabs">
          <button type="button" class="pm-tab active" data-method="Cash on Delivery">Cash on Delivery</button>
          <button type="button" class="pm-tab" data-method="GCash">GCash</button>
        </div>
        <div class="pm-desc" id="pmDesc">Cash on Delivery</div>
        <div class="pm-actions">
          <button type="button" class="btn-plain" id="pmCancel">Cancel</button>
          <button type="button" class="btn-accent" id="pmApply">Apply</button>
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

  <script>
    // Namespace orders per logged-in user so browser sessions don't mix
    window.PURR_USER_ID = <?= json_encode((string)($_SESSION['user_id'] ?? 'anon')) ?>;
  </script>
  <script src="../js/cart.js?v=no-tax"></script>
  <script src="js/address-modal.js"></script>
  <script>
    const ORDERS_KEY = () => `purrfectOrders:${window.PURR_USER_ID||'anon'}`;
    const SELECTED_ADDR_KEY = () => `purrfectSelectedAddr:${window.PURR_USER_ID||'anon'}`;
    async function fetchAddresses(){
      try{ const r = await fetch('../profile_php/addresses.php?action=list'); return await r.json(); }catch(e){ return []; }
    }
    async function fetchDefaultAddress(){
      const items = await fetchAddresses();
      if(!Array.isArray(items) || !items.length) return null;
      const sel = sessionStorage.getItem(SELECTED_ADDR_KEY());
      if(sel){ const hit = items.find(a=>String(a.address_id)===String(sel)); if(hit) return hit; }
      return items.find(a => String(a.is_default)==='1') || items[0] || null;
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
          <a class="da-change" href="#" id="changeAddrLink">Change</a>
        </div>`;
      const change = document.getElementById('changeAddrLink');
      if(change){ change.addEventListener('click', (e)=>{ e.preventDefault(); openAddressPicker(); }); }
    }

    async function openAddressPicker(){
      const modal = document.getElementById('addrPicker');
      const list = await fetchAddresses();
      const current = await fetchDefaultAddress();
      if(!Array.isArray(list) || !list.length){
        modal.style.display='flex';
        modal.innerHTML = `<div class="panel">
          <h3>My Address</h3>
          <div style="padding:16px">No saved addresses. <a class="addr-edit" href="../profile_php/profile.php#addresses">Add New Address</a></div>
          <div class="addr-f"><button class="btn-plain" onclick="closeAddrPicker()">Close</button></div>
        </div>`;
        return;
      }
      modal.style.display='flex';
      modal.innerHTML = `
        <div class="panel">
          <h3>My Address</h3>
          <div class="addr-list">
            ${list.map(a=>`
              <label class="addr-item">
                <input type="radio" name="addr" value="${a.address_id}" ${current&&String(current.address_id)===String(a.address_id)?'checked':''} style="margin-top:4px">
                <div class="addr-main">
                  <div><span class="addr-name">${esc(a.fullname)}</span> <span class="addr-phone">(+63) ${esc(a.phone)}</span> ${String(a.is_default)==='1'?'<span class="addr-default">Default</span>':''}
                    <a class="addr-edit" href="../profile_php/profile.php#addresses" target="_blank" style="float:right">Edit</a>
                  </div>
                  <div class="addr-lines">${esc(a.address_line)}${a.barangay?', '+esc(a.barangay):''}, ${esc(a.city)}${a.province?', '+esc(a.province):''} ${a.postal_code?esc(a.postal_code):''}</div>
                </div>
              </label>
            `).join('')}
            <div style="padding:12px 16px"><a class="add-outline" href="#" onclick="openAddrForm();return false;">+ <span>Add New Address</span></a></div>
          </div>
          <div class="addr-f">
            <button class="link-cancel" onclick="closeAddrPicker()">Cancel</button>
            <button class="btn-accent" onclick="confirmAddrPicker()">Confirm</button>
          </div>
        </div>`;
    }
    function closeAddrPicker(){ const m=document.getElementById('addrPicker'); m.style.display='none'; m.innerHTML=''; }
    function confirmAddrPicker(){
      const m=document.getElementById('addrPicker');
      const sel = m.querySelector('input[name="addr"]:checked');
      if(sel){ sessionStorage.setItem(SELECTED_ADDR_KEY(), String(sel.value)); }
      closeAddrPicker();
      renderAddress();
    }

    function openAddrForm(){
      const modal = document.getElementById('addrPicker');
      modal.style.display='flex';
      modal.innerHTML = `
        <div class="panel">
          <h3>New Address</h3>
          <form id="addrForm" class="addr-form">
            <div class="grid">
              <div>
                <input name="fullname" type="text" required placeholder="Full Name" pattern="^[A-Za-z\\s\\-\.'\\u00C0-\\u024F]+$" title="Letters only" oninput="this.value=this.value.replace(/[^A-Za-z\\s\\-\.'\u00C0-\u024F]/g,'')">
              </div>
              <div>
                <input name="phone" type="tel" required placeholder="09123456789" inputmode="numeric" maxlength="11" pattern="^09\\d{9}$" title="Enter PH mobile starting with 09 (11 digits)" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
              </div>
              <div class="full">
                <div class="ph-picker" id="phPicker">
                  <input id="ab_region" placeholder="Region, Province, City, Barangay" readonly>
                  <button type="button" class="ph-toggle">▾</button>
                  <div class="ph-panel" hidden>
                    <div class="ph-tabs">
                      <button type="button" data-tab="region" class="active">Region</button>
                      <button type="button" data-tab="province" disabled>Province</button>
                      <button type="button" data-tab="city" disabled>City</button>
                      <button type="button" data-tab="barangay" disabled>Barangay</button>
                    </div>
                    <div class="ph-list" id="phList"></div>
                  </div>
                </div>
                <input type="hidden" name="region" id="ab_hidden_region" value="">
                <input type="hidden" name="province" id="ab_hidden_province" value="">
                <input type="hidden" name="city" id="ab_hidden_city" value="">
                <input type="hidden" name="barangay" id="ab_hidden_barangay" value="">
              </div>
              <div class="full">
                <input name="postal_code" placeholder="Postal Code">
              </div>
              <div class="full">
                <input name="address_line" required placeholder="Street Name, Building, House No.">
              </div>
              <div class="full">
                <label>Label As</label>
                <div class="label-pills" id="ab_labels">
                  <button type="button" class="label-pill active" data-value="Home">Home</button>
                  <button type="button" class="label-pill" data-value="Work">Work</button>
                </div>
                <input type="hidden" name="label" value="Home">
              </div>
              <div class="full" style="margin-top:6px;">
                <div class="checkline">
                  <input type="checkbox" name="is_default" value="1"> <span>Set as Default Address</span>
                </div>
              </div>
            </div>
            <footer class="ab-actions">
              <button type="button" class="link-cancel" onclick="openAddressPicker()">Cancel</button>
              <button type="submit" class="btn-accent">Submit</button>
            </footer>
          </form>
        </div>`;
      const form = document.getElementById('addrForm');
      if (window.AddressModal){ AddressModal.wireLabelPills(modal); AddressModal.initPhPicker(modal); }
      // Autofill phone from session if available; normalize to 09XXXXXXXXX
      try{
        const phField = form.querySelector('input[name="phone"]');
        const sessionPhone = <?= json_encode($_SESSION['phone'] ?? '') ?>;
        if (phField && sessionPhone){
          let norm = String(sessionPhone).replace(/[^0-9]/g,'');
          // Convert +639XXXXXXXXX to 09XXXXXXXXX if needed
          if (norm.startsWith('639') && norm.length >= 12) norm = '0' + norm.slice(2);
          if (norm.startsWith('9') && norm.length === 10) norm = '0' + norm; // 9XXXXXXXXX -> 09XXXXXXXXX
          norm = norm.slice(0,11);
          if (/^09\d{9}$/.test(norm)) phField.value = norm;
        }
      }catch(_){}
      form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const fd = new FormData(form);
        fd.append('action','create');
        // Ensure picker-selected values are included
        fd.set('region', document.getElementById('ab_hidden_region')?.value || '');
        fd.set('province', document.getElementById('ab_hidden_province')?.value || '');
        fd.set('city', document.getElementById('ab_hidden_city')?.value || '');
        fd.set('barangay', document.getElementById('ab_hidden_barangay')?.value || '');
        if (fd.get('is_default')) fd.set('is_default','1'); else fd.set('is_default','0');
        try{
          const r = await fetch('../profile_php/addresses.php', { method:'POST', body: fd });
          const data = await r.json();
          if(data && data.address_id){
            sessionStorage.setItem(SELECTED_ADDR_KEY(), String(data.address_id));
            // Go back to list and refresh
            await openAddressPicker();
          }else if(data && data.ok){
            await openAddressPicker();
          }else{
            alert('Failed to save address');
          }
        }catch(err){ alert('Failed to save address'); }
      });
    }

    function updatePaymentPanel(items){
      const sums = calculateTotals(items);
      const totalPay = sums.total; // shipping currently 0
      const el = document.getElementById('pmTotalPayment');
      if (el) el.textContent = `₱${totalPay.toFixed(2)}`;
    }

    function renderCartToCheckout(){
      const all = getCart();
      const selected = (()=>{ try { return JSON.parse(sessionStorage.getItem(SELECTED_KEY())||'[]'); } catch(e){ return []; } })();
      const items = selected.length ? all.filter(it => selected.includes(it.name)) : all;
      const wrap = document.getElementById('orderItems');
      if(!items || items.length===0){
        wrap.innerHTML = '<div class="body">Your cart is empty. <a href="product.php">Go shopping</a>.</div>';
        return;
      }

      // Header row: left label + right-side columns
      wrap.innerHTML = `
        <div class="order-head">
          <span class="left-label">Products Ordered</span>
          <div class="order-cols">
            <span class="col">Unit Price</span>
            <span class="col">Quantity</span>
            <span class="col">Item Subtotal</span>
          </div>
        </div>
      ` + items.map(it=>`
        <div class="shop-row">
          <img src="${it.image}" alt="${esc(it.name)}" onerror="this.src='images/catbed.jpg'">
          <div style="flex:1;min-width:0;">
            <div class="title">${esc(it.name)}</div>
          </div>
          <div class="order-cols values">
            <span class="col">₱${Number(it.price).toFixed(2)}</span>
            <span class="col">${it.quantity}</span>
            <span class="col">₱${(it.price*it.quantity).toFixed(2)}</span>
          </div>
        </div>
      `).join('');

      updatePaymentPanel(items);
    }

    document.getElementById('placeOrder').addEventListener('click', async function(){
      const all = getCart();
      const selected = (()=>{ try { return JSON.parse(sessionStorage.getItem(SELECTED_KEY())||'[]'); } catch(e){ return []; } })();
      const items = selected.length ? all.filter(it => selected.includes(it.name)) : all;
      if(!items || items.length===0){ alert('Your cart is empty'); return; }
      const sums = calculateTotals(items);
      const orders = JSON.parse(sessionStorage.getItem(ORDERS_KEY()) || '[]');
      const record = { items, total: sums.total, date: new Date().toISOString(), status: 'to_pay' };
      orders.push(record);
      sessionStorage.setItem(ORDERS_KEY(), JSON.stringify(orders));
      // Also persist to server for admin panel; use keepalive so it survives redirect
      try {
        const resp = await fetch('../crud/crud.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'create_order', items, total: sums.total, user_id: window.PURR_USER_ID }),
          keepalive: true,
          credentials: 'same-origin'
        });
        try {
          const data = await resp.json();
          if (data?.success && data?.order_id){
            // Save the created order_id back to the same record in sessionStorage
            const arr = JSON.parse(sessionStorage.getItem(ORDERS_KEY())||'[]');
            const idx = arr.findIndex(o => o.date === record.date);
            if (idx > -1){ arr[idx].order_id = data.order_id; sessionStorage.setItem(ORDERS_KEY(), JSON.stringify(arr)); }
          } else {
            console.warn('Server create_order response:', data);
          }
        } catch (_) {}
      } catch (e) { console.warn('create_order failed', e); }
      // Remove only purchased items from cart if a subset was selected
      if (selected.length){
        const remaining = all.filter(it => !selected.includes(it.name));
        sessionStorage.setItem(CART_KEY(), JSON.stringify(remaining));
        sessionStorage.removeItem(SELECTED_KEY());
      } else {
        sessionStorage.removeItem(CART_KEY());
      }
      if (typeof updateCartBadge === 'function') updateCartBadge();
      window.location.href = '../profile_php/profile.php#purchases:to_pay';
    });

    // Payment method chooser logic
    window.PAYMENT_METHOD = 'Cash on Delivery';
    const pmModal = document.getElementById('pmModal');
    const pmChange = document.getElementById('pmChange');
    const pmCurrent = document.getElementById('pmCurrentMethod');
    const pmDesc = document.getElementById('pmDesc');
    const pmCancel = document.getElementById('pmCancel');
    const pmApply = document.getElementById('pmApply');
    function openPm(){
      pmModal.style.display='flex';
      requestAnimationFrame(()=> pmModal.classList.add('show'));
      const firstTab = document.querySelector('.pm-tab');
      if(firstTab) firstTab.focus();
      document.addEventListener('keydown', onPmKey);
    }
    function closePm(){
      pmModal.classList.remove('show');
      setTimeout(()=>{ pmModal.style.display='none'; }, 180);
      document.removeEventListener('keydown', onPmKey);
    }
    function onPmKey(e){ if(e.key==='Escape') closePm(); }
    function setActive(btn){
      document.querySelectorAll('.pm-tab').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      pmDesc.textContent = btn.getAttribute('data-method');
    }
    if (pmChange){ pmChange.addEventListener('click', (e)=>{ e.preventDefault(); openPm(); }); }
    pmModal.addEventListener('click', (e)=>{ if (e.target===pmModal) closePm(); });
    document.querySelectorAll('.pm-tab').forEach(btn=>{
      btn.addEventListener('click', ()=> setActive(btn));
    });
    if (pmCancel) pmCancel.onclick = closePm;
    if (pmApply) pmApply.onclick = ()=>{
      const active = document.querySelector('.pm-tab.active');
      const method = active ? active.getAttribute('data-method') : 'Cash on Delivery';
      window.PAYMENT_METHOD = method;
      if (pmCurrent) pmCurrent.textContent = method;
      closePm();
    };
    // Initialize address and cart on first load
    document.addEventListener('DOMContentLoaded', () => {
      try { renderAddress(); } catch(_) {}
      try { renderCartToCheckout(); } catch(_) {}
    });
  </script>
</body>
</html>
