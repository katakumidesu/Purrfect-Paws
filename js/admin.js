const toggleSidebar = document.getElementById("toggleSidebar");
const sidebar = document.querySelector(".sidebar");
if (toggleSidebar) {
toggleSidebar.addEventListener("click", () => sidebar.classList.toggle("collapsed"));
}

function fmtNumber(v){
    const n = Number(v)||0;
    return n.toLocaleString('en-PH');
}
function fmtCurrency(v){
    const n = Number(v)||0;
    return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const menuItems = document.querySelectorAll(".menu li");
const mainContent = document.getElementById("mainContent");

// API endpoint
const API_URL = '../crud/crud.php';


menuItems.forEach(item => {
    item.addEventListener("click", () => {
        document.querySelector(".menu li.active")?.classList.remove("active");
        item.classList.add("active");
        const section = item.getAttribute("data-section");

        switch(section){
            case "dashboard": loadDashboard(); break;
            case "inventory": loadInventory(); break;
            case "orders": loadOrders(); break;
            case "delivery": loadDelivery(); break;
            case "users": loadUsers(); break;
            case "analytics": loadAnalytics(); break;
            case "reports": loadReports(); break;
        }
});
});

// Default load after listeners
try { document.querySelector('.menu li.active')?.click(); } catch (e) { loadDashboard(); }

// ---------------- API HELPER FUNCTIONS ----------------
async function fetchAPI(action, method = 'GET', data = null) {
    try {
        if (method === 'GET' && action) {
            const response = await fetch(`${API_URL}?action=${action}&_=${Date.now()}`);
            const result = await response.json();
            return result;
        }
        
        // POST request
        const options = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data || { action: action })
        };
        
        const response = await fetch(API_URL, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Error:', error);
        return { error: error.message };
    }
}

// ---------------- DASHBOARD ----------------
async function loadDashboard(){
    try {
        const products = await fetchAPI('get_products');
        const users = await fetchAPI('get_users');
        const orders = await fetchAPI('get_orders');
        
        const totalProducts = Array.isArray(products) ? products.length : 0;
        const totalUsers = Array.isArray(users) ? users.length : 0;
        const totalOrders = Array.isArray(orders) ? orders.length : 0;
        const lowStock = Array.isArray(products) ? products.filter(p => p.stock < 10).length : 0;
        // Compute lightweight analytics for current month
        const arr = Array.isArray(orders) ? orders : [];
        const now = new Date();
        const y = now.getFullYear();
        const m = now.getMonth();
        const start = new Date(y, m, 1);
        const end = new Date(y, m + 1, 0);
        const daysInMonth = end.getDate();
        const dailyTotal = Array.from({ length: daysInMonth }, () => 0);
        const statusCount = { to_pay: 0, to_ship: 0, to_receive: 0, completed: 0, cancelled: 0 };
        for (let i = 0; i < arr.length; i++) {
            const o = arr[i];
            const d = o.date ? new Date(o.date) : null;
            const total = Number(o.total || 0) || 0;
            const s = String(o.status || '').toLowerCase();
            if (statusCount[s] !== undefined) statusCount[s] += 1;
            if (d && d.getFullYear() === y && d.getMonth() === m) {
                const di = d.getDate() - 1;
                if (di >= 0 && di < dailyTotal.length) dailyTotal[di] += total;
            }
        }

        // Build monthly series like Reports (same result)
        const byMonth = Array.from({length:12}, () => ({rev:0, count:0}));
        for (let i=0;i<arr.length;i++){
            const o = arr[i];
            // Try a wider set of potential date fields
            const dateFields = ['order_date','date','created_at','updated_at','ordered_at','order_time','placed_at','timestamp','createdAt','updatedAt'];
            let raw = null;
            for (let f=0; f<dateFields.length; f++){ if (o[dateFields[f]]) { raw = o[dateFields[f]]; break; } }
            const d = raw ? new Date(typeof raw==='string' ? String(raw).replace(' ','T') : raw) : null;
            const mm = (!d || isNaN(d)) ? (new Date()).getMonth() : d.getMonth();
            const rev = (function(){
                const raw = (o.amount!=null? o.amount : (o.total!=null? o.total : o.total_amount));
                const n = parseFloat(String(raw==null?0:raw).replace(/[^0-9.-]/g,''));
                return isFinite(n)? n : 0;
            })();
            byMonth[mm].rev += rev;
            byMonth[mm].count += 1;
        }
        const monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const revData = byMonth.map(x=>x.rev);
        const cntData = byMonth.map(x=>x.count);
        // Build orders-by-day for current month
        const dayCounts = Array.from({length: daysInMonth}, ()=>0);
        for (let i=0;i<arr.length;i++){
            const o = arr[i];
            const dateFields = ['order_date','date','created_at','updated_at','ordered_at','order_time','placed_at','timestamp','createdAt','updatedAt'];
            let raw = null; for (let f=0; f<dateFields.length; f++){ if (o[dateFields[f]]) { raw = o[dateFields[f]]; break; } }
            let d = raw ? new Date(typeof raw==='string' ? String(raw).replace(' ','T') : raw) : null;
            if (!d || isNaN(d)) d = new Date();
            if (d.getFullYear()===y && d.getMonth()===m){ const di = d.getDate()-1; if (di>=0 && di<dayCounts.length) dayCounts[di] = (dayCounts[di]||0)+1; }
        }
        
    mainContent.innerHTML = `
            <div class="dashboard-header">
    <h2>Dashboard</h2>
                <p class="subtitle">Welcome to Purrfect Paws Admin Panel</p>
            </div>
    <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon"><i class="fa fa-box"></i></div>
                    <div class="card-content">
                        <h3>Total Products</h3>
                        <p class="card-value">${fmtNumber(totalProducts)}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-users"></i></div>
                    <div class="card-content">
                        <h3>Total Users</h3>
                        <p class="card-value">${fmtNumber(totalUsers)}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-file-invoice"></i></div>
                    <div class="card-content">
                        <h3>Total Orders</h3>
                        <p class="card-value">${fmtNumber(totalOrders)}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="card-content">
                        <h3>Low Stock Items</h3>
                        <p class="card-value">${fmtNumber(lowStock)}</p>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-top:14px;">
                <div style="border:1px solid #333;border-radius:12px;background:#222;padding:12px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;"><div style="font-weight:600;color:#fff">Total Sales</div></div>
                    <canvas id="dash_sales_line" height="110"></canvas>
                </div>
                <div style="border:1px solid #333;border-radius:12px;background:#222;padding:12px;">
                    <div style="font-weight:600;margin-bottom:8px;color:#fff">Orders by status</div>
                    <canvas id="dash_pie" height="110"></canvas>
                </div>
            </div>
        `;
        // Lazy load Chart.js and render widgets
        try {
            if (!window.Chart) {
                await new Promise((res) => { const s = document.createElement('script'); s.src = 'https://cdn.jsdelivr.net/npm/chart.js'; s.onload = () => res(); document.head.appendChild(s); });
            }
            const ctxTS = document.getElementById('dash_sales_line')?.getContext('2d');
            if (ctxTS) {
                new Chart(ctxTS, {
                    type: 'line',
                    data: { labels: monthLabels, datasets: [
                        { label: 'Revenue', data: revData, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.12)', tension: 0.35, fill: true },
                        { label: 'Orders', data: cntData, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.12)', tension: 0.35 }
                    ] },
                    options: { responsive: true, plugins:{ legend:{ labels:{ color:'#fff' } } }, scales:{ x:{ ticks:{ color:'#ddd' } }, y:{ ticks:{ color:'#ddd' } } } }
                });
            }
            const ctxP = document.getElementById('dash_pie')?.getContext('2d');
            if (ctxP) {
                const pieLabels = ['To Pay','To Ship','To Receive','Completed','Cancelled'];
                const pieData = [statusCount.to_pay, statusCount.to_ship, statusCount.to_receive, statusCount.completed, statusCount.cancelled];
                const pieColors = ['#f59e0b','#3b82f6','#10b981','#22c55e','#ef4444'];
                new Chart(ctxP, { type: 'doughnut', data: { labels: pieLabels, datasets: [{ data: pieData, backgroundColor: pieColors }] }, options: { plugins: { legend: { position: 'bottom', labels:{ color:'#fff' } } } } });
            }
        } catch(_) {}
    } catch (error) {
        mainContent.innerHTML = `<h2>Dashboard</h2><p>Error loading dashboard: ${error.message}</p>`;
    }
}

