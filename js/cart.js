// Shopping Cart Functionality
const TAX_RATE = 0.06; // 6% VAT

// Get cart from sessionStorage
function getCart() {
    const cart = sessionStorage.getItem('purrfectCart');
    return cart ? JSON.parse(cart) : [];
}

// Save cart to sessionStorage
function saveCart(cart) {
    sessionStorage.setItem('purrfectCart', JSON.stringify(cart));
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

// Calculate totals
function calculateTotals() {
    const cart = getCart();
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * TAX_RATE;
    const total = subtotal + tax;
    
    return { subtotal, tax, total };
}

// Render cart page
function renderCart() {
    const cart = getCart();
    const cartTable = document.getElementById('cartItems');
    const totalsDiv = document.getElementById('cartTotals');
    
    if (!cartTable) return;
    
    if (cart.length === 0) {
        cartTable.innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 40px;">
                    <i class="fa fa-shopping-cart" style="font-size: 48px; color: #aaa;"></i>
                    <p style="color: #aaa; margin-top: 20px;">Your cart is empty</p>
                    <a href="product.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">Go Shopping Now</a>
                </td>
            </tr>
        `;
        totalsDiv.innerHTML = '';
        return;
    }
    
    // Render cart items (make image and name clickable to product details)
    cartTable.innerHTML = cart.map(item => `
        <tr data-product="${escapeHtml(item.name)}">
            <td>
                <div class="cart-info">
                    <a class="cart-link" href="product-detail.php?name=${encodeURIComponent(item.name)}" title="View ${escapeHtml(item.name)}">
                        <img src="${item.image}" alt="${escapeHtml(item.name)}" onerror="this.src='images/catbed.jpg'">
                    </a>
                    <div>
                        <a class="cart-link" href="product-detail.php?name=${encodeURIComponent(item.name)}" style="text-decoration: none; color: inherit;">
                            <p>${escapeHtml(item.name)}</p>
                        </a>
                        <small>Price: $${item.price.toFixed(2)}</small>
                        <br>
                        <a href="#" onclick="removeFromCart('${escapeHtml(item.name)}'); return false;" style="color: #ff6b6b;">Remove</a>
                    </div>
                </div>
            </td>
            <td>
                <input type="number" 
                       value="${item.quantity}" 
                       min="1" 
                       onchange="updateQuantity('${escapeHtml(item.name)}', this.value)"
                       style="width: 60px; padding: 5px;">
            </td>
            <td class="item-subtotal">$${(item.price * item.quantity).toFixed(2)}</td>
        </tr>
    `).join('');
    
    // Calculate and render totals
    const { subtotal, tax, total } = calculateTotals();
    
    totalsDiv.innerHTML = `
        <table>
            <tr>
                <td>Subtotal</td>
                <td>$${subtotal.toFixed(2)}</td>
            </tr>
            <tr>
                <td>Tax (6% VAT)</td>
                <td>$${tax.toFixed(2)}</td>
            </tr>
            <tr style="font-weight: bold; font-size: 18px;">
                <td>Total</td>
                <td>$${total.toFixed(2)}</td>
            </tr>
        </table>
    `;
    
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

// Sticky checkout bar (Shopee-like)
function updateCheckoutBar() {
    const bar = document.getElementById('checkoutBar');
    if (!bar) return;

    const cart = getCart();
    if (cart.length === 0) {
        bar.style.display = 'none';
        bar.innerHTML = '';
        return;
    }

    const { total } = calculateTotals();
    bar.style.display = 'block';
    bar.innerHTML = `
        <div class="checkout-bar-inner">
            <div class="checkout-bar-total"><span>Total:</span> <strong>$${total.toFixed(2)}</strong></div>
            <button class="checkout-btn" onclick="proceedToCheckout()">Checkout</button>
        </div>
    `;
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
.checkout-bar-inner{max-width:900px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:12px}
.checkout-bar-total span{color:#666;margin-right:6px}
.checkout-bar-total strong{font-size:18px}
.brand-accent{color:var(--brand-strong);}
`;
document.head.appendChild(style);
