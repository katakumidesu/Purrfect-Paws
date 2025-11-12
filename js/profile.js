// ----- Profile form save -----
document.getElementById('profileForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  // Show loading state
  const saveBtn = document.querySelector('.save-btn');
  const originalBtnText = saveBtn.textContent;
  saveBtn.disabled = true;
  saveBtn.textContent = 'Saving...';

  fetch('../profile_php/update_profile.php', {
    method: 'POST',
    body: formData
  })
    .then(res => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.json();
    })
    .then(data => {
      const toast = document.getElementById('toast');
      const toastMsg = document.getElementById('toastMsg');

      // Restore button
      saveBtn.disabled = false;
      saveBtn.textContent = originalBtnText;

      if (data.success) {
        let message = "✔ Profile updated successfully!";
        if (data.image_warning) {
          message += " (Note: " + data.image_warning + ")";
        }
        toastMsg.textContent = message;
        toast.classList.add("show");
        toast.classList.remove("error");

        if (data.updated_phone) {
          document.getElementById('phone').value = data.updated_phone;
        }

        if (data.new_image) {
          const preview = document.getElementById('preview');
          preview.src = data.new_image + '?t=' + new Date().getTime(); // refresh cache
          
          // ✅ Update user-menu image immediately
          const userMenuImg = document.querySelector('.user-menu .user-icon');
          if (userMenuImg) {
            userMenuImg.src = data.new_image + '?t=' + new Date().getTime();
          }
        }

        // ✅ Force refresh of navbar (image & name) after 2 sec
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        toastMsg.textContent = "⚠ " + (data.message || 'Failed to update profile');
        toast.classList.add("error", "show");
      }

      setTimeout(() => toast.classList.remove("show"), 5000);
    })
    .catch(err => {
      console.error('Error:', err);
      const toast = document.getElementById('toast');
      const toastMsg = document.getElementById('toastMsg');
      
      // Restore button
      saveBtn.disabled = false;
      saveBtn.textContent = originalBtnText;
      
      toastMsg.textContent = "⚠ Error: " + (err.message || 'Failed to save. Please try again.');
      toast.classList.add("error", "show");
      setTimeout(() => toast.classList.remove("show"), 5000);
    });
});

document.getElementById('upload').addEventListener('change', function (e) {
  const [file] = e.target.files;
  if (file) document.getElementById('preview').src = URL.createObjectURL(file);
});

// Make the image itself open the file picker
const previewImg = document.getElementById('preview');
if (previewImg) {
  previewImg.addEventListener('click', function(){
    const up = document.getElementById('upload');
    if (up) up.click();
  });
}

// ===== Address Book =====
const addressListEl = document.getElementById('addressList');
const addressEmptyEl = document.getElementById('addressEmpty');
const modalEl = document.getElementById('addressModal');
const addBtn = document.getElementById('addAddressBtn');
const API = '../profile_php/addresses.php';

// Sections
const sectionProfile = document.getElementById('profile');
const sectionAddresses = document.getElementById('addresses');

function setActiveMenu(id){
  document.querySelectorAll('.account-menu a').forEach(a=>{
    const hash = a.getAttribute('href');
    if(hash===`#${id}`){ a.classList.add('active'); } else { a.classList.remove('active'); }
  });
}

const sectionPurchases = document.getElementById('purchases');
let purchasesInit = false;
function setSectionClass(id){
  document.body.classList.remove('section-profile','section-addresses','section-purchases');
  document.body.classList.add(`section-${id}`);
}
function showSectionById(id){
  if(id==='addresses'){
    if(sectionProfile) sectionProfile.style.display = 'none';
    if(sectionAddresses) sectionAddresses.style.display = '';
    if(sectionPurchases) sectionPurchases.style.display = 'none';
    setSectionClass('addresses');
    setActiveMenu('addresses');
    loadAddresses();
  }else if(id==='purchases'){
    if(sectionProfile) sectionProfile.style.display = 'none';
    if(sectionAddresses) sectionAddresses.style.display = 'none';
    if(sectionPurchases) sectionPurchases.style.display = '';
    setSectionClass('purchases');
    setActiveMenu('purchases');
    if(!purchasesInit){ initPurchases(); purchasesInit = true; }
    renderPurchases('all');
  }else{ // default to profile
    if(sectionProfile) sectionProfile.style.display = '';
    if(sectionAddresses) sectionAddresses.style.display = 'none';
    if(sectionPurchases) sectionPurchases.style.display = 'none';
    setSectionClass('profile');
    setActiveMenu('profile');
  }
}

