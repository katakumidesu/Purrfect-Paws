// 🐾 Product List (loaded from database)
let products = [];
const API_URL = '../crud/crud.php';

// Load products from database (in-stock only)
async function loadProducts() {
    try {
        const res = await fetch(`${API_URL}?action=get_products&available_only=1&_ts=${Date.now()}`);
        const data = await res.json();
        if (data && !data.error) {
            // Transform DB products to match expected format; safeguard filter stock > 0
            const inStock = Array.isArray(data) ? data.filter(p => Number(p?.stock) > 0) : [];
            products = inStock.map(p => ({
                name: p.name || '',
                img: p.image_url || 'images/catbed.jpg',
                price: parseFloat(p.price || 0),
                rating: computeRating(p),
                description: p.description || 'No description available.'
            }));
            return products;
        } else {
            console.error('Error loading products:', data.error);
            return [];
        }
    } catch (e) {
        console.error('Error loading products:', e);
        return [];
    }
}

function computeRating(p) {
    // Force all products to display as 5-star rating
    return 5;
}

// ⭐ Generate star icons
function getStars(rating) {
    let stars = "";
    for (let i = 1; i <= 5; i++) {
        if (rating >= i) stars += '<i class="fa-solid fa-star" style="color: gold;"></i>';
        else if (rating >= i - 0.5) stars += '<i class="fa-solid fa-star-half-stroke" style="color: gold;"></i>';
        else stars += '<i class="fa-regular fa-star" style="color: gold;"></i>';
    }
    return stars;
}

// 🐾 Shuffle related products (excluding current one)
function shuffle(array) {
    let currentIndex = array.length, randomIndex;
    while (currentIndex > 0) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex--;
        [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
    }
    return array;
}

