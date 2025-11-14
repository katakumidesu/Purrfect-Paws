// Shopping Cart Functionality
const TAX_RATE = 0; // tax disabled

// Namespace storage per logged-in user so carts don't mix between accounts
function USER_ID(){
    return (typeof window !== 'undefined' && window.PURR_USER_ID && String(window.PURR_USER_ID) !== 'undefined')
        ? String(window.PURR_USER_ID)
        : 'anon';
}
const CART_KEY = () => `purrfectCart:${USER_ID()}`;
const SELECTED_KEY = () => `purrfectSelected:${USER_ID()}`;

// One-time cleanup: remove legacy global keys to prevent cross-user mixing
try {
    if (sessionStorage.getItem('purrfectCart')) sessionStorage.removeItem('purrfectCart');
    if (sessionStorage.getItem('purrfectSelected')) sessionStorage.removeItem('purrfectSelected');
} catch (e) {}

// Get cart from sessionStorage
function getCart() {
    const cart = sessionStorage.getItem(CART_KEY());
    return cart ? JSON.parse(cart) : [];
}

// Save cart to sessionStorage
function saveCart(cart) {
    sessionStorage.setItem(CART_KEY(), JSON.stringify(cart));
    updateCartBadge();
}

// Add item to cart (supports optional quantity)
function addToCart(product, qty = 1) {
    // Check if user is logged in by checking for user menu
    const isLoggedIn = document.querySelector('.user-menu') !== null;
    
    if (!isLoggedIn) {
        window.location.href = '../login_register/purdex.php';
        return;
    }
    
    const cart = getCart();
    const existingItem = cart.find(item => item.name === product.name);

    const quantityToAdd = Math.max(1, parseInt(qty || 1, 10));
    if (existingItem) {
        existingItem.quantity += quantityToAdd;
    } else {
        cart.push({
            name: product.name,
            price: parseFloat(product.price),
            image: product.image || 'images/catbed.jpg',
            quantity: quantityToAdd
        });
    }

    saveCart(cart);
    showNotification(`${product.name} added to cart!`);
}

// Remove item from cart
function removeFromCart(productName) {
    let cart = getCart();
    cart = cart.filter(item => item.name !== productName);
    saveCart(cart);
    renderCart();
}

// Update quantity
function updateQuantity(productName, quantity) {
    const cart = getCart();
    const item = cart.find(item => item.name === productName);
    
    if (item) {
        item.quantity = Math.max(1, parseInt(quantity));
        saveCart(cart);
        renderCart();
    }
}

// Calculate totals (optionally for a provided items array)
function calculateTotals(items) {
    const data = Array.isArray(items) ? items : getCart();
    const subtotal = data.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    // Tax disabled
    const tax = 0;
    const total = subtotal;
    return { subtotal, tax, total };
}

// Helpers for selection
function getSelectedNames(){
    try{ return JSON.parse(sessionStorage.getItem(SELECTED_KEY()) || '[]'); }catch(e){ return []; }
}
function setSelectedNames(arr){ sessionStorage.setItem(SELECTED_KEY(), JSON.stringify(arr||[])); }
function selectedItemsOnly(){
    const selected = getSelectedNames();
    if (selected.length === 0) return [];
    const set = new Set(selected);
    return getCart().filter(it => set.has(it.name));
}

