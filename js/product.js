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
  return (src && src.trim()) ? src : '';
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
  const safeList = Array.isArray(list) ? list.filter(p => Number(p?.stock) > 0) : [];
  if (safeList.length === 0) {
    container.innerHTML = '<p style="color:#aaa;">No products found.</p>';
    return;
  }

  container.innerHTML = safeList.map(p => {
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
    const res = await fetch(`${API_URL}?action=get_products&available_only=1&_ts=${Date.now()}`);
    const data = await res.json();
    if (data && !data.error) {
      const filtered = Array.isArray(data) ? data.filter(p => Number(p.stock) > 0) : [];
      renderProducts(filtered);
    } else {
      console.error('Error loading products:', data.error);
    }
  } catch (e) {
    console.error('Error loading products:', e);
  }
}

document.addEventListener('DOMContentLoaded', loadProducts);

// Auto-refresh products when admin updates inventory in another tab
window.addEventListener('storage', (e) => {
  if (e.key === 'pp_products_updated') {
    loadProducts();
  }
});

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