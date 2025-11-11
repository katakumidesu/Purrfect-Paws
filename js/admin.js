const toggleSidebar = document.getElementById("toggleSidebar");
const sidebar = document.querySelector(".sidebar");
if (toggleSidebar) {
toggleSidebar.addEventListener("click", () => sidebar.classList.toggle("collapsed"));
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
        
        const totalProducts = Array.isArray(products) ? products.length : 0;
        const totalUsers = Array.isArray(users) ? users.length : 0;
        const lowStock = Array.isArray(products) ? products.filter(p => p.stock < 10).length : 0;
        
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
                        <p class="card-value">${totalProducts}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-users"></i></div>
                    <div class="card-content">
                        <h3>Total Users</h3>
                        <p class="card-value">${totalUsers}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-file-invoice"></i></div>
                    <div class="card-content">
                        <h3>Total Orders</h3>
                        <p class="card-value">0</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="card-content">
                        <h3>Low Stock Items</h3>
                        <p class="card-value">${lowStock}</p>
                    </div>
                </div>
            </div>
        `;
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
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    ${productsList.length === 0 ? 
                        '<tr><td colspan="8" class="text-center">No products found. Click "Add Product" to get started.</td></tr>' :
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
                                <td>${escapeHtml(p.category_name || 'Uncategorized')}</td>
                                <td class="price">$${parseFloat(p.price).toFixed(2)}</td>
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
        (p.category_name && p.category_name.toLowerCase().includes(searchTerm)) ||
        (p.description && p.description.toLowerCase().includes(searchTerm))
    );
    
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;
    
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No products found matching your search.</td></tr>';
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
            <td>${escapeHtml(p.category_name || 'Uncategorized')}</td>
            <td class="price">$${parseFloat(p.price).toFixed(2)}</td>
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
                        <label for="productCategory">Category</label>
                        <select id="productCategory" name="category_id">
                            <option value="">Select Category</option>
                            ${categories.map(cat => `
                                <option value="${cat.category_id}">${escapeHtml(cat.category_name)}</option>
                            `).join('')}
                        </select>
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
    document.getElementById('productCategory').value = product.category_id || '';
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
        category_id: document.getElementById('productCategory').value || null,
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
            <td>${o.date ? new Date(o.date).toLocaleString() : '-'}</td>
            <td class="price">$${Number(o.total||0).toFixed(2)}</td>
            <td>
                <span class="status-badge ${st==='to_pay'?'low-stock': st==='completed'?'available':''}">${st.replace('_',' ')}</span>
            </td>
            <td>
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="viewOrderItems(${o.order_id})">Details</button>
                    ${st==='to_pay' ? `<button type="button" class="btn btn-primary" onclick="changeOrderStatus(${o.order_id},'to_ship')">Approve → To Ship</button>` : ''}
                    ${st==='to_ship' ? `<button type="button" class="btn" onclick="changeOrderStatus(${o.order_id},'to_receive')">Mark To Receive</button>` : ''}
                    ${st==='to_receive' ? `<button type="button" class="btn" onclick="changeOrderStatus(${o.order_id},'completed')">Complete</button>` : ''}
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
                        <th>Details</th>
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

    const res = await fetchAPI(null,'POST',{ action:'update_order_status', order_id: parseInt(orderId,10), status });
    console.log('update_order_status resp', res);
    if (res?.success){
        // Re-fetch to keep items synced (bust cache)
        await loadOrders();
    } else {
        // If backend reports current_status, reflect it
        if (res?.current_status && idx>-1){ ordersCache[idx].status = res.current_status; if (tr) tr.outerHTML = renderOrderRow(ordersCache[idx]); }
        else if (idx>-1 && prev){ ordersCache[idx] = prev; if (tr) tr.outerHTML = renderOrderRow(prev); }
        alert(res?.error || 'Failed to update order');
    }
}

function viewOrderItems(orderId){
    const o = ordersCache.find(x=>x.order_id==orderId);
    const items = Array.isArray(o?.items) ? o.items : [];
    const html = items.length? items.map(it=>`
        <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
            <img src="${it.image_url||'../HTML/images/catbed.jpg'}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" onerror="this.src='../HTML/images/catbed.jpg'">
            <div style="flex:1;">
                <div style="font-weight:600;">${escapeHtml(it.product_name||'')}</div>
                <div style="color:#aaa;font-size:12px;">Qty: ${it.quantity} • $${Number(it.price||0).toFixed(2)}</div>
            </div>
            <div style="font-weight:600;">$${(Number(it.price||0)*Number(it.quantity||0)).toFixed(2)}</div>
        </div>
    `).join('') : '<div style="color:#aaa;">No items found for this order.</div>';
    const body = document.getElementById('orderItemsBody');
    if (body){ body.innerHTML = html; document.getElementById('orderItemsModal').classList.remove('hidden'); }
}
// Expose functions for inline onclick handlers
window.changeOrderStatus = changeOrderStatus;
window.viewOrderItems = viewOrderItems;

// ---------------- ANALYTICS ----------------
function loadAnalytics(){
    mainContent.innerHTML = `
        <div class="inventory-header">
            <h2>Analytics & Insights</h2>
        </div>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-icon"><i class="fa fa-dollar-sign"></i></div>
                <div class="card-content">
                    <h3>Total Revenue</h3>
                    <p class="card-value">$0</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-shopping-cart"></i></div>
                <div class="card-content">
                    <h3>Total Sales</h3>
                    <p class="card-value">0</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-user"></i></div>
                <div class="card-content">
                    <h3>Active Customers</h3>
                    <p class="card-value">0</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fa fa-box"></i></div>
                <div class="card-content">
                    <h3>Popular Products</h3>
                    <p class="card-value">-</p>
                </div>
            </div>
        </div>
        <p style="margin-top: 20px; color: #aaa;">Analytics dashboard coming soon...</p>
    `;
}

// ---------------- REPORTS ----------------
function loadReports(){
    mainContent.innerHTML = `
        <div class="inventory-header">
            <h2>Reports</h2>
        </div>
        <div class="dashboard-cards">
            <div class="card" onclick="alert('Sales Report coming soon')" style="cursor: pointer;">
                <div class="card-icon"><i class="fa fa-file-invoice-dollar"></i></div>
                <div class="card-content">
                    <h3>Sales Report</h3>
                    <p style="color: #aaa;">View detailed sales reports</p>
                </div>
            </div>
            <div class="card" onclick="alert('Product Report coming soon')" style="cursor: pointer;">
                <div class="card-icon"><i class="fa fa-box"></i></div>
                <div class="card-content">
                    <h3>Product Report</h3>
                    <p style="color: #aaa;">Product performance analysis</p>
                </div>
            </div>
            <div class="card" onclick="alert('Customer Report coming soon')" style="cursor: pointer;">
                <div class="card-icon"><i class="fa fa-users"></i></div>
                <div class="card-content">
                    <h3>Customer Report</h3>
                    <p style="color: #aaa;">Customer behavior insights</p>
                </div>
            </div>
            <div class="card" onclick="alert('Inventory Report coming soon')" style="cursor: pointer;">
                <div class="card-icon"><i class="fa fa-warehouse"></i></div>
                <div class="card-content">
                    <h3>Inventory Report</h3>
                    <p style="color: #aaa;">Stock levels and movements</p>
                </div>
            </div>
        </div>
    `;
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