// Render cart page
function renderCart() {
    const cart = getCart();
    const cartTable = document.getElementById('cartItems');
    const totalsDiv = document.getElementById('cartTotals');
    const selected = new Set(getSelectedNames());
    
    if (!cartTable) return;
    
    if (cart.length === 0) {
        cartTable.innerHTML = `
            <tr>
                <td colspan=\"6\" style=\"text-align: center; padding: 40px;\">
                    <i class=\"fa fa-shopping-cart\" style=\"font-size: 48px; color: #aaa;\"></i>
                    <p style=\"color: #aaa; margin-top: 20px;\">Your cart is empty</p>
                    <a href=\"product.php\" class=\"btn-primary\" style=\"display: inline-block; margin-top: 20px;\">Go Shopping Now</a>
                </td>
            </tr>
        `;
        totalsDiv.innerHTML = '';
        // Hide header and checkout bar immediately when cart is empty
        const hdr = document.getElementById('cartHeader'); if (hdr) hdr.style.display = 'none';
        updateCheckoutBar();
        return;
    }
    // Ensure header is visible when there are items
    { const hdr = document.getElementById('cartHeader'); if (hdr) hdr.style.display = ''; }
    
    // Render cart items with selection checkboxes
    cartTable.innerHTML = cart.map(item => `
        <tr data-product=\"${escapeHtml(item.name)}\">
            <td style=\"text-align:center;\"><input type=\"checkbox\" class=\"item-check\" data-name=\"${escapeHtml(item.name)}\" ${selected.has(item.name)?'checked':''}></td>
            <td>
                <div class=\"cart-info\">
                    <a class=\"cart-link\" href=\"product-detail.php?name=${encodeURIComponent(item.name)}\" title=\"View ${escapeHtml(item.name)}\">\
                        <img src=\"${item.image}\" alt=\"${escapeHtml(item.name)}\" onerror=\"this.src='images/catbed.jpg'\">\
                    </a>
                    <div>
                        <a class=\"cart-link\" href=\"product-detail.php?name=${encodeURIComponent(item.name)}\" style=\"text-decoration: none; color: inherit;\">\
                            <p>${escapeHtml(item.name)}</p>
                        </a>
                        <small>Price: ₱${item.price.toFixed(2)}</small>
                    </div>
                </div>
            </td>
            <td style=\"text-align:center;white-space:nowrap;\">₱${item.price.toFixed(2)}</td>
            <td style=\"text-align:center;\">
                <div class=\"qty-controls\">
                    <button type=\"button\" class=\"qty-dec\" data-name=\"${escapeHtml(item.name)}\">-</button>
                    <input type=\"number\" class=\"qty-input\" value=\"${item.quantity}\" min=\"1\" data-name=\"${escapeHtml(item.name)}\">
                    <button type=\"button\" class=\"qty-inc\" data-name=\"${escapeHtml(item.name)}\">+</button>
                </div>
            </td>
            </td>
            <td class=\"item-subtotal\" style=\"text-align:center;\">₱${(item.price * item.quantity).toFixed(2)}</td>
            <td style=\"text-align:center;\"><button class=\"link-delete\" data-name=\"${escapeHtml(item.name)}\">Delete</button></td>
        </tr>
    `).join('');

    // Wire up qty +/- and delete actions
    cartTable.querySelectorAll('.qty-dec').forEach(btn=>btn.addEventListener('click',()=>{
        const n = btn.getAttribute('data-name');
        const cart = getCart();
        const it = cart.find(i=>i.name===n); if(!it) return; it.quantity = Math.max(1, (it.quantity||1)-1); saveCart(cart); renderCart();
    }));
    cartTable.querySelectorAll('.qty-inc').forEach(btn=>btn.addEventListener('click',()=>{
        const n = btn.getAttribute('data-name');
        const cart = getCart();
        const it = cart.find(i=>i.name===n); if(!it) return; it.quantity = (it.quantity||1)+1; saveCart(cart); renderCart();
    }));
    cartTable.querySelectorAll('.qty-input').forEach(inp=>inp.addEventListener('change',()=>{
        const n = inp.getAttribute('data-name'); updateQuantity(n, inp.value);
    }));
    cartTable.querySelectorAll('.link-delete').forEach(btn=>btn.addEventListener('click',()=>{ removeFromCart(btn.getAttribute('data-name')); }));

    // Wire up checkbox events
    const checks = cartTable.querySelectorAll('.item-check');
    checks.forEach(cb => cb.addEventListener('change', ()=>{
        const name = cb.getAttribute('data-name');
        const names = getSelectedNames();
        const set = new Set(names);
        if (cb.checked) set.add(name); else set.delete(name);
        setSelectedNames(Array.from(set));
        // Sync select-all
        const allChecked = Array.from(cartTable.querySelectorAll('.item-check')).every(x=>x.checked);
        const sa = document.getElementById('checkAll'); if (sa) sa.checked = allChecked;
// Update totals and bar
        const itemsForTotals2 = (getSelectedNames().length? getCart().filter(it=>getSelectedNames().includes(it.name)) : []);
        const t2 = calculateTotals(itemsForTotals2);
        totalsDiv.innerHTML = '';
        updateCheckoutBar();
    }));

    // Master checkbox
    const selectAll = document.getElementById('checkAll');
    if (selectAll){
        const allChecked = Array.from(cartTable.querySelectorAll('.item-check')).every(x=>x.checked);
        selectAll.checked = allChecked && checks.length>0;
        selectAll.onchange = ()=>{
            const names = new Set();
            checks.forEach(cb => { cb.checked = selectAll.checked; if (selectAll.checked) names.add(cb.getAttribute('data-name')); });
            setSelectedNames(Array.from(names));
            updateCheckoutBar();
        };
    }

// Calculate and render totals based on selection
const itemsForTotals = (getSelectedNames().length? getCart().filter(it=>getSelectedNames().includes(it.name)) : []);
    const { total } = calculateTotals(itemsForTotals);
    
    totalsDiv.innerHTML = '';
    
    // Update sticky checkout bar
    updateCheckoutBar();
}