function renderAddresses(items){
  if(!Array.isArray(items) || !items.length){
    // show empty state
    if(addressEmptyEl){ addressEmptyEl.style.display = 'flex'; }
    addressListEl.innerHTML = '';
    addressListEl.style.display = 'none';
    addressListEl.classList.remove('rows');
    return;
  }
  if(addressEmptyEl){ addressEmptyEl.style.display = 'none'; }
  addressListEl.style.display = '';
  addressListEl.classList.add('rows');
  addressListEl.innerHTML = items.map(a => `
    <div class="address-row" data-id="${a.address_id}">
      <div class="addr-left">
        <div class="name-line"><span class="name">${escapeHtml(a.fullname)}</span> <span class="divider">|</span> <span class="phone">${escapeHtml(a.phone)}</span></div>
        <div class="addr-lines">${escapeHtml(a.address_line)}${a.barangay?', '+escapeHtml(a.barangay):''}<br>${escapeHtml(a.city)}${a.province?', '+escapeHtml(a.province):''}${a.postal_code?', '+escapeHtml(a.postal_code):''}</div>
        ${a.is_default==1?'<div class="badges"><span class="badge">Default</span></div>':''}
      </div>
      <div class="addr-actions">
        <button class="link-btn" onclick="openEditAddress(${a.address_id})">Edit</button>
        <button class="link-btn danger" onclick="deleteAddress(${a.address_id})">Delete</button>
        ${a.is_default==1? '' : `<button class=\"btn-outline\" onclick=\"setDefaultAddress(${a.address_id})\">Set as default</button>`}
      </div>
    </div>
  `).join('');
}

function loadAddresses(){
  if(addressEmptyEl){ addressEmptyEl.style.display = 'none'; }
  addressListEl.style.display = '';
  addressListEl.classList.remove('rows');
  addressListEl.innerHTML = '<div class="loading" style="padding:16px">Loading addresses...</div>';
  fetch(`${API}?action=list`).then(r=>r.json()).then(renderAddresses).catch((err)=>{
    // Fallback to empty-state UI if API fails
    if(addressEmptyEl){ addressEmptyEl.style.display = 'flex'; }
    addressListEl.innerHTML = '';
    addressListEl.style.display = 'none';
    console.error('Failed to load addresses', err);
  });
}

function openAddAddress(){
  showAddressModal('Add Address');
}
function openEditAddress(id){
  // fetch list and find
  fetch(`${API}?action=list`).then(r=>r.json()).then(list=>{
    const a = (list||[]).find(x=>String(x.address_id)===String(id));
    showAddressModal('Edit Address', a);
  });
}

function showAddressModal(title, addr){
  modalEl.style.display = 'flex';
  modalEl.innerHTML = `
    <div class="panel">
      <h3>${title}</h3>
      <form id="addrForm">
        ${addr?`<input type="hidden" name="address_id" value="${addr.address_id}">`:''}
        <div class="grid">
          <div>
            <label>Full Name</label>
            <input name="fullname" required value="${addr?escapeHtml(addr.fullname):''}">
          </div>
          <div>
            <label>Phone</label>
            <input name="phone" required value="${addr?escapeHtml(addr.phone):''}" placeholder="09xxxxxxxxx">
          </div>
          <div>
            <label>Label</label>
            <select name="label_select_unused" style="display:none">
              ${['Home','Work','Other'].map(l=>`<option ${addr&&addr.label===l?'selected':''}>${l}</option>`).join('')}
            </select>
          </div>
          <div class="full">
            <label>Address Line</label>
            <input name="address_line" required value="${addr?escapeHtml(addr.address_line):''}" placeholder="Street Name, Building, House No.">
          </div>
          <div class="full">
            <label>Label As</label>
            <input type="hidden" name="label" value="${addr?escapeHtml(addr.label||'Home'):'Home'}">
            <div class="label-pills">
              ${['Home','Work','Other'].map(l=>`<span class=\"label-pill ${addr&&addr.label===l?'active':''}\" data-value=\"${l}\">${l}</span>`).join('')}
            </div>
          </div>
          <div>
            <label>Barangay</label>
            <input name="barangay" value="${addr?escapeHtml(addr.barangay):''}">
          </div>
          <div>
            <label>City</label>
            <input name="city" required value="${addr?escapeHtml(addr.city):''}">
          </div>
          <div>
            <label>Province</label>
            <input name="province" value="${addr?escapeHtml(addr.province):''}">
          </div>
          <div>
            <label>Postal Code</label>
            <input name="postal_code" value="${addr?escapeHtml(addr.postal_code):''}">
          </div>
          <div class="full">
            <div class="map-placeholder">Map preview (optional)</div>
          </div>
          <div class="full">
            <label><input type="checkbox" name="is_default" ${addr&&addr.is_default==1?'checked':''}> Set as default address</label>
          </div>
        </div>
        <footer>
          <button type="button" class="btn-secondary" onclick="closeAddressModal()">Cancel</button>
          <button type="submit" class="btn-primary">Save</button>
        </footer>
      </form>
    </div>`;
  document.getElementById('addrForm').addEventListener('submit', submitAddressForm);
  // Label pill selection
  modalEl.querySelectorAll('.label-pill').forEach(pill=>{
    pill.addEventListener('click', ()=>{
      modalEl.querySelectorAll('.label-pill').forEach(x=>x.classList.remove('active'));
      pill.classList.add('active');
      const input = modalEl.querySelector('input[name="label"]');
      if(input) input.value = pill.getAttribute('data-value');
    });
  });
}

function closeAddressModal(){ modalEl.style.display='none'; modalEl.innerHTML=''; }

function submitAddressForm(e){
  e.preventDefault();
  const fd = new FormData(e.target);
  const isEdit = !!fd.get('address_id');
  const action = isEdit ? 'update' : 'create';
  fd.append('action', action);
  if (fd.get('is_default')) fd.set('is_default','1'); else fd.set('is_default','0');
  fetch(API, { method:'POST', body: fd })
    .then(r=>r.json()).then(data=>{
      if(data.error){ alert(data.error); return; }
      closeAddressModal();
      loadAddresses();
    }).catch(err=>alert('Failed to save address'));
}

function deleteAddress(id){
  if(!confirm('Delete this address?')) return;
  const fd = new FormData(); fd.append('action','delete'); fd.append('address_id', id);
  fetch(API, { method:'POST', body: fd}).then(r=>r.json()).then(d=>{ if(!d.error){ loadAddresses(); }});
}
function setDefaultAddress(id){
  const fd = new FormData(); fd.append('action','set_default'); fd.append('address_id', id);
  fetch(API, { method:'POST', body: fd}).then(r=>r.json()).then(d=>{ if(!d.error){ loadAddresses(); }});
}

if(addBtn){ addBtn.addEventListener('click', openAddAddress); }

// Sidebar links (including avatar/edit link) toggle between sections
document.querySelectorAll('.account-sidebar a[href^="#"]').forEach(a=>{
  a.addEventListener('click', (e)=>{
    e.preventDefault();
    const id = a.getAttribute('href').slice(1);
    showSectionById(id);
    if(history && history.pushState){ history.pushState(null,'',`#${id}`);} else { location.hash = `#${id}`; }
  });
});

