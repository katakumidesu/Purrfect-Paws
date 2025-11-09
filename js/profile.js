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

// ===== Address Book (Shopee-like) =====
const addressListEl = document.getElementById('addressList');
const modalEl = document.getElementById('addressModal');
const addBtn = document.getElementById('addAddressBtn');
const API = '../profile_php/addresses.php';

function renderAddresses(items){
  if(!Array.isArray(items) || !items.length){
    // hide container and remove border when empty
    addressListEl.innerHTML = '';
    addressListEl.style.display = 'none';
    addressListEl.classList.remove('rows');
    return;
  }
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
  addressListEl.innerHTML = '<div class="loading">Loading addresses...</div>';
  fetch(`${API}?action=list`).then(r=>r.json()).then(renderAddresses).catch(()=>{
    addressListEl.innerHTML = '<div class="empty">Failed to load addresses.</div>';
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

// Sidebar dropdown toggle
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.account-toggle');
  const submenu = document.querySelector('.account-submenu');
  if (toggle && submenu) {
    toggle.addEventListener('click', () => {
      submenu.classList.toggle('open');
    });
  }
  loadAddresses();
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