// Update cart badge count
function updateCartBadge() {
    // Check if user is logged in
    const isLoggedIn = document.querySelector('.user-menu') !== null;
    const badges = document.querySelectorAll('.cart-badge');
    
    if (!isLoggedIn) {
        badges.forEach(badge => {
            badge.style.display = 'none';
        });
        return;
    }
    
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    badges.forEach(badge => {
        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    });
}

// Proceed to checkout (full page)
function proceedToCheckout() {
    const cart = getCart();
    if (cart.length === 0) { alert('Your cart is empty!'); return; }

    // Require login
    const isLoggedIn = document.querySelector('.user-menu') !== null;
    if (!isLoggedIn) {
        if (confirm('Please login to proceed with checkout. Redirect to login page?')) {
            window.location.href = '../login_register/purdex.php';
        }
        return;
    }

    // Persist the current selection so checkout page can filter items
    // (If no selection, checkout page will use all items.)
    // Nothing else to do—selection is already stored in sessionStorage.

// Must select at least one item
    if (!getSelectedNames().length){
        alert('Please select at least one item to checkout.');
        return;
    }

    // Go to dedicated checkout page (no modal)
    window.location.href = 'checkout.php';
}

// Fetch the user's default address from the profile API (shared by checkout page)
async function fetchDefaultAddress(){
    try{
        const res = await fetch('../profile_php/addresses.php?action=list');
        const items = await res.json();
        if (!Array.isArray(items)) return null;
        // Prefer the one marked default; otherwise take the first item
        return items.find(a => String(a.is_default) === '1') || items[0] || null;
    }catch(e){ return null; }
}

// The modal-based checkout has been removed in favor of a dedicated page.
// No modal code remains here.


// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.innerHTML = `<i class="fa fa-check-circle"></i> ${message}`;
    notification.style.cssText = 'position: fixed; top: 80px; right: 20px; background: #4caf50; color: white; padding: 15px 20px; border-radius: 5px; z-index: 10000; animation: slideIn 0.3s ease-out;';
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('cartItems')) {
        renderCart();
    }
    updateCartBadge();
});

