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

// Proceed to checkout
function proceedToCheckout() {
    const cart = getCart();
    
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Check if user is logged in
    const isLoggedIn = document.querySelector('.user-menu') !== null;
    
    if (!isLoggedIn) {
        if (confirm('Please login to proceed with checkout. Redirect to login page?')) {
            window.location.href = '../login_register/purdex.php';
        }
        return;
    }
    
    // Show checkout modal
    showCheckoutModal();
}

// Show checkout modal
function showCheckoutModal() {
    const { subtotal, tax, total } = calculateTotals();
    
    const modal = document.createElement('div');
    modal.className = 'checkout-modal';
    modal.innerHTML = `
        <div class="checkout-modal-content">
            <span class="close-checkout" onclick="this.closest('.checkout-modal').remove()">&times;</span>
            <h2><i class="fa fa-shopping-bag"></i> Checkout</h2>
            
            <div class="checkout-summary">
                <h3>Order Summary</h3>
                <p><strong>Subtotal:</strong> $${subtotal.toFixed(2)}</p>
                <p><strong>Tax (6% VAT):</strong> $${tax.toFixed(2)}</p>
                <p class="brand-accent" style="font-size: 20px;"><strong>Total:</strong> $${total.toFixed(2)}</p>
            </div>
            
            <form id="checkoutForm" onsubmit="submitOrder(event)">
                <h3>Shipping Information</h3>
                
                <div class="form-group">
                    <label>Full Name <span style="color: red;">*</span></label>
                    <input type="text" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number <span style="color: red;">*</span></label>
                    <input type="tel" name="phone" placeholder="09xxxxxxxxx" required>
                </div>
                
                <div class="form-group">
                    <label>Complete Address <span style="color: red;">*</span></label>
                    <textarea name="address" rows="3" placeholder="House No., Street, Barangay" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>City <span style="color: red;">*</span></label>
                    <input type="text" name="city" value="Cagayan de Oro City" required>
                </div>
                
                <h3>Payment Method</h3>
                
                <div class="payment-options">
                    <label class="payment-card">
                        <input type="radio" name="payment" value="cod" checked>
                        <div class="content">
                            <div class="title"><i class="fa fa-hand-holding-dollar"></i> Cash on Delivery</div>
                            <div class="desc">Pay when the item arrives</div>
                        </div>
                    </label>
                    <label class="payment-card gcash">
                        <input type="radio" name="payment" value="gcash">
                        <div class="content">
                            <div class="title"><i class="fa fa-wallet"></i> GCash</div>
                            <div class="desc">Pay via mobile wallet</div>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Order Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Any special instructions?"></textarea>
                </div>
                
                <button type="submit" class="place-order-btn">
                    <i class="fa fa-check-circle"></i> Place Order
                </button>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// Submit order
function submitOrder(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const cart = getCart();
    const { subtotal, tax, total } = calculateTotals();
    
    const orderData = {
        items: cart,
        shipping: {
            fullname: formData.get('fullname'),
            phone: formData.get('phone'),
            address: formData.get('address'),
            city: formData.get('city')
        },
        payment: formData.get('payment'),
        notes: formData.get('notes'),
        subtotal: subtotal,
        tax: tax,
        total: total,
        date: new Date().toISOString()
    };
    
    // Save order to sessionStorage (in real app, send to server)
    const orders = JSON.parse(sessionStorage.getItem('purrfectOrders') || '[]');
    orders.push(orderData);
    sessionStorage.setItem('purrfectOrders', JSON.stringify(orders));
    
    // Clear cart
    sessionStorage.removeItem('purrfectCart');
    updateCartBadge();
    
    // Show success message
    document.querySelector('.checkout-modal').innerHTML = `
        <div class="checkout-modal-content" style="text-align: center; padding: 40px;">
            <i class="fa fa-check-circle" style="font-size: 64px; color: #4caf50;"></i>
            <h2 style="margin-top: 20px;">Order Placed Successfully!</h2>
            <p style="color: #aaa;">Thank you for your order. We'll contact you shortly.</p>
            <p><strong>Order Total: $${total.toFixed(2)}</strong></p>
            <button onclick="window.location.href='product.php'" class="btn-primary" style="margin: 20px 10px;">
                Go Shopping Now
            </button>
            <button onclick="window.location.href='cart.php'" class="btn-secondary" style="margin: 20px 10px;">
                View Cart
            </button>
        </div>
    `;
}

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
@keyframes slideIn {
    from { transform: translateX(400px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(400px); opacity: 0; }
}
.checkout-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.checkout-modal-content {
    background: #1a1a1a;
    padding: 30px;
    border-radius: 10px;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    color: #fff;
}
.close-checkout {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    cursor: pointer;
    color: #aaa;
}
.close-checkout:hover {
    color: #fff;
}
.checkout-summary {
    background: #2a2a2a;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    color: var(--brand-strong);
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #444;
    border-radius: 8px;
    background: #2a2a2a;
    color: #fff;
    outline: none;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--brand-strong);
    box-shadow: 0 0 0 3px rgba(92,191,239,0.25);
}
.place-order-btn {
    width: 100%;
    padding: 15px;
    background: var(--brand-strong);
    color: #003044;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 20px;
}
.place-order-btn:hover {
    background: #3fb2ea;
}
.checkout-btn{
    width:100%;
    padding:15px;
    background:var(--brand-strong);
    color:#003044;
    border:none;
    border-radius:8px;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    margin-top:20px;
}
.checkout-btn:hover{background:#3fb2ea;}
.btn-primary{display:inline-block;padding:10px 20px;background:var(--brand-strong);color:#003044;border:none;border-radius:8px;text-decoration:none;font-weight:700}
.btn-primary:hover{background:#3fb2ea;color:#002333}
.btn-secondary{display:inline-block;padding:10px 20px;background:#333;color:#fff;border:none;border-radius:8px;text-decoration:none;font-weight:700}
.checkout-bar{position:fixed;left:0;right:0;bottom:0;background:#ffffffd9;backdrop-filter:saturate(180%) blur(6px);box-shadow:0 -4px 16px rgba(0,0,0,0.08);z-index:9998;padding:10px 16px}
.checkout-bar-inner{max-width:900px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;gap:12px}
.checkout-bar-total span{color:#666;margin-right:6px}
.checkout-bar-total strong{font-size:18px}
 .brand-accent{color:var(--brand-strong);} 
.payment-options{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;margin-bottom:8px}
.payment-card{cursor:pointer;display:block;position:relative}
.payment-card input{position:absolute;opacity:0;pointer-events:none}
.payment-card .content{border:2px solid #2e4450;background:rgba(155,216,247,0.05);padding:12px;border-radius:12px;transition:all .2s ease;color:#e9f7ff}
.payment-card .title{font-weight:700;color:var(--brand-strong);display:flex;gap:8px;align-items:center}
.payment-card .desc{font-size:12px;color:#a9c6d6;margin-top:4px}
.payment-card input:checked + .content{border-color:var(--brand-strong);background:rgba(92,191,239,0.15)}
.payment-card.gcash .title i{color:var(--brand-strong)}
`;
document.head.appendChild(style);