// ---------------- INVENTORY ----------------
let categories = [];
let products = [];

async function loadInventory(){
    try {
        // Load categories first
        categories = await fetchAPI('get_categories') || [];
        
        // Load products
        products = await fetchAPI('get_products') || [];
        
        if (products.error) {
            mainContent.innerHTML = `<h2>Inventory</h2><p class="error">Error: ${products.error}</p>`;
            return;
        }
        
        renderInventoryTable(products);
    } catch (error) {
        mainContent.innerHTML = `<h2>Inventory</h2><p class="error">Error loading inventory: ${error.message}</p>`;
    }
}

function renderInventoryTable(productsList) {
    const searchHtml = `
        <div class="inventory-header">
            <h2>Inventory Management</h2>
            <div class="inventory-actions">
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search products..." onkeyup="filterProducts()">
                </div>
                <button class="btn btn-secondary" onclick="window.open('../crud/import_products.php', '_blank')" style="margin-right: 10px;">
                    <i class="fa fa-download"></i> Import Products
                </button>
                <button class="btn btn-primary" onclick="openAddProductModal()">
                    <i class="fa fa-plus"></i> Add Product
                </button>
            </div>
        </div>
        <div class="table-container">
            <table class="table inventory-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    ${productsList.length === 0 ? 
                        '<tr><td colspan="7" class="text-center">No products found. Click "Add Product" to get started.</td></tr>' :
                        productsList.map(p => `
                            <tr data-product-id="${p.product_id}">
                                <td>${p.product_id}</td>
                                <td>
                                    <img src="${p.image_url || '../HTML/images/catbed.jpg'}" 
                                         alt="${p.name}" 
                                         class="product-thumbnail"
                                         onerror="this.src='../HTML/images/catbed.jpg'">
                                </td>
                                <td>
                                    <div class="product-name">${escapeHtml(p.name)}</div>
                                    <div class="product-desc">${escapeHtml(p.description || '').substring(0, 50)}${p.description && p.description.length > 50 ? '...' : ''}</div>
                                </td>
                                <td class="price">${fmtCurrency(p.price)}</td>
                                <td class="stock ${p.stock < 10 ? 'low-stock' : ''}">${p.stock}</td>
                                <td>
                                    <span class="status-badge ${p.stock > 10 ? 'available' : 'low-stock'}">
                                        ${p.stock > 10 ? 'Available' : 'Low Stock'}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-edit" onclick="openEditProductModal(${p.product_id})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteProduct(${p.product_id})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')
                    }
                </tbody>
            </table>
        </div>
    `;
    
    mainContent.innerHTML = searchHtml + getProductModal();
}

function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filtered = products.filter(p => 
        p.name.toLowerCase().includes(searchTerm) ||
        (p.description && p.description.toLowerCase().includes(searchTerm))
    );
    
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No products found matching your search.</td></tr>';
        return;
    }
    
    tbody.innerHTML = filtered.map(p => `
        <tr data-product-id="${p.product_id}">
            <td>${p.product_id}</td>
            <td>
                <img src="${p.image_url || '../HTML/images/catbed.jpg'}" 
                     alt="${p.name}" 
                     class="product-thumbnail"
                     onerror="this.src='../HTML/images/catbed.jpg'">
            </td>
            <td>
                <div class="product-name">${escapeHtml(p.name)}</div>
                <div class="product-desc">${escapeHtml(p.description || '').substring(0, 50)}${p.description && p.description.length > 50 ? '...' : ''}</div>
            </td>
            <td class="price">${fmtCurrency(p.price)}</td>
            <td class="stock ${p.stock < 10 ? 'low-stock' : ''}">${p.stock}</td>
            <td>
                <span class="status-badge ${p.stock > 10 ? 'available' : 'low-stock'}">
                    ${p.stock > 10 ? 'Available' : 'Low Stock'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-icon btn-edit" onclick="openEditProductModal(${p.product_id})" title="Edit">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-icon btn-delete" onclick="deleteProduct(${p.product_id})" title="Delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                    </td>
        </tr>
    `).join('');
}

function getProductModal() {
    return `
        <div id="productModal" class="modal hidden">
            <div class="modal-content">
                <span class="closeBtn" onclick="closeProductModal()">&times;</span>
                <h2 id="modalTitle">Add Product</h2>
                <form id="productForm" onsubmit="saveProduct(event)">
                    <input type="hidden" id="productId" name="product_id">
                    
                    <div class="form-group">
                        <label for="productName">Product Name <span class="required">*</span></label>
                        <input type="text" id="productName" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productPrice">Price <span class="required">*</span></label>
                            <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="productStock">Stock <span class="required">*</span></label>
                            <input type="number" id="productStock" name="stock" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="productRating">Rating</label>
                        <input type="number" id="productRating" name="rating" min="0" max="5" step="0.5" placeholder="0 - 5 (optional)">
                        <small>Optional: 0 to 5 in 0.5 steps. Used for stars on product pages.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="productImage">Image URL</label>
                        <input type="text" id="productImage" name="image_url" placeholder="Enter image URL or path">
                        <small>Example: ../HTML/images/product.jpg</small>
                        <div style="margin-top: 10px;">
                            <input type="file" id="imageUpload" accept="image/*" style="display: none;" onchange="uploadImage(event)">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('imageUpload').click()" style="margin-top: 10px;">
                                <i class="fa fa-upload"></i> Upload Image
                            </button>
                            <div id="uploadStatus" style="margin-top: 10px; font-size: 12px; color: #aaa;"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function openAddProductModal() {
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('uploadStatus').innerHTML = '';
}

function openEditProductModal(productId) {
    const product = products.find(p => p.product_id == productId);
    if (!product) {
        alert('Product not found');
        return;
    }
    
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.product_id;
    document.getElementById('productName').value = product.name || '';
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('productPrice').value = product.price || 0;
    document.getElementById('productStock').value = product.stock || 0;
    document.getElementById('productRating').value = (product.rating != null && product.rating !== '') ? product.rating : '';
    document.getElementById('productImage').value = product.image_url || '';
    document.getElementById('uploadStatus').innerHTML = '';
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

async function saveProduct(event) {
    event.preventDefault();
    
    const formData = {
        action: document.getElementById('productId').value ? 'edit_product' : 'add_product',
        product_id: document.getElementById('productId').value || null,
        name: document.getElementById('productName').value,
        category_id: null,
        description: document.getElementById('productDescription').value,
        price: parseFloat(document.getElementById('productPrice').value),
        stock: parseInt(document.getElementById('productStock').value),
        image_url: document.getElementById('productImage').value || '',
        rating: (document.getElementById('productRating').value !== '') ? parseFloat(document.getElementById('productRating').value) : ''
    };
    
    if (formData.product_id) {
        formData.product_id = parseInt(formData.product_id);
    }
    
    try {
        const result = await fetchAPI(null, 'POST', formData);
        
        if (result.error) {
            alert('Error: ' + result.error);
            return;
        }
        
        if (result.success) {
            // Notify other tabs (e.g., product listing) to refresh
            try { localStorage.setItem('pp_products_updated', Date.now().toString()); } catch (e) {}
            closeProductModal();
            loadInventory(); // Reload inventory
            loadDashboard(); // Update dashboard stats
        } else {
            alert('Failed to save product');
        }
    } catch (error) {
        alert('Error saving product: ' + error.message);
    }
}

async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
        return;
    }
    
    try {
        const result = await fetchAPI(null, 'POST', {
            action: 'delete_product',
            product_id: productId
        });
        
        if (result.error) {
            alert('Error: ' + result.error);
            return;
        }
        
        if (result.success) {
            // Notify other tabs (e.g., product listing) to refresh
            try { localStorage.setItem('pp_products_updated', Date.now().toString()); } catch (e) {}
            loadInventory(); // Reload inventory
            loadDashboard(); // Update dashboard stats
        } else {
            alert('Failed to delete product');
        }
    } catch (error) {
        alert('Error deleting product: ' + error.message);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Image upload function
async function uploadImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const statusDiv = document.getElementById('uploadStatus');
    statusDiv.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
    statusDiv.style.color = '#aaa';
    
    const formData = new FormData();
    formData.append('image', file);
    
    try {
        const response = await fetch('../crud/upload_image.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.error) {
            statusDiv.innerHTML = '<span style="color: #ff6b6b;">Error: ' + result.error + '</span>';
            return;
        }
        
        if (result.success) {
            document.getElementById('productImage').value = result.image_url;
            statusDiv.innerHTML = '<span style="color: #4caf50;"><i class="fa fa-check"></i> ' + result.message + '</span>';
            // Clear the file input
            event.target.value = '';
        }
    } catch (error) {
        statusDiv.innerHTML = '<span style="color: #ff6b6b;">Error uploading image: ' + error.message + '</span>';
    }
}

// ---------------- USERS ----------------
let allUsers = [];

async function loadUsers(){
    try {
        allUsers = await fetchAPI('get_users') || [];
        
        if (allUsers.error) {
            mainContent.innerHTML = `<h2>Users</h2><p class="error">Error: ${allUsers.error}</p>`;
            return;
        }
        
        renderUsersTable(allUsers);
    } catch (error) {
        mainContent.innerHTML = `<h2>Users</h2><p class="error">Error loading users: ${error.message}</p>`;
    }
}

function renderUsersTable(usersList) {
    const html = `
        <div class="inventory-header">
            <h2>User Management</h2>
            <div class="inventory-actions">
                <button class="btn btn-primary" onclick="openAddUserModal()">
                    <i class="fa fa-plus"></i> Add User
                </button>
            </div>
        </div>
        <div class="table-container">
                <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${usersList.length === 0 ? 
                        '<tr><td colspan="8" class="text-center">No users found. Click "Add User" to get started.</td></tr>' :
                        usersList.map(u => `
                            <tr data-user-id="${u.user_id}">
                                <td>${u.user_id}</td>
                                <td>${escapeHtml(u.name || '')}</td>
                                <td>${escapeHtml(u.username || 'N/A')}</td>
                                <td>${escapeHtml(u.email || '')}</td>
                                <td>${escapeHtml(u.phone || 'N/A')}</td>
                                <td><span class="status-badge ${u.role === 'admin' ? 'available' : ''}">${escapeHtml(u.role || 'user')}</span></td>
                                <td>${u.created_at ? u.created_at.split(' ')[0] : 'N/A'}</td>
                                <td>
                                    <div class="action-buttons">
                                        ${u.role === 'admin' ? 
                                            '<span style="color: #aaa; font-size: 12px;">Admin</span>' :
                                            `<button class="btn-icon btn-edit" onclick="openEditUserModal(${u.user_id})" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn-icon btn-delete" onclick="deleteUser(${u.user_id})" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>`
                                        }
                                    </div>
                    </td>
                            </tr>
                        `).join('')
                    }
                </tbody>
            </table>
        </div>
        ${getUserModal()}
    `;
    
    mainContent.innerHTML = html;
}

function getUserModal() {
    return `
        <div id="userModal" class="modal hidden">
            <div class="modal-content">
                <span class="closeBtn" onclick="closeUserModal()">&times;</span>
                <h2 id="userModalTitle">Add User</h2>
                <form id="userForm" onsubmit="saveUser(event)">
                    <input type="hidden" id="userId" name="user_id">
                    
                    <div class="form-group">
                        <label for="userName">Full Name <span class="required">*</span></label>
                        <input type="text" id="userName" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userUsername">Username</label>
                        <input type="text" id="userUsername" name="username" placeholder="Enter username">
                        <small>Optional: Enter username</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="userEmail">Email <span class="required">*</span></label>
                        <input type="email" id="userEmail" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="userPhone">Phone Number</label>
                        <input type="tel" id="userPhone" name="phone" placeholder="09xxxxxxxxx">
                        <small>Optional: Enter phone number (e.g., 09123456789)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="userPassword">Password <span id="passwordRequired" class="required">*</span></label>
                        <input type="password" id="userPassword" name="password" placeholder="Enter password">
                        <small id="passwordHint" style="color: #aaa; display: block; margin-top: 5px;">Enter new password (required for new users)</small>
                        <small id="passwordNote" style="color: #ff6b6b; display: none; margin-top: 5px; font-weight: bold;">⚠️ Passwords are encrypted and cannot be viewed. Enter a new password to change it.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="userRole">Role</label>
                        <input type="text" id="userRole" name="role" value="user" readonly style="background: #2a2a2a; cursor: not-allowed;">
                        <small>All users are assigned the "user" role. Only the hardcoded admin account has admin privileges.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    `;
}

function openAddUserModal() {
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('userPassword').required = true;
    document.getElementById('userPassword').placeholder = 'Enter password (required)';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordHint').style.display = 'block';
    document.getElementById('passwordHint').textContent = 'Enter password (required for new users)';
    document.getElementById('passwordHint').style.color = '#aaa';
    document.getElementById('passwordNote').style.display = 'none';
}

function openEditUserModal(userId) {
    const user = allUsers.find(u => u.user_id == userId);
    if (!user) {
        alert('User not found');
        return;
    }
    
    // Prevent editing admin account
    if (user.role === 'admin') {
        alert('Cannot edit admin account through this interface. Admin account is hardcoded.');
        return;
    }
    
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.user_id;
    document.getElementById('userName').value = user.name || '';
    document.getElementById('userUsername').value = user.username || '';
    document.getElementById('userEmail').value = user.email || '';
    document.getElementById('userPhone').value = user.phone || '';
    document.getElementById('userRole').value = 'user'; // Always 'user' for non-admin accounts
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').placeholder = 'Enter new password to change (leave blank to keep current)';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('passwordNote').style.display = 'block';
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

async function saveUser(event) {
    event.preventDefault();
    
    const userId = document.getElementById('userId').value;
    const isEdit = !!userId;
    const password = document.getElementById('userPassword').value;
    
    // Validate password for new users
    if (!isEdit && !password) {
        alert('Password is required for new users');
        return;
    }
    
    const formData = {
        action: isEdit ? 'edit_user' : 'add_user',
        user_id: userId || null,
        name: document.getElementById('userName').value,
        username: document.getElementById('userUsername').value || '',
        email: document.getElementById('userEmail').value,
        phone: document.getElementById('userPhone').value || '',
        password: password || '',
        role: document.getElementById('userRole').value
    };
    
    if (isEdit) {
        formData.user_id = parseInt(userId);
    }
    
    try {
        const result = await fetchAPI(null, 'POST', formData);
        
        if (result.error) {
            alert('Error: ' + result.error);
            return;
        }
        
        if (result.success) {
            closeUserModal();
            loadUsers(); // Reload users
        } else {
            alert('Failed to save user');
        }
    } catch (error) {
        alert('Error saving user: ' + error.message);
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        return;
    }
    
    try {
        const result = await fetchAPI(null, 'POST', {
            action: 'delete_user',
            user_id: userId
        });
        
        if (result.error) {
            alert('Error: ' + result.error);
            return;
        }
        
        if (result.success) {
            loadUsers(); // Reload users
        } else {
            alert('Failed to delete user');
        }
    } catch (error) {
        alert('Error deleting user: ' + error.message);
    }
}

// ---------------- ORDERS ----------------
let ordersCache = [];
function renderOrderRow(o){
    const st = (o.status && String(o.status).trim()) ? String(o.status) : 'to_pay';
    return `
        <tr data-order-id="${o.order_id}">
            <td>#${o.order_id}</td>
            <td>${escapeHtml(o.customer||'User')} (ID:${o.user_id})</td>
            <td style="text-align:center; width: 120px;"><button type="button" class="btn btn-secondary" onclick="viewOrderItems(${o.order_id})">Details</button></td>
            <td class="price">${fmtCurrency(o.total)}</td>
            <td>
                <span class="status-badge ${st==='to_pay'?'low-stock': st==='completed'?'available':''}">${st.replace('_',' ')}</span>
            </td>
            <td>
                <div class="action-buttons">
                    ${st==='to_pay' ? `<button type="button" class="btn btn-primary" onclick="openStatusModal(${o.order_id},'to_ship')">Approve → To Ship</button>` : ''}
                    ${st==='to_ship' ? `<button type="button" class="btn" onclick="openStatusModal(${o.order_id},'to_receive')">Mark To Receive</button>` : ''}
                    ${st==='to_receive' ? `<button type="button" class="btn" onclick="openStatusModal(${o.order_id},'completed')">Complete</button>` : ''}
                    ${st==='to_pay' || st==='to_ship' ? `<button type="button" class="btn btn-delete" onclick="changeOrderStatus(${o.order_id},'cancelled')">Cancel</button>` : ''}
                </div>
            </td>
        </tr>`;
}
async function loadOrders(){
    const orders = await fetchAPI('get_orders');
    if (orders?.error){
        mainContent.innerHTML = `<h2>Orders</h2><p class="error">${orders.error}</p>`; return;
    }
    ordersCache = Array.isArray(orders) ? orders : [];
    const rows = (ordersCache.length>0) ? ordersCache.map(renderOrderRow).join('') : '<tr><td colspan="6" class="text-center">No orders found.</td></tr>';

    mainContent.innerHTML = `
        <div class="inventory-header">
            <h2>Orders</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th style="text-align:center; width: 120px;">Details</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
        <div id="orderItemsModal" class="modal hidden"><div class="modal-content"><span class="closeBtn" onclick="document.getElementById('orderItemsModal').classList.add('hidden')">&times;</span><h2>Order Items</h2><div id="orderItemsBody" style="margin-top:10px"></div></div></div>
    `;
}

async function changeOrderStatus(orderId, status){
    console.log('changeOrderStatus click', orderId, status);
    // Optimistic UI: update the single row immediately
    const idx = ordersCache.findIndex(o=> String(o.order_id) === String(orderId));
    const prev = idx>-1 ? {...ordersCache[idx]} : null;
    if (idx>-1){ ordersCache[idx].status = status; }
    const tr = document.querySelector(`tr[data-order-id="${orderId}"]`);
    if (tr && idx>-1){ tr.outerHTML = renderOrderRow(ordersCache[idx]); }

    let res;
    try{
        // Use unified backend that already supports status updates and matches schema
        res = await fetchAPI(null,'POST',{ action:'update_order_status', order_id: parseInt(orderId,10), status });
    }catch(e){ res = { success:false, error: e?.message || 'Network error' }; }
    console.log('update order status resp', res);
    if (res?.success){
        // Keep optimistic UI; ensure cached row reflects new status
        if (idx>-1){ ordersCache[idx].status = status; }
        const trOk = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (trOk && idx>-1){ trOk.outerHTML = renderOrderRow(ordersCache[idx]); }
        // Optional: background refresh to sync other rows without flashing this one
        try { fetchAPI('get_orders').then(list => { if (Array.isArray(list)) { ordersCache = list; } }); } catch(_){ }
    } else {
        // Roll back optimistic change
        if (idx>-1 && prev){ ordersCache[idx] = prev; }
        const tr2 = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (tr2 && idx>-1){ tr2.outerHTML = renderOrderRow(prev || ordersCache[idx]); }
        const detail = (res && (res.current_status!==undefined)) ? ` (current_status: ${res.current_status})` : '';
        if (res && res.tried) { try { console.warn('Update variants tried:', res.tried); } catch(_){} }
    }
}

function viewOrderItems(orderId){
    try {
        // Try Orders tab cache first, then Delivery cache
        let order = ordersCache.find(o => String(o.order_id) === String(orderId));
        if (!order) {
            order = (deliveryCache || []).find(o => String(o.order_id) === String(orderId));
        }

        const items = Array.isArray(order?.items) ? order.items : [];
        const addrParts = [];
        if (order) {
            if (order.address_line) addrParts.push(order.address_line);
            if (order.barangay) addrParts.push(order.barangay);
            if (order.city) addrParts.push(order.city);
            if (order.province) addrParts.push(order.province);
            if (order.postal_code) addrParts.push(order.postal_code);
        }
        const addressHtml = order && addrParts.length
            ? `<div style="margin-bottom:10px;padding:10px;border-radius:8px;background:#111827;color:#e5e7eb;font-size:13px;">
                    <div style="font-weight:600;margin-bottom:4px;">Customer</div>
                    <div>${escapeHtml(order.customer||'User')} (ID: ${order.user_id!=null? order.user_id : 'N/A'})</div>
                    <div style="margin-top:6px;font-size:12px;color:#9ca3af;">${escapeHtml(addrParts.join(', '))}</div>
               </div>`
            : '';

        const pmText = order && order.payment_method ? String(order.payment_method) : '';
        let proofPath = order && order.payment_proof ? String(order.payment_proof) : '';
        if (proofPath && !/^https?:\/\//i.test(proofPath)){
            // Make relative paths work from admin/ by prefixing HTML base
            proofPath = '../HTML/' + proofPath.replace(/^\/+/, '');
        }
        const paymentHtml = pmText
            ? `<div style="margin:10px 0 6px;padding:8px 10px;border-radius:8px;background:#0f172a;color:#e5e7eb;font-size:13px;">
                    <div style="font-weight:600;margin-bottom:2px;">Payment Method</div>
                    <div>${escapeHtml(pmText)}</div>
                    ${proofPath ? `<div style="margin-top:8px;">
                        <div style="font-weight:600;margin-bottom:4px;font-size:12px;">Payment Proof</div>
                        <img src="${proofPath}" alt="Payment proof" style="max-width:100%;border-radius:8px;border:1px solid #1f2937;object-fit:contain;">
                    </div>` : ''}
               </div>`
            : (proofPath ? `<div style="margin:10px 0 6px;padding:8px 10px;border-radius:8px;background:#0f172a;color:#e5e7eb;font-size:13px;">
                    <div style="font-weight:600;margin-bottom:4px;font-size:12px;">Payment Proof</div>
                    <img src="${proofPath}" alt="Payment proof" style="max-width:100%;border-radius:8px;border:1px solid #1f2937;object-fit:contain;">
               </div>` : '');

        const itemsHtml = items.length ? items.map(it=>`
            <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
                <img src="${it.image_url||it.image||'../HTML/images/catbed.jpg'}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" onerror="this.src='../HTML/images/catbed.jpg'">
                <div style="flex:1;">
                    <div style="font-weight:600;">${escapeHtml(it.product_name||it.name||'')}</div>
                    <div style="color:#aaa;font-size:12px;">Qty: ${Number(it.quantity||0)} • ₱${Number(it.price||0).toFixed(2)}</div>
                </div>
                <div style="font-weight:600;">₱${(Number(it.price||0)*Number(it.quantity||0)).toFixed(2)}</div>
            </div>
        `).join('') : '<div style="color:#aaa;">No items found for this order.</div>';

        const body = document.getElementById('orderItemsBody');
        const modal = document.getElementById('orderItemsModal');
        if (body && modal){ body.innerHTML = itemsHtml + paymentHtml + addressHtml; modal.classList.remove('hidden'); }
    } catch (e) {
        alert('Unable to load order details.');
    }
}
// Expose functions for inline onclick handlers
window.changeOrderStatus = changeOrderStatus;
window.viewOrderItems = viewOrderItems;
window.openStatusModal = openStatusModal;

// ---------------- DELIVERY (ADMIN) ----------------
let deliveryCache = [];

function filterDeliveriesByStatus(status) {
    if (!Array.isArray(deliveryCache) || deliveryCache.length === 0) return [];
    if (!status || status === 'all') return deliveryCache;
    return deliveryCache.filter(o => String(o.status || '').toLowerCase() === status);
}

function renderDeliveryRow(o) {
    // Normalized order status for logic/filtering
    const st = (o.status && String(o.status).trim()) ? String(o.status).toLowerCase() : 'to_pay';
    const canArrange = (st === 'to_ship');
    const badgeClass = st === 'completed' ? 'available' : (st === 'cancelled' ? 'low-stock' : '');

    // Prefer delivery_status from delivery table for display if present
    const deliveryStatusRaw = (o.delivery_status != null) ? String(o.delivery_status).trim() : '';
    const labelMap = {
        to_receive: 'ORDER IS ON THE WAY',
        completed: 'ORDER HAS BEEN DELIVERED'
    };
    const fallbackText = labelMap[st] || st.replace('_',' ');
    const statusText = deliveryStatusRaw !== '' ? deliveryStatusRaw : fallbackText;
    const deliveryId = `D-${o.order_id}`;
    return `
        <tr data-order-id="${o.order_id}">
            <td>${deliveryId}</td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:999px;background:#333;display:flex;align-items:center;justify-content:center;font-size:18px;">🐾</div>
                    <div>
                        <div style="font-weight:600;">Order #${o.order_id}</div>
                        <div style="font-size:12px;color:#aaa;">${escapeHtml(o.customer||'User')} • ₱${Number(o.total||0).toFixed(2)}</div>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(o.customer||'User')}</td>
            <td class="price">${fmtCurrency(o.total)}</td>
            <td><span class="status-badge ${badgeClass}">${statusText}</span></td>
            <td style="white-space:nowrap;">${escapeHtml(o.shipping_method || 'Standard')}</td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="viewOrderItems(${o.order_id})">View</button>
                    ${canArrange ? `<button type="button" class="btn btn-primary" onclick="openStatusModal(${o.order_id}, 'to_receive')">Arrange Delivery</button>` : ''}
                </div>
            </td>
        </tr>`;
}

async function loadDelivery(statusFilter = 'all') {
    try {
        const orders = await fetchAPI('get_orders');
        if (orders?.error) {
            mainContent.innerHTML = `<div class="inventory-header"><h2>Delivery</h2></div><p class="error">${orders.error}</p>`;
            return;
        }
        deliveryCache = Array.isArray(orders) ? orders : [];
        const filtered = filterDeliveriesByStatus(statusFilter);
        const rows = filtered.length ? filtered.map(renderDeliveryRow).join('') : '<tr><td colspan="6" class="text-center">No deliveries found for this filter.</td></tr>';

        const tabs = [
            { id: 'all', label: 'All' },
            { id: 'to_pay', label: 'To Pay' },
            { id: 'to_ship', label: 'To Ship' },
            { id: 'to_receive', label: 'To Receive' },
            { id: 'completed', label: 'Completed' },
            { id: 'cancelled', label: 'Cancelled' }
        ];

        const tabsHtml = tabs.map(t => `
            <button type="button" class="btn ${statusFilter === t.id ? 'btn-primary' : 'btn-secondary'}" data-filter="${t.id}">
                ${t.label}
            </button>
        `).join('');

        mainContent.innerHTML = `
            <div class="inventory-header">
                <h2>Delivery</h2>
                <div class="inventory-actions">
                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="deliverySearch" placeholder="Search order or customer...">
                    </div>
                </div>
            </div>
            <div style="margin-bottom:15px; display:flex; flex-wrap:wrap; gap:8px;">
                ${tabsHtml}
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Delivery ID</th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Delivery Service</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="deliveryTableBody">${rows}</tbody>
                </table>
            </div>
            <div id="orderItemsModal" class="modal hidden"><div class="modal-content"><span class="closeBtn" onclick="document.getElementById('orderItemsModal').classList.add('hidden')">&times;</span><h2>Order Items</h2><div id="orderItemsBody" style="margin-top:10px"></div></div></div>
        `;

        // Wire filter buttons
        document.querySelectorAll('button[data-filter]').forEach(btn => {
            btn.addEventListener('click', () => {
                const f = btn.getAttribute('data-filter');
                loadDelivery(f);
            });
        });

        // Wire search
        const searchInput = document.getElementById('deliverySearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', () => {
                const term = searchInput.value.toLowerCase();
                const list = filterDeliveriesByStatus(statusFilter);
                const filteredRows = list.filter(o => {
                    const name = String(o.customer || '').toLowerCase();
                    const idStr = String(o.order_id || '');
                    return name.includes(term) || idStr.includes(term);
                }).map(renderDeliveryRow).join('');
                const tbody = document.getElementById('deliveryTableBody');
                if (tbody) {
                    tbody.innerHTML = filteredRows || '<tr><td colspan="6" class="text-center">No deliveries match your search.</td></tr>';
                }
            });
        }
    } catch (e) {
        mainContent.innerHTML = `<div class="inventory-header"><h2>Delivery</h2></div><p class="error">Failed to load deliveries: ${e.message}</p>`;
    }
}

// expose
window.loadDelivery = loadDelivery;

// Lightweight confirmation modal for status updates
function openStatusModal(orderId, status){
    const labels = { to_ship: 'Approved → To Ship', to_receive: 'Marked To Receive', completed: 'Completed' };
    let modal = document.getElementById('admStatusModal');
    if (!modal){
        modal = document.createElement('div');
        modal.id = 'admStatusModal';
        // Toast container at bottom-right (no overlay)
        modal.style.cssText = 'position:fixed;right:16px;bottom:16px;display:flex;align-items:center;justify-content:center;z-index:99999;background:transparent;pointer-events:none;';
        document.body.appendChild(modal);
    }
    const panelStyle = 'pointer-events:auto;display:flex;gap:10px;align-items:center;background:#111;color:#fff;border-radius:10px;min-width:280px;max-width:360px;padding:10px 12px;box-shadow:0 8px 24px rgba(0,0,0,.35);font-family:inherit;border:1px solid rgba(255,255,255,.08);';
    modal.innerHTML = `
      <div class="panel" style="${panelStyle}">
        <div style="width:28px;height:28px;border-radius:8px;background:#1a73e8;display:flex;align-items:center;justify-content:center;flex:0 0 auto;">
          <span style="display:block;width:12px;height:12px;border-radius:50%;background:#fff;"></span>
        </div>
        <div style="min-width:0;flex:1 1 auto;">
          <div id="admStatusTitle" style="font-weight:700;font-size:13px;line-height:1.2;margin:0 0 2px;">Updating...</div>
          <div id="admStatusMsg" style="opacity:.9;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Please wait while we update the order status.</div>
        </div>
      </div>`;
    modal.style.display = 'flex';
    const close = ()=>{ const m=document.getElementById('admStatusModal'); if (m) m.remove(); };

    (async ()=>{
      try{
        await changeOrderStatus(orderId, status);
        const t = document.getElementById('admStatusTitle');
        const m = document.getElementById('admStatusMsg');
        if (t) t.textContent = labels[status] || 'Updated';
        if (m) m.textContent = 'Order status updated successfully.';
      }catch(err){
        const t = document.getElementById('admStatusTitle');
        const m = document.getElementById('admStatusMsg');
        if (t) t.textContent = 'Update Failed';
        if (m) m.textContent = 'There was a problem updating the order. Please try again.';
      }
      setTimeout(close, 2000);
    })();
}

// ---------------- ANALYTICS ----------------
async function loadAnalytics(){
    try {
        var orders = await fetchAPI('get_orders');
        var arr = Array.isArray(orders) ? orders : [];
        var now = new Date();
        var y = now.getFullYear();
        var m = now.getMonth();
        var start = new Date(y, m, 1);
        var end = new Date(y, m + 1, 0);

        var daysInMonth = end.getDate();
        var dailyTotal = Array.from({length: daysInMonth}, function(){ return 0; });

        var thisMonthCount = 0;
        var thisMonthValue = 0;
        var highestOrder = 0;
        var statusRev = {to_pay:0,to_ship:0,to_receive:0,completed:0,cancelled:0};

        // Top products map
        var prodMap = {};

        for (var i=0;i<arr.length;i++){
            var o = arr[i];
            // Robust date parse; fallback to current month if missing
            var raw = o.order_date || o.date || o.created_at || o.updated_at || null;
            var d = raw ? new Date(typeof raw==='string' ? String(raw).replace(' ','T') : raw) : null;
            var inThisMonth = false;
            if (d && !isNaN(d)) { inThisMonth = (d.getFullYear()===y && d.getMonth()===m); }
            else { d = new Date(); inThisMonth = (d.getFullYear()===y && d.getMonth()===m); }

            // Robust amount parse
            var aRaw = (o.amount!=null? o.amount : (o.total!=null? o.total : o.total_amount));
            var total = parseFloat(String(aRaw==null?0:aRaw).replace(/[^0-9.-]/g,'')) || 0;

            var s = String(o.status||'').toLowerCase();
            if (statusRev[s]!==undefined) statusRev[s] += total;

            if (inThisMonth){
                thisMonthCount += 1;
                thisMonthValue += total;
                if (total>highestOrder) highestOrder = total;
                var di = d.getDate()-1; if (di>=0 && di<dailyTotal.length) dailyTotal[di] += total;
            }

            var items = o.items||[];
            for (var k=0;k<items.length;k++){
                var it = items[k];
                var name = String(it.product_name||it.name||'');
                var q = Number(it.quantity||0); var p = Number(it.price||0);
                if (!name) continue; if (!prodMap[name]) prodMap[name] = {qty:0,rev:0};
                prodMap[name].qty += q; prodMap[name].rev += q*p;
            }
        }

        var avgOrder = thisMonthCount ? (thisMonthValue/thisMonthCount) : 0;

        // Build HTML
        var html = '<div class="analytics-wrap" style="color:#111">'
        + '<div class="inventory-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">'
        +   '<div><h2>Analytics</h2><div style="color:#6b7280;font-size:13px;margin-top:2px">View advanced analytics for your business</div></div>'
        +   '<div><button class="btn" onclick="window.print()" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer">Export CSV</button></div>'
        + '</div>'
        + '<div class="dashboard-cards" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:10px;">'
        +   '<div class="card" style="padding:14px;border:1px solid #eee;border-radius:12px;background:#fff"><div style="font-size:12px;color:#6b7280">Orders this month</div><div style="font-size:24px;font-weight:700">' + fmtNumber(thisMonthCount) + '</div></div>'
        +   '<div class="card" style="padding:14px;border:1px solid #eee;border-radius:12px;background:#fff"><div style="font-size:12px;color:#6b7280">Total order value</div><div style="font-size:24px;font-weight:700">' + fmtCurrency(thisMonthValue) + '</div></div>'
        +   '<div class="card" style="padding:14px;border:1px solid #eee;border-radius:12px;background:#fff"><div style="font-size:12px;color:#6b7280">Average order value</div><div style="font-size:24px;font-weight:700">' + fmtCurrency(avgOrder) + '</div></div>'
        +   '<div class="card" style="padding:14px;border:1px solid #eee;border-radius:12px;background:#fff"><div style="font-size:12px;color:#6b7280">Highest order value</div><div style="font-size:24px;font-weight:700">' + fmtCurrency(highestOrder) + '</div></div>'
        + '</div>'
        + '<div style="border:1px solid #eee;border-radius:12px;background:#fff;margin-top:12px;padding:10px 12px;">'
        +   '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;"><div style="font-weight:600">This month at a glance</div><div style="font-size:12px;color:#6b7280">' + start.toLocaleDateString() + ' - ' + end.toLocaleDateString() + '</div></div>'
        +   '<div style="display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:10px;align-items:center">'
        +     '<canvas id="an_line" height="80"></canvas>'
        +     '<div style="border-left:1px solid #f1f5f9;padding-left:10px">'
        +        '<div style="font-size:12px;color:#6b7280">Total</div>'
        +        '<div style="font-size:20px;font-weight:700">' + fmtCurrency(thisMonthValue) + '</div>'
        +        '<div style="font-size:12px;color:#6b7280;margin-top:10px">Orders</div>'
        +        '<div style="font-size:20px;font-weight:700">' + fmtNumber(thisMonthCount) + '</div>'
        +     '</div>'
        +   '</div>'
        + '</div>'
        + '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-top:14px;">'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;"><div style="font-weight:600;margin-bottom:8px;">Value by status</div><canvas id="an_bar_status" height="120"></canvas></div>'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;"><div style="font-weight:600;margin-bottom:8px;">Top products by revenue</div><canvas id="an_bar_prod" height="120"></canvas></div>'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;"><div style="font-weight:600;margin-bottom:8px;">Orders by day</div><canvas id="an_bar_count" height="120"></canvas></div>'
        + '</div>'
        + '</div>';

        mainContent.innerHTML = html;

        function ensureChart(){
            return new Promise(function(res){
                if (window.Chart) return res();
                var s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/chart.js'; s.onload=function(){res();}; document.head.appendChild(s);
            });
        }
        await ensureChart();

        // Charts
        var daysLabels = Array.from({length:daysInMonth}, function(_,i){ return (i+1).toString().padStart(2,'0'); });
        var ctxL = document.getElementById('an_line').getContext('2d');
        new Chart(ctxL, { type:'line', data:{ labels: daysLabels, datasets:[{ label:'Revenue', data: dailyTotal, borderColor:'#0ea5e9', backgroundColor:'rgba(14,165,233,.12)', tension:.35, fill:true }] } });

        var ctxS = document.getElementById('an_bar_status').getContext('2d');
        var sLabels = ['To Pay','To Ship','To Receive','Completed','Cancelled'];
        var sData = [statusRev.to_pay,statusRev.to_ship,statusRev.to_receive,statusRev.completed,statusRev.cancelled];
        new Chart(ctxS, { type:'bar', data:{ labels:sLabels, datasets:[{ label:'Revenue', data:sData, backgroundColor:['#f59e0b','#3b82f6','#10b981','#22c55e','#ef4444'] }] } });

        var top = Object.entries(prodMap).sort(function(a,b){ return b[1].rev - a[1].rev; }).slice(0,6);
        var pLabels = top.map(function(t){ return t[0]; });
        var pData = top.map(function(t){ return Number(t[1].rev||0); });
        var ctxP = document.getElementById('an_bar_prod').getContext('2d');
        new Chart(ctxP, { type:'bar', data:{ labels:pLabels, datasets:[{ label:'Revenue', data:pData, backgroundColor:'#111827' }] } });

        var ctxC = document.getElementById('an_bar_count').getContext('2d');
        var dayCounts = Array.from({length: daysInMonth}, function(){ return 0; });
        for (var d=0; d<arr.length; d++){
            var obj = arr[d];
            var rd = obj.order_date || obj.date || obj.created_at || obj.updated_at || null;
            var od = rd ? new Date(typeof rd==='string' ? String(rd).replace(' ','T') : rd) : null;
            if (!od || isNaN(od)) od = new Date(); // fallback to today so it's counted
            if (od.getFullYear()===y && od.getMonth()===m){ var di2 = od.getDate()-1; dayCounts[di2] = (dayCounts[di2]||0) + 1; }
        }
        window.__charts = window.__charts || {};
        if (window.__charts.an_bar_count) { try { window.__charts.an_bar_count.destroy(); } catch(_){} }
        var maxCount = Math.max.apply(null, dayCounts);
        window.__charts.an_bar_count = new Chart(ctxC, {
            type:'bar',
            data:{ labels: daysLabels, datasets:[{ label:'Orders', data: dayCounts, backgroundColor:'#0f172a' }] },
            options:{ scales:{ y:{ beginAtZero:true, suggestedMax: (isFinite(maxCount)? maxCount : 0) + 1 } } }
        });

        // Auto-refresh analytics every 15s while Analytics tab is active
        try {
            if (window.__analyticsTimer) { clearInterval(window.__analyticsTimer); }
            window.__analyticsTimer = setInterval(function(){
                try {
                    var active = document.querySelector('.menu li.active');
                    if (active && active.getAttribute('data-section') === 'analytics') {
                        loadAnalytics();
                    } else {
                        clearInterval(window.__analyticsTimer);
                    }
                } catch(_){}
            }, 15000);
        } catch(_){}
    } catch (e) {
        mainContent.innerHTML = '<div class="inventory-header"><h2>Analytics</h2></div><p style="color:#ef4444">Failed to load analytics: ' + (e.message||e) + '</p>';
    }
}

// ---------------- REPORTS ----------------
async function loadReports(){
    try {
        var orders = await fetchAPI('get_orders');
        var arr = Array.isArray(orders) ? orders : [];
        var now = new Date();
        var y = now.getFullYear();
        var m = now.getMonth();
        var byMonth = Array.from({length:12}, function(){ return {rev:0,count:0}; });
        var completedRev = 0, completedCount = 0;
        var thisMonthRev = 0, lastMonthRev = 0, thisMonthCount = 0;
        var statusCount = {to_pay:0,to_ship:0,to_receive:0,completed:0,cancelled:0};
        for (var i=0;i<arr.length;i++){
            var o = arr[i];
            var d = o.date ? new Date(o.date) : null;
            var mm = d? d.getMonth(): m;
            var rev = Number(o.total||0);
            var s = String(o.status||'').toLowerCase();
            if (statusCount[s]!==undefined) statusCount[s]++;
            byMonth[mm].rev += rev; byMonth[mm].count += 1;
            if (d && d.getFullYear()===y){ if (mm===m) { thisMonthRev+=rev; thisMonthCount++; } if (mm===((m+11)%12)) { lastMonthRev+=rev; } }
            if (s==='completed'){ completedRev += rev; completedCount++; }
        }
        const netIncome = completedRev;
        const avgOrder = completedCount? (completedRev/completedCount): 0;
        const growthRate = lastMonthRev>0? ((thisMonthRev-lastMonthRev)/lastMonthRev*100): (thisMonthRev>0?100:0);
        var lastMonthIndex = (m+11)%12;
        var lastMonthCount = byMonth[lastMonthIndex].count;

        var html = '<div class="report-wrap" style="color:#111">'
        + '<div class="inventory-header" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">'
        + '<h2>Report</h2>'
        + '<div><button class="btn" onclick="window.print()" style="padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer"><i class="fa fa-download"></i> Download</button></div>'
        + '</div>'
        + '<div class="dashboard-cards" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:10px;">'
        +   '<div class="card" style="padding:16px;border:1px solid #eee;border-radius:12px;background:#fff">'
        +     '<div style="font-size:12px;color:#6b7280;margin-bottom:6px">Net income</div>'
        +     '<div style="font-size:22px;font-weight:700">' + fmtCurrency(netIncome) + '</div>'
        +     '<div style="font-size:12px;color:#16a34a;margin-top:6px">This month: ' + fmtCurrency(thisMonthRev) + '</div>'
        +   '</div>'
        +   '<div class="card" style="padding:16px;border:1px solid #eee;border-radius:12px;background:#fff">'
        +     '<div style="font-size:12px;color:#6b7280;margin-bottom:6px">Orders this month</div>'
        +     '<div style="font-size:22px;font-weight:700">' + fmtNumber(thisMonthCount) + '</div>'
        +     '<div style="font-size:12px;color:#6b7280;margin-top:6px">vs last month ' + fmtNumber(lastMonthCount) + '</div>'
        +   '</div>'
        +   '<div class="card" style="padding:16px;border:1px solid #eee;border-radius:12px;background:#fff">'
        +     '<div style="font-size:12px;color:#6b7280;margin-bottom:6px">Average Order</div>'
        +     '<div style="font-size:22px;font-weight:700">' + fmtCurrency(avgOrder) + '</div>'
        +     '<div style="font-size:12px;color:#6b7280;margin-top:6px">Completed only</div>'
        +   '</div>'
        +   '<div class="card" style="padding:16px;border:1px solid #eee;border-radius:12px;background:#fff">'
        +     '<div style="font-size:12px;color:#6b7280;margin-bottom:6px">Growth Rate</div>'
        +     '<div style="font-size:22px;font-weight:700">' + growthRate.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + '%</div>'
        +     '<div style="font-size:12px;color:#16a34a;margin-top:6px">vs last month</div>'
        +   '</div>'
        + '</div>'
        + '<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-top:14px;">'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;">'
        +     '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;"><div style="font-weight:600">Total Sales</div></div>'
        +     '<canvas id="salesLine" height="110"></canvas>'
        +   '</div>'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;">'
        +     '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;"><div style="font-weight:600">Sales Activity</div></div>'
        +     '<canvas id="salesPie" height="110"></canvas>'
        +   '</div>'
        + '</div>'
        + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;">'
        +     '<div style="font-weight:600;margin-bottom:8px;">Top Products</div><div id="topProducts"></div>'
        +   '</div>'
        +   '<div style="border:1px solid #eee;border-radius:12px;background:#fff;padding:12px;">'
        +     '<div style="font-weight:600;margin-bottom:8px;">New Orders by Month</div>'
        +     '<canvas id="ordersBar" height="120"></canvas>'
        +   '</div>'
        + '</div>'
        + '</div>';
        mainContent.innerHTML = html;

        function ensureChart(){
            return new Promise(function(res){
                if (window.Chart) return res();
                var s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/chart.js'; s.onload=function(){res();}; document.head.appendChild(s);
            });
        }
        await ensureChart();

        var monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var revData = byMonth.map(function(x){ return x.rev; });
        var cntData = byMonth.map(function(x){ return x.count; });
        var ctx1 = document.getElementById('salesLine').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: { labels: monthLabels, datasets: [
                { label: 'Revenue', data: revData, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.12)', tension: 0.35, fill: true },
                { label: 'Orders', data: cntData, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.12)', tension: 0.35 }
            ] },
            options: { responsive: true, plugins:{ legend:{ labels:{ color:'#111' } } }, scales:{ x:{ ticks:{ color:'#111' } }, y:{ ticks:{ color:'#111' } } } }
        });

        var ctx2 = document.getElementById('salesPie').getContext('2d');
        var pieLabels = ['To Pay','To Ship','To Receive','Completed','Cancelled'];
        var pieData = [statusCount.to_pay, statusCount.to_ship, statusCount.to_receive, statusCount.completed, statusCount.cancelled];
        var pieColors = ['#f59e0b','#3b82f6','#10b981','#22c55e','#ef4444'];
        new Chart(ctx2, {
            type: 'doughnut',
            data: { labels: pieLabels, datasets: [{ data: pieData, backgroundColor: pieColors }] },
            options: { plugins: { legend: { position: 'bottom', labels:{ color:'#111' } } } }
        });

        var ctx3 = document.getElementById('ordersBar').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: { labels: monthLabels, datasets: [{ label: 'Orders', data: cntData, backgroundColor: '#111827' }] },
            options: { scales: { x:{ ticks:{ color:'#111' } }, y: { beginAtZero: true, ticks:{ color:'#111' } } }, plugins: { legend: { display: false } } }
        });

        var topWrap = document.getElementById('topProducts');
        var topMap = {};
        for (var j=0;j<arr.length;j++){
            var items = arr[j].items||[];
            for (var k=0;k<items.length;k++){
                var it = items[k];
                var key = String(it.product_name||it.name||'');
                var q = Number(it.quantity||0);
                var p = Number(it.price||0);
                if (!key) continue;
                if (!topMap[key]) topMap[key] = {qty:0,rev:0};
                topMap[key].qty += q; topMap[key].rev += q*p;
            }
        }
        var top = Object.entries(topMap).sort(function(a,b){ return b[1].rev-a[1].rev; }).slice(0,6);
        var headerHtml = '<div style="display:grid;grid-template-columns:1fr 100px 120px;gap:8px;padding:6px 0;color:#6b7280;font-size:13px;border-bottom:1px solid #f1f5f9;">'
            + '<div>Product</div><div style="text-align:right">Qty</div><div style="text-align:right">Revenue</div>'
            + '</div>';
        var rowsHtml = top.map(function(entry){
            var name = entry[0]; var v = entry[1];
            return '<div style="display:grid;grid-template-columns:1fr 100px 120px;gap:8px;padding:8px 0;border-bottom:1px solid #f8fafc;">'
                + '<div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + name + '</div>'
                + '<div style="text-align:right">' + v.qty + '</div>'
                + '<div style="text-align:right">\u20B1' + v.rev.toFixed(2) + '</div>'
                + '</div>';
        }).join('');
        if (!rowsHtml) { rowsHtml = '<div style="padding:8px;color:#9ca3af;">No data</div>'; }
        topWrap.innerHTML = headerHtml + rowsHtml;
    } catch (e) {
        mainContent.innerHTML = `<div class=\"inventory-header\"><h2>Report</h2></div><p style=\"color:#ef4444\">Failed to load report: ${e.message}</p>`;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const productModal = document.getElementById('productModal');
    const userModal = document.getElementById('userModal');
    
    if (event.target == productModal) {
        closeProductModal();
    }
    
    if (event.target == userModal) {
        closeUserModal();
    }
}