// Sticky checkout bar 
function updateCheckoutBar() {
    const bar = document.getElementById('checkoutBar');
    if (!bar) return;

    const cart = getCart();
    if (cart.length === 0) {
        bar.style.display = 'none';
        bar.innerHTML = '';
        return;
    }

const items = (getSelectedNames().length? getCart().filter(it=>getSelectedNames().includes(it.name)) : []);
    const { total } = calculateTotals(items);
    const allCount = getCart().length;
    const selectedCount = items.length;
    bar.style.display = 'block';
bar.innerHTML = `
        <div class="checkout-bar-inner">
            <div class="checkout-left">
                <label class="select-all-bottom">
                    <input type="checkbox" id="checkAllBottom" ${selectedCount===allCount && allCount>0 ? 'checked' : ''}>
                    <span>Select All (${allCount})</span>
                </label>
                <button class="link-delete" id="deleteSelected">Delete</button>
            </div>
            <div class="checkout-right" style="display:flex;align-items:center;gap:12px;">
                <div class="checkout-bar-total"><span>Total (${selectedCount} item${selectedCount!==1?'s':''}):</span> <strong>₱${total.toFixed(2)}</strong></div>
                <button class="checkout-btn" ${items.length===0?'disabled':''} onclick="proceedToCheckout()">Check Out</button>
            </div>
        </div>
    `;

    // Bottom select-all behavior
    const bottomAll = document.getElementById('checkAllBottom');
    if (bottomAll){
        bottomAll.onchange = () => {
            const names = new Set();
            document.querySelectorAll('.item-check').forEach(cb => { cb.checked = bottomAll.checked; if (bottomAll.checked) names.add(cb.getAttribute('data-name')); });
            setSelectedNames(Array.from(names));
            renderCart();
        };
    }

    // Delete selected items
    const delSel = document.getElementById('deleteSelected');
    if (delSel){
        delSel.onclick = () => {
            const sel = getSelectedNames();
            if (!sel || sel.length===0) return;
            const remaining = getCart().filter(it => !sel.includes(it.name));
            saveCart(remaining);
            setSelectedNames([]);
            renderCart();
        };
    }
}

// Add CSS for animations and components
const style = document.createElement('style');
style.textContent = `
:root{--brand:#9bd8f7;--brand-strong:#5cbfef;--brand-text:#003a57;}
@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }
.btn-primary{display:inline-block;padding:10px 20px;background:var(--brand-strong);color:#003044;border:none;border-radius:8px;text-decoration:none;font-weight:700}
.btn-primary:hover{background:#3fb2ea;color:#002333}
.btn-secondary{display:inline-block;padding:10px 20px;background:#333;color:#fff;border:none;border-radius:8px;text-decoration:none;font-weight:700}
.checkout-bar{position:fixed;left:0;right:0;bottom:0;background:#ffffffd9;backdrop-filter:saturate(180%) blur(6px);box-shadow:0 -4px 16px rgba(0,0,0,0.08);z-index:9998;padding:10px 16px}
.checkout-bar-inner{max-width:1000px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:12px}
.checkout-bar-total span{color:#666;margin-right:6px}
.checkout-bar-total strong{font-size:18px}
.brand-accent{color:var(--brand-strong);} 
.checkout-btn[disabled]{opacity:.6;cursor:not-allowed}
/* Qty controls */
.qty-controls{display:inline-flex;align-items:center;gap:6px}
.qty-controls .qty-dec,.qty-controls .qty-inc{width:28px;height:28px;border:1px solid #d8e5ec;background:#fff;color:#333;border-radius:4px;cursor:pointer}
.qty-controls .qty-input{width:54px;text-align:center;border:1px solid #d8e5ec;border-radius:4px;padding:4px}
/* Checkbox sizing */
.item-check,#checkAll{width:18px;height:18px}
/* Delete link sizing */
.link-delete{background:none;border:none;color:#1a73e8;cursor:pointer;font-size:14px;padding:0;line-height:1}
.link-delete:hover{text-decoration:underline}
.select-all-bottom{display:inline-flex;align-items:center;gap:8px;margin-right:12px}
`;
document.head.appendChild(style);
