// 🐾 Product List (loaded from database)
let products = [];
const API_URL = '../crud/crud.php';

// Load products from database
async function loadProducts() {
    try {
        const res = await fetch(`${API_URL}?action=get_products`);
        const data = await res.json();
        if (data && !data.error) {
            // Transform DB products to match expected format
            products = data.map(p => ({
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
    if (p && p.rating != null && p.rating !== '') {
        const r = Math.max(0, Math.min(5, parseFloat(p.rating)));
        return Math.round(r * 2) / 2;
    }
    const base = (p && p.product_id) ? Number(p.product_id) : String(p.name || '').split('').reduce((s,c)=>s+c.charCodeAt(0),0);
    const choices = [3.5, 4, 4.5, 5];
    return choices[base % choices.length];
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
        productContainer.innerHTML = `
            <div class="product-container single-product">
                <div class="product-image">
                    <img src="${product.img}" alt="${product.name}" onerror="this.src='images/catbed.jpg'">
                </div>
                <div class="product-details">
                    <p>Home / Cat Accessories</p>
                    <h1>${product.name}</h1>
                    <h4>$ ${product.price.toFixed(2)}</h4>
                    <input type="number" value="1" min="1">
                    <a href="#" class="purchase-btn-2">Add To Cart</a>
                    <h3>Description:</h3>
                    <p>${product.description}</p>
                    <h3>Product Details <i class="fa fa-indent"></i></h3>
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
            <p><strong>$ ${prod.price.toFixed(2)}</strong></p>
            <a href="product-detail.php?name=${encodeURIComponent(prod.name)}">
                <button class="purchase-btn">Purchase</button>
            </a>
        `;
        relatedContainer.appendChild(div);
    });
}

// Load and display products when page loads
document.addEventListener('DOMContentLoaded', displayProductDetails);