// Toggle dropdown for My Account in sidebar
(function(){
  const toggle = document.getElementById('toggle-account');
  const menu = document.getElementById('submenu-account');
  if(toggle && menu){
    toggle.addEventListener('click', ()=>{ 
      const open = menu.classList.toggle('open'); 
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  document.querySelectorAll('#submenu-account a').forEach(a=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      const id = a.getAttribute('href').slice(1);
      showSectionById(id);
      if(history && history.pushState){ history.pushState(null,'',`#${id}`);} else { location.hash = `#${id}`; }
    });
  });
})();
// Explicit binding for purchases link (single link)
const purchasesLink = document.getElementById('link-purchases');
if(purchasesLink){
  purchasesLink.addEventListener('click', (e)=>{
    e.preventDefault();
    showSectionById('purchases');
    if(history&&history.pushState){history.pushState(null,'','#purchases');} else { location.hash='#purchases'; }
  });
}

// ===== Purchases =====
function getOrders(){
  try { return JSON.parse(sessionStorage.getItem('purrfectOrders')||'[]'); } catch(e){ return []; }
}
function money(n){ return '₱'+Number(n||0).toFixed(2); }
function saveOrders(arr){ sessionStorage.setItem('purrfectOrders', JSON.stringify(arr||[])); }
// Cancel modal (Shopee-like)
function openCancelModal(orderKey){
  const modal = document.getElementById('pcModal') || Object.assign(document.createElement('div'), { id:'pcModal', className:'pc-modal' });
  const panelStyle = 'background:#fff;border-radius:12px;width:420px;max-width:95vw;padding:16px 16px 12px;box-shadow:0 14px 34px rgba(2,8,23,.18);border:1px solid #e9eef2;';
  const noteStyle = 'background:#fff7e6;border:1px solid #ffe0b2;color:#8d6e63;padding:10px 12px;border-radius:6px;font-size:13px;margin:8px 0 12px;';
  const btnBase = 'display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:all .15s ease;box-shadow:0 2px 6px rgba(0,0,0,.08);';
  modal.innerHTML = `
    <div class="panel" style="${panelStyle}">
      <h3 style="margin:0 0 8px;">Select Cancellation Reason</h3>
      <div class="note" style="${noteStyle}">Please select a cancellation reason. Please take note that this will cancel all items in the order and the action cannot be undone.</div>
      <form id="pcForm">
        ${[
          'Need to change delivery address',
          'Found cheaper elsewhere',
          "Don\'t want to buy anymore",
          'Others'
        ].map((txt,i)=>`<label class=\"row\" style=\"display:flex;gap:8px;align-items:flex-start;\"><input type=\"radio\" name=\"reason\" value=\"${txt.replace(/\"/g,'&quot;')}\" ${i===0?'checked':''}> <span>${txt}</span></label>`).join('')}
        <footer class="actions" style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;">
          <button type="button" class="btn outline" id="pcNotNow" style="${btnBase}background:#f7f7f7;border:1px solid #dfe3e6;color:#444;">NOT NOW</button>
          <button type="submit" class="btn danger" id="pcConfirm" style="${btnBase}background:#fff;border:1px solid #ff6b6b;color:#c92a2a;">CANCEL ORDER</button>
        </footer>
      </form>
    </div>`;
  document.body.appendChild(modal);
  modal.style.display = 'flex';
  modal.addEventListener('click', (e)=>{ if (e.target===modal) closeCancelModal(); });
  document.getElementById('pcNotNow').onclick = closeCancelModal;
  document.getElementById('pcForm').onsubmit = (e)=>{
    e.preventDefault();
    const orders = getOrders();
    const idx = orders.findIndex(o=> String(o.date) === String(orderKey));
    if (idx>-1){ orders[idx].status = 'cancelled'; saveOrders(orders); }
    closeCancelModal();
    updatePurchaseTabCounts();
    const current = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
    renderPurchases(current);
  };
}
function closeCancelModal(){ const m = document.getElementById('pcModal'); if (m) m.remove(); }

// Update tab counts like Shopee (e.g., To Pay (1))
function updatePurchaseTabCounts(){
  const orders = getOrders().map(o=> ({...o, status: (o.status||'completed')}));
  const counts = orders.reduce((acc,o)=>{ acc[o.status]=(acc[o.status]||0)+1; acc.all++; return acc; }, {all:0});
  const set = (tab, label, n)=>{
    const el = document.querySelector(`.p-head .tabs a[data-tab="${tab}"]`);
    if (el) el.textContent = n>0 ? `${label} (${n})` : label;
  };
  set('all','All', counts.all||0);
  set('to_pay','To Pay', counts.to_pay||0);
  set('to_receive','To Receive', counts.to_receive||0);
  set('completed','Completed', counts.completed||0);
  set('cancelled','Cancelled', counts.cancelled||0);
}

function initPurchases(){
  const tabs = document.querySelectorAll('.p-head .tabs a');
  tabs.forEach(a=>a.addEventListener('click', (e)=>{
    e.preventDefault();
    tabs.forEach(x=>x.classList.remove('active'));
    a.classList.add('active');
    renderPurchases(a.dataset.tab);
  }));
  const search = document.getElementById('p-search');
  if(search){ search.addEventListener('input', ()=>{
    const current = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
    renderPurchases(current);
  }); }
  updatePurchaseTabCounts();
}
function renderPurchases(tab){
  const wrap = document.getElementById('p-orders');
  if(!wrap) return;
  const q = (document.getElementById('p-search')?.value||'').toLowerCase();
  let orders = getOrders().map(o=> ({...o, status: (o.status||'completed')}));

  if(tab==='to_pay') orders = orders.filter(o=>o.status==='to_pay');
  if(tab==='to_receive') orders = orders.filter(o=>o.status==='to_receive');
  if(tab==='completed') orders = orders.filter(o=>o.status==='completed');
  if(tab==='cancelled') orders = orders.filter(o=>o.status==='cancelled');

  if(q) orders = orders.filter(o => (o.items||[]).some(it => (it.name||'').toLowerCase().includes(q)) );

  if(!orders.length){
    wrap.innerHTML = '<div class="address-empty" style="display:flex"><div class="empty-inner"><p class="empty-main">No orders yet.</p></div></div>';
    return;
  }

  const statusLabel = (s)=>({to_pay:'To Pay', to_receive:'To Receive', completed:'Completed', cancelled:'Cancelled'})[s]||'—';

  wrap.innerHTML = orders.map((o,idx)=>{
    if (o.status === 'to_pay'){
      return `
      <div class="order pay">
        <div class="shopbar">
          <div class="left"><span class="shopname">In Shopping</span>
            <button class="mini">Chat</button>
            <button class="mini">View Shop</button>
          </div>
          <div class="status tag to-pay">TO PAY</div>
        </div>
        <div class="order-items">
          ${(o.items||[]).map(it=>`
            <div class="oi">
              <img src="${it.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" alt="">
              <div style="flex:1">
                <div class="name">${escapeHtml(it.name)}</div>
                <div class="meta">Variation: — • x${it.quantity||1}</div>
              </div>
              <div class="price">${money((it.price||0))}</div>
            </div>
          `).join('')}
        </div>
        <div class="order-f pay">
          <div class="amount">Amount Payable: <strong class="aprice">${money(o.total||0)}</strong></div>
          <div class="actions">
            <button class="btn small" disabled>Pending</button>
            <button class="btn outline">Contact Seller</button>
            <button class="btn outline danger cancel-order" data-key="${o.date}">Cancel Order</button>
          </div>
        </div>
      </div>`;
    }
    return `
    <div class="order">
      <div class="order-h">
        <div><strong>Order #${idx+1}</strong> <span style="color:#9ab0bd;margin-left:8px">${new Date(o.date||Date.now()).toLocaleString()}</span></div>
        <div class="status">${statusLabel(o.status)}</div>
      </div>
      <div class="order-items">
        ${(o.items||[]).map(it=>`
          <div class="oi">
            <img src="${it.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" alt="">
            <div style="flex:1">
              <div class="name">${escapeHtml(it.name)}</div>
              <div class="meta">Qty: ${it.quantity||1} • Price: ${money(it.price||0)}</div>
            </div>
          </div>
        `).join('')}
      </div>
      <div class="order-f">
        <div class="order-total">Order Total: <strong>${money(o.total||0)}</strong></div>
        <div class="actions"><button class="buy-again" onclick="location.href='../HTML/product-detail.php?name=${encodeURIComponent(((o.items||[])[0]||{}).name||'')}'">Buy Again</button></div>
      </div>
    </div>`;
  }).join('');

  // Wire up cancel buttons
  wrap.querySelectorAll('.cancel-order').forEach(btn=>{
    btn.addEventListener('click', ()=> openCancelModal(btn.getAttribute('data-key')) );
  });
  updatePurchaseTabCounts();
}

// Initial section on load
 document.addEventListener('DOMContentLoaded', () => {
  const initial = (location.hash||'#profile').replace('#','');
  showSectionById(initial);
});

window.addEventListener('hashchange', ()=>{
  const id = (location.hash||'#profile').replace('#','');
  showSectionById(id);
});

// small helper already available above
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text || '';
  return div.innerHTML;
}

window.openEditAddress = openEditAddress; // expose for inline onclick
window.deleteAddress = deleteAddress;
window.setDefaultAddress = setDefaultAddress;
window.closeAddressModal = closeAddressModal;