// 📦 Display product details
async function displayProductDetails() {
    // Load products from database first
    await loadProducts();
    
    // 🧗 Get current product name from URL
    const params = new URLSearchParams(window.location.search);
    const productName = params.get("name");
    
    // 🕵️ Find product by name
    const product = products.find(p => p.name === productName);
    
    // 📦 Display single product
    const productContainer = document.getElementById("product-details");
    
    if (product) {
        const avgRating = 5;
        productContainer.innerHTML = `
            <div class="product-container single-product">
                <div class="product-image">
                    <img src="${product.img}" alt="${product.name}" onerror="this.src='images/catbed.jpg'">
                </div>
                <div class="product-details">
                    <p>Home / Cat Accessories</p>
                    <h1>${product.name}</h1>
                    <h4>₱ ${product.price.toFixed(2)}</h4>
                    <input type="number" value="1" min="1">
                    <a href="#" class="purchase-btn-2">Add To Cart</a>
                    <h3>Description:</h3>
                    <p>${product.description}</p>
                </div>
            </div>
        `;
        // Wire up "Add To Cart"
        const addBtn = document.querySelector('.purchase-btn-2');
        const qtyInput = document.querySelector('.single-product input');
        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10));
                addToCart({ name: product.name, price: product.price, image: product.img }, qty);
            });
        }
    } else {
        productContainer.innerHTML = `<p>Product not found.</p>`;
    }
    
    // Display related products
    const filteredProducts = products.filter(p => p.name !== productName);
    const relatedContainer = document.getElementById("related-container");
    const shuffled = shuffle([...filteredProducts]).slice(0, 4);
    
    // ⭐ Display related products with clickable product boxes
    relatedContainer.innerHTML = "";
    shuffled.forEach(prod => {
        const div = document.createElement("div");
        div.classList.add("cat2");
        div.innerHTML = `
            <a href="product-detail.php?name=${encodeURIComponent(prod.name)}">
                <img src="${prod.img}" alt="${prod.name}" onerror="this.src='images/catbed.jpg'">
            </a>
            <h4>${prod.name}</h4>
            <div class="rating">
                ${getStars(prod.rating)}
            </div>
            <p><strong>₱ ${prod.price.toFixed(2)}</strong></p>
            <a href="product-detail.php?name=${encodeURIComponent(prod.name)}">
                <button class="purchase-btn">Purchase</button>
            </a>
        `;
        relatedContainer.appendChild(div);
    });

    // Big Product Ratings block under related products (full width of related section)
    if (product) {
        // Read any saved reviews for this product from localStorage (written from profile rating modal)
        let reviews = [];
        try {
            const raw = window.localStorage.getItem('pp_product_reviews');
            const arr = raw ? JSON.parse(raw) : [];
            const key = product.name.toLowerCase().trim();
            if (Array.isArray(arr)) {
                reviews = arr.filter(r => r && (r.key === key || (r.product||'').toLowerCase().trim() === key));
            }
        } catch (e) {
            reviews = [];
        }
        // Compute Shopee-like average rating (win rate) from reviews; fall back to 5 if none yet
        let avgRating = 5;
        if (reviews.length > 0) {
            const sum = reviews.reduce((acc, r) => acc + Number(r.stars || 0), 0);
            avgRating = sum / reviews.length;
        }
        const ratingsSection = document.createElement('section');
        ratingsSection.className = 'product-ratings';
        ratingsSection.style.margin = '24px auto 0';
        ratingsSection.style.background = '#fff7ed';
        ratingsSection.style.border = '1px solid #fed7aa';
        ratingsSection.style.borderRadius = '10px';
        ratingsSection.style.padding = '24px 28px';
        ratingsSection.style.boxShadow = '0 8px 20px rgba(15,23,42,0.08)';
        ratingsSection.style.width = '100%';
        ratingsSection.style.maxWidth = '720px';
        ratingsSection.style.display = 'flex';
        ratingsSection.style.flexDirection = 'column';
        ratingsSection.style.alignItems = 'center';

        const hasReviews = reviews.length > 0;
        const ratingCountText = hasReviews
            ? `(${reviews.length} rating${reviews.length>1?'s':''})`
            : '(0 ratings)';
        const reviewHtml = hasReviews
            ? reviews
                .sort((a,b) => (b.ts||0) - (a.ts||0))
                .map(r => `
                    <div class="pr-review" style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                      <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <strong>${(r.user||'User')}</strong>
                        <span style="font-size:12px;color:#9ca3af;">${new Date(r.ts||Date.now()).toLocaleString()}</span>
                      </div>
                      <div style="font-size:14px;color:#f97316;margin-bottom:4px;">
                        ${'★'.repeat(Math.max(1,Math.min(5,Number(r.stars||0))))}
                      </div>
                      <div style="font-size:14px;color:#374151;white-space:pre-wrap;">${(r.text||'').replace(/[<>]/g,'')}</div>
                    </div>
                  `).join('')
            : '<p>No written reviews yet. Be the first to rate this product.</p>';

        ratingsSection.innerHTML = `
            <h2 style="margin-bottom:18px;font-size:24px;text-align:center;width:100%;">Product Ratings</h2>
            <div style="display:flex;align-items:center;gap:22px;margin-bottom:18px;justify-content:center;width:100%;">
                <div style="font-size:42px;font-weight:700;color:#f97316;">${avgRating.toFixed(1)}</div>
                <div style="font-size:18px;">out of 5</div>
                <div class="rating" style="font-size:22px;display:flex;align-items:center;gap:8px;">
                    <span>${getStars(avgRating)}</span>
                    <span style="font-size:14px;color:#6b7280;">${ratingCountText}</span>
                </div>
            </div>
            <div class="rating-list" style="border-top:1px solid #f1f5f9;padding-top:14px;font-size:14px;color:#6b7280;width:100%;max-width:640px;">
                ${reviewHtml}
            </div>
        `;
        const relatedSection = document.querySelector('.related-section');
        (relatedSection || relatedContainer.parentElement || document.body).appendChild(ratingsSection);
    }
}

// Load and display products when page loads
document.addEventListener('DOMContentLoaded', displayProductDetails);
