// Profile / Address Book interactions
(function(){
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  const API = '../profile_php/addresses.php'; // from HTML dir

  // Section switching helpers (Profile / Addresses / Purchases)
  const sections = {
    profile: $('#profile'),
    addresses: $('#addresses'),
    purchases: $('#purchases')
  };
  let purchasesInit = false;
  const submenu = $('#submenu-account');
  const accountToggle = $('#toggle-account');
  const showSection = (name) => {
    // Show the requested content section
    Object.entries(sections).forEach(([k, el]) => el && (el.style.display = (k===name? 'block':'none')));

    // Collapse or expand the "My Account" submenu like Shopee
    if (submenu){
      if (name === 'purchases'){
        submenu.classList.remove('open');
        if (accountToggle) accountToggle.setAttribute('aria-expanded','false');
      } else {
        submenu.classList.add('open');
        if (accountToggle) accountToggle.setAttribute('aria-expanded','true');
      }

    }

    // Highlight the active menu item
    $$('.account-menu a').forEach(a=>a.classList.remove('active'));
    const selMap = {
      profile: 'a[href=\"#profile\"]',
      addresses: 'a[href=\"#addresses\"]',
      purchases: '#link-purchases, a[href=\"#purchases\"]'
    };
    $$(selMap[name]).forEach(a=>a.classList.add('active'));

    // Initialize purchases tab handlers and render orders on first entry
    if (name === 'purchases'){
      if (!purchasesInit){ initPurchases(); purchasesInit = true; }
      const tab = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
      renderPurchases(tab);
    }
  };
  // Attach handlers to ALL matching links instead of just the first one
  $$('.account-submenu a[href="#addresses"], a[href="#addresses"], #link-addresses').forEach((el)=>{
    el.addEventListener('click', (e)=>{ e.preventDefault(); showSection('addresses'); loadAddresses(); });
  });
  $$('.account-submenu a[href=\"#profile\"], a[href=\"#profile\"], #link-profile, .au-edit').forEach((el)=>{
    el.addEventListener('click', (e)=>{ e.preventDefault(); showSection('profile'); });
  });
  $$('#link-purchases, a[href=\"#purchases\"]').forEach((el)=>{
    el.addEventListener('click', (e)=>{ e.preventDefault(); showSection('purchases'); });
  });
  // Clicking the "My Account" heading should bring the submenu back and show Profile
  if (accountToggle){
    accountToggle.addEventListener('click', (e)=>{
      e.preventDefault();
      if (submenu){ submenu.classList.add('open'); }
      accountToggle.setAttribute('aria-expanded','true');
      showSection('profile');
    });
  }

  // Toast
  const toast = $('#toast') || Object.assign(document.createElement('div'), { id:'toast', className:'toast' });
  if (!toast.parentNode) document.body.appendChild(toast);
  function showToast(msg, isErr=false){
    const msgEl = toast.querySelector('#toastMsg');
    if (msgEl) {
      msgEl.textContent = msg;
    } else {
      toast.textContent = msg;
    }
    toast.classList.toggle('error', !!isErr);
    toast.classList.add('show');
    setTimeout(()=> toast.classList.remove('show'), 2200);
  }

  // ----- Profile form (image upload + save) -----
  const profileForm = document.getElementById('profileForm');
  if (profileForm){
    // Submit handler -> POST to update_profile.php using FormData
    profileForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(profileForm);
      const btn = profileForm.querySelector('.save-btn');
      const orig = btn ? btn.textContent : '';
      if (btn){ btn.disabled = true; btn.textContent = 'Saving...'; }
      try{
        const res = await fetch('../profile_php/update_profile.php', { method:'POST', body: fd });
        const data = await res.json();
        if (!res.ok || data.success === false){ throw new Error(data.message || 'Failed to update profile'); }
        // Success toast (include image warning if present)
        let msg = 'Profile updated successfully!';
        if (data.image_warning) msg += ' (' + data.image_warning + ')';
        showToast(msg, false);
        // Update preview + navbar + sidebar avatars immediately
        if (data.new_image){
          const url = data.new_image + '?t=' + Date.now();
          const preview = document.getElementById('preview'); if (preview) preview.src = url;
          const navImg = document.querySelector('.user-menu .user-icon'); if (navImg) navImg.src = url;
          const sideImg = document.querySelector('.au-avatar'); if (sideImg) sideImg.src = url;
        }
        // Small delay then refresh so session header updates
        setTimeout(()=> location.reload(), 1200);
      }catch(err){
        showToast(err.message || 'Failed to save. Please try again.', true);
      }finally{
        if (btn){ btn.disabled = false; btn.textContent = orig; }
      }
    });

    // Image preview + click-to-open
    const uploadInput = document.getElementById('upload');
    const previewImg = document.getElementById('preview');
    if (uploadInput){
      uploadInput.addEventListener('change', (e)=>{
        const file = e.target.files && e.target.files[0];
        if (file && previewImg){ previewImg.src = URL.createObjectURL(file); }
      });
    }
    if (previewImg){
      previewImg.addEventListener('click', ()=>{ const up = document.getElementById('upload'); if (up) up.click(); });
    }
  }

  // Address modal
  const modal = $('#addressModal');
  function openAddressModal(address){
    if (!modal) return;
    const isEdit = !!address;
    const label = address?.label || 'Home';
    const html = `
      <div class="panel">
        <h3>${isEdit? 'Edit Address' : 'New Address'}</h3>
        <form id="abForm">
          <div class="grid">
            <div>
              <input id="ab_fullname" name="fullname" placeholder="Full Name" value="${escapeHtml(address?.fullname||'')}" required>
            </div>
            <div>
              <input id="ab_phone" name="phone" placeholder="Phone Number" value="${escapeHtml(address?.phone||'')}" required>
            </div>
            <div class="full">
              <div class="ph-picker" id="phPicker">
                <input id="ab_region" placeholder="Region, Province, City, Barangay" value="${escapeHtml([
                  address?.region||'', address?.province||'', address?.city||'', address?.barangay||''
                ].filter(Boolean).join(', '))}" readonly>
                <button type="button" class="ph-toggle">▾</button>
                <div class="ph-panel" hidden>
                  <div class="ph-tabs">
                    <button type="button" data-tab="region" class="active">Region</button>
                    <button type="button" data-tab="province" disabled>Province</button>
                    <button type="button" data-tab="city" disabled>City</button>
                    <button type="button" data-tab="barangay" disabled>Barangay</button>
                  </div>
                  <div class="ph-list" id="phList"></div>
                </div>
              </div>
              <input type="hidden" name="region" id="ab_hidden_region" value="${escapeHtml(address?.region||'')}">
              <input type="hidden" name="province" id="ab_hidden_province" value="${escapeHtml(address?.province||'')}">
              <input type="hidden" name="city" id="ab_hidden_city" value="${escapeHtml(address?.city||'')}">
              <input type="hidden" name="barangay" id="ab_hidden_barangay" value="${escapeHtml(address?.barangay||'')}">
            </div>
            <div class="full">
              <input id="ab_postal" name="postal_code" placeholder="Postal Code" value="${escapeHtml(address?.postal_code||'')}">
            </div>
            <div class="full">
              <input id="ab_line" name="address_line" placeholder="Street Name, Building, House No." value="${escapeHtml(address?.address_line||'')}">
            </div>
            <div class="full">
              <label>Label As</label>
              <div class="label-pills" id="ab_labels">
                <button type="button" data-val="Home" class="label-pill ${label==='Home'?'active':''}">Home</button>
                <button type="button" data-val="Work" class="label-pill ${label==='Work'?'active':''}">Work</button>
              </div>
              <input type="hidden" name="label" id="ab_label" value="${label}">
            </div>
            <div style="margin-top:6px;">
              <div class="checkline">
                <input type="checkbox" name="is_default" value="1" ${address?.is_default==1 ? 'checked':''}>
                <span>Set as Default Address</span>
              </div>
            </div>
          </div>
          <footer class="ab-actions">
            <button type="button" class="link-cancel" id="abCancel">Cancel</button>
            <button type="submit" class="btn-accent">Submit</button>
          </footer>
          <input type="hidden" name="action" value="${isEdit? 'update':'create'}">
          ${isEdit? `<input type="hidden" name="address_id" value="${address.address_id}">`:''}
        </form>
      </div>`;
    modal.innerHTML = html;
    modal.style.display = 'flex';

    // Label selection
    const labels = $$('#ab_labels .label-pill', modal);
    labels.forEach(btn => btn.addEventListener('click', () => {
      labels.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      $('#ab_label', modal).value = btn.dataset.val;
    }));

    // Close
    $('#abCancel', modal).addEventListener('click', closeModal);
    modal.addEventListener('click', (e)=>{ if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', escCloseOnce);

    // Init PH picker (Region > Province > City > Barangay)
    initPhPicker(modal);

    // Submit
    $('#abForm', modal).addEventListener('submit', async (e)=>{
      e.preventDefault();
      const form = e.currentTarget;
      const fd = new FormData(form);
      // Ensure fields are present from picker
      fd.set('region', $('#ab_hidden_region', modal)?.value || '');
      fd.set('province', $('#ab_hidden_province', modal)?.value || '');
      fd.set('city', $('#ab_hidden_city', modal)?.value || '');
      fd.set('barangay', $('#ab_hidden_barangay', modal)?.value || '');
      try{
        const res = await fetch(API, { method:'POST', body: fd });
        const data = await res.json();
        if (!res.ok || data.error){ throw new Error(data.error || 'Request failed'); }
        closeModal();
        showToast(isEdit? 'Address updated' : 'Address added');
        loadAddresses();
      }catch(err){ showToast(err.message||'Error saving address', true); }
    });
  }
  function closeModal(){ modal.style.display = 'none'; modal.innerHTML = ''; document.removeEventListener('keydown', escCloseOnce); }
  function escCloseOnce(e){ if (e.key === 'Escape') closeModal(); }
  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }

  // Simple PH location dataset (sample). Replace/extend as needed.
  const PH_LOC = {
    regions: {
      'Metro Manila': {
        // Treat "Metro Manila" as the province, then cities beneath it
        'Metro Manila': {
          'Manila': ['Tondo', 'Ermita', 'Malate'],
          'Quezon City': ['Batasan Hills', 'Commonwealth', 'Payatas'],
          'Makati': ['Bel-Air', 'Poblacion'],
          'Pasig': ['Ortigas Center', 'Manggahan'],
          'Taguig': ['Lower Bicutan', 'Western Bicutan']
        }
      },
      'North Luzon': {
        'Ilocos Norte': {
          'Laoag City': ['Barangay 1', 'Barangay 2'],
          'Batac': ['Ablan Central', 'Barbar']
        },
        'Pangasinan': {
          'Dagupan': ['Barangay I', 'Calmay'],
          'Urdaneta': ['Anonas', 'Bayaoas']
        },
        'Benguet': {
          'Baguio City': ['Asin Road', 'Loakan']
        }
      },
      'South Luzon': {
        'Laguna': {
          'Calamba': ['Canlubang', 'Pansol'],
          'Santa Rosa': ['Balibago', 'Dila'],
          'San Pedro': ['Magsaysay', 'United Bayanihan']
        },
        'Batangas': {
          'Lipa City': ['Banaybanay', 'Sico'],
          'Batangas City': ['Alangilan', 'Pallocan']
        },
        'Cavite': {
          'Bacoor': ['Molino I', 'Talaba'],
          'Dasmariñas': ['Salitran', 'Langkaan']
        }
      },
      'Visayas': {
        'Cebu': {
          'Cebu City': ['Lahug', 'Mabolo'],
          'Mandaue City': ['Banilad', 'Subangdaku']
        },
        'Iloilo': {
          'Iloilo City': ['Jaro', 'La Paz']
        },
        'Negros Occidental': {
          'Bacolod': ['Mansilingan', 'Tangub']
        }
      },
      'Mindanao': {
        'Zamboanga del Norte': {
          'Dipolog': ['Sta. Filomena','Central'],
          'Dapitan': ['Potol','Bagting']
        },
        'Zamboanga del Sur': {
          'Pagadian': ['Balangasan','San Pedro'],
          'Zamboanga City': ['Tetuan','Putik']
        },
        'Zamboanga Sibugay': {
          'Ipil': ['Pangi','Sanito'],
          'Kabasalan': ['Tiayon','Salipyasin']
        },
        'Misamis Occidental': {
          'Ozamiz': ['Bañadero','Tinago'],
          'Oroquieta': ['Lower Lamac','Loyal']
        },
        'Misamis Oriental': {
          'Cagayan de Oro': ['Carmen','Lumbia'],
          'Gingoog': ['Tupsan','Barangay 1']
        },
        'Lanao del Norte': {
          'Iligan': ['Acmac','Tibanga'],
          'Tubod': ['Poblacion','Malingao']
        },
        'Bukidnon': {
          'Malaybalay': ['Sumpong','Barangay 9'],
          'Valencia': ['Poblacion','Bagontaas']
        },
        'Davao de Oro': {
          'Nabunturan': ['Poblacion','Magsaysay'],
          'Monkayo': ['Poblacion','San Jose']
        },
        'Davao del Norte': {
          'Tagum': ['Magugpo East','Poblacion'],
          'Panabo': ['Gredu','San Francisco']
        },
        'Davao del Sur': {
          'Digos': ['Zone 1','Zone 2']
        },
        'Davao Oriental': {
          'Mati': ['Central','Sainz']
        },
        'Davao Occidental': {
          'Malita': ['Bulila','Poblacion']
        },
        'South Cotabato': {
          'Koronadal': ['Zone 1','Zone 2'],
          'General Santos': ['Lagao','Apopong']
        },
        'Cotabato': {
          'Kidapawan': ['Poblacion','Sudapin'],
          'Midsayap': ['Poblacion 1','Bual Norte']
        },
        'Sultan Kudarat': {
          'Isulan': ['Kalawag 1','Kalawag 2'],
          'Tacurong': ['Poblacion','New Isabela']
        },
        'Sarangani': {
          'Alabel': ['Alegria','Maribulan'],
          'Glan': ['Poblacion','Tango']
        },
        'Agusan del Norte': {
          'Butuan': ['Ampayon','Baan'],
          'Cabadbaran': ['Comagascas','Poblacion']
        },
        'Agusan del Sur': {
          'Prosperidad': ['Patin-ay','Poblacion'],
          'San Francisco': ['Poblacion','Hubang']
        },
        'Surigao del Norte': {
          'Surigao City': ['Washington','San Juan'],
          'Dapa': ['Poblacion','Union']
        },
        'Surigao del Sur': {
          'Tandag City': ['Awasian','Mabua'],
          'Bislig City': ['Mangagoy','Poblacion']
        },
        'Dinagat Islands': {
          'San Jose': ['Don Ruben','Justiniana'],
          'Basilisa': ['Iberica','Poblacion']
        },
        'Basilan': {
          'Lamitan': ['Maligaya','Lukbuton'],
          'Isabela City': ['Carbon','Santa Barbara']
        },
        'Sulu': {
          'Jolo': ['Asturias','Busbus'],
          'Patikul': ['Bangkal','Danag']
        },
        'Tawi-Tawi': {
          'Bongao': ['Poblacion','Sanga-Sanga'],
          'Sitangkai': ['Datu Baguinda Putih','Tongbangkaw']
        },
        'Lanao del Sur': {
          'Marawi': ['Bangon','Lokesadato'],
          'Wao': ['Poblacion','Park Area']
        },
        'Maguindanao del Norte': {
          'Datu Odin Sinsuat': ['Tamontaka','Dalican'],
          'Parang': ['Sarmiento','Making']
        },
        'Maguindanao del Sur': {
          'Buluan': ['Poblacion','Paitan'],
          'Shariff Aguak': ['Mother Barangay','Poblacion']
        },
        'Camiguin': {
          'Mambajao': ['Agoho','Balbagon'],
          'Mahinog': ['Benoni','Poblacion']
        }
      }
    }
  };

  async function initPhPicker(root){
    const wrap = root.querySelector('#phPicker');
    if (!wrap) return;
    const input = wrap.querySelector('#ab_region');
    const panel = wrap.querySelector('.ph-panel');
    const list = wrap.querySelector('#phList');
    const tabs = Array.from(wrap.querySelectorAll('.ph-tabs button'));
    const state = {
      region: $('#ab_hidden_region', root)?.value || '',
      province: $('#ab_hidden_province', root)?.value || '',
      city: $('#ab_hidden_city', root)?.value || '',
      barangay: $('#ab_hidden_barangay', root)?.value || ''
    };
    const updateInput = () => {
      input.value = [state.region, state.province, state.city, state.barangay].filter(Boolean).join(', ');
    };
    updateInput();
    function open(){ panel.hidden = false; render('region'); document.addEventListener('click', onDoc); }
    function close(){ panel.hidden = true; document.removeEventListener('click', onDoc); }
    function onDoc(e){
      // If the click originated from within the picker (even if the clicked element
      // was removed during render), do not close the panel.
      const path = typeof e.composedPath === 'function' ? e.composedPath() : [];
      if (wrap.contains(e.target) || (Array.isArray(path) && path.includes(wrap))) return;
      close();
    }
    wrap.querySelector('.ph-toggle').addEventListener('click', (e)=>{ e.stopPropagation(); panel.hidden? open(): close(); });
    input.addEventListener('click', (e)=>{ e.stopPropagation(); panel.hidden? open(): close(); });

    function setActive(tab){ tabs.forEach(b=>{ b.classList.toggle('active', b.dataset.tab===tab); }); }
    function enable(tab){ const b = tabs.find(x=>x.dataset.tab===tab); if (b){ b.disabled = false; } }

    function render(tab){
      setActive(tab);
      list.innerHTML = '';
      if (tab==='region'){
        Object.keys(PH_LOC.regions).forEach(name=>{
          const btn = document.createElement('button');
          btn.className='ph-item'; btn.type='button'; btn.textContent=name;
          btn.onclick = ()=>{ state.region = name; state.province=''; state.city=''; state.barangay=''; enable('province'); render('province'); updateInput(); $('#ab_hidden_region', root).value = name; };
          list.appendChild(btn);
        });
      }
      if (tab==='province'){
        const obj = PH_LOC.regions[state.region] || {};
        Object.keys(obj).forEach(name=>{
          const btn = document.createElement('button');
          btn.className='ph-item'; btn.type='button'; btn.textContent=name;
          btn.onclick = ()=>{ state.province = name; state.city=''; state.barangay=''; enable('city'); render('city'); updateInput(); $('#ab_hidden_province', root).value = name; };
          list.appendChild(btn);
        });
      }
      if (tab==='city'){
        const provData = PH_LOC.regions[state.region]?.[state.province] || {};
        const cityNames = Array.isArray(provData) ? provData : Object.keys(provData);
        cityNames.forEach(name=>{
          const btn = document.createElement('button'); btn.className='ph-item'; btn.type='button'; btn.textContent=name;
          btn.onclick = ()=>{ state.city = name; state.barangay=''; enable('barangay'); render('barangay'); updateInput(); $('#ab_hidden_city', root).value = name; };
          list.appendChild(btn);
        });
      }
      if (tab==='barangay'){
        const arr = PH_LOC.regions[state.region]?.[state.province] || [];
        const brgys = Array.isArray(arr) ? arr : (arr[state.city] || []);
        brgys.forEach(name=>{
          const btn = document.createElement('button'); btn.className='ph-item'; btn.type='button'; btn.textContent=name;
          btn.onclick = ()=>{
            state.barangay = name;
            updateInput();
            $('#ab_hidden_barangay', root).value = name;
            close();
          };
          list.appendChild(btn);
        });
      }
    }

    // Tab clicks
    tabs.forEach(b=> b.addEventListener('click', ()=>{ if (!b.disabled) render(b.dataset.tab); }));

    // Prefill navigation
    if (state.region){ enable('province'); }
    if (state.province){ enable('city'); }
    if (state.city){ enable('barangay'); }
  }

  // Load + render addresses
  async function loadAddresses(){
    const empty = $('#addressEmpty');
    const list = $('#addressList');
    if (!list) return;
    try{
      const res = await fetch(`${API}?action=list`);
      const items = await res.json();
      if (!Array.isArray(items) || items.length === 0){
        if (empty) empty.style.display = 'flex';
        list.innerHTML = '';
        return;
      }
      if (empty) empty.style.display = 'none';
      list.classList.add('rows');
      const canDelete = items.length > 1;
      list.innerHTML = items.map(a => {
        const rightActions = [
          '<button class="link-btn edit">Edit</button>',
          a.is_default==1 ? '' : '<button class="btn-outline set-default">Set as default</button>',
          (!a.is_default && canDelete) ? '<button class="link-btn danger delete">Delete</button>' : ''
        ].filter(Boolean).join(' ');
        return `
        <div class="address-row" data-id="${a.address_id}">
          <div class="addr-left">
            <div class="name-line">
              <span class="name">${escapeHtml(a.fullname||'')}</span>
              <span class="divider">|</span>
              <span class="phone">${escapeHtml(a.phone||'')}</span>
            </div>
            <div class="addr-lines">${escapeHtml(a.address_line||'')}${a.barangay? ', '+escapeHtml(a.barangay):''}${a.city? ', '+escapeHtml(a.city):''}${a.province? ', '+escapeHtml(a.province):''}${a.postal_code? ', '+escapeHtml(a.postal_code):''}
              ${a.label? ` <span style="margin-left:6px;color:#6b8897;font-weight:600;">• ${escapeHtml(a.label)}</span>`:''}
            </div>
            ${a.is_default==1 ? '<span class="badge">Default</span>' : ''}
          </div>
          <div class="addr-actions">
            ${rightActions}
          </div>
        </div>`;
      }).join('');
    }catch(err){
      showToast('Failed to load addresses', true);
    }
  }

  // Event delegation for list actions
  const listEl = $('#addressList');
  if (listEl){
    listEl.addEventListener('click', async (e)=>{
      const row = e.target.closest('.address-row');
      if (!row) return;
      const id = row.getAttribute('data-id');
      if (e.target.classList.contains('edit')){
        // fetch current list to find this address
        try{
          const res = await fetch(`${API}?action=list`);
          const items = await res.json();
          const a = items.find(x => String(x.address_id) === String(id));
          if (a) openAddressModal(a);
        }catch(err){ showToast('Unable to edit address', true); }
      }
      if (e.target.classList.contains('delete')){
        if (!confirm('Delete this address?')) return;
        const fd = new FormData(); fd.append('action','delete'); fd.append('address_id', id);
        try{
          const res = await fetch(API, { method:'POST', body: fd });
          const data = await res.json();
          if (data?.ok){ showToast('Address deleted'); loadAddresses(); } else { throw new Error(data.error||'Failed'); }
        }catch(err){ showToast('Delete failed', true); }
      }
    });

    listEl.addEventListener('click', async (e)=>{
      if (e.target.classList.contains('set-default')){
        const row = e.target.closest('.address-row');
        const id = row?.getAttribute('data-id');
        if (!id) return;
        const fd = new FormData(); fd.append('action','set_default'); fd.append('address_id', id);
        try{
          const res = await fetch(API, { method:'POST', body: fd });
          const data = await res.json();
          if (data?.ok){ showToast('Default address updated'); loadAddresses(); }
        }catch(err){ showToast('Failed to update default', true); }
      }
    });
  }

  // Add Address button
  const addBtn = $('#addAddressBtn');
  if (addBtn){ addBtn.addEventListener('click', ()=> openAddressModal()); }

  // Honor URL hash on load (e.g., #purchases or #purchases:to_pay)
  const rawHash = (location.hash||'').replace('#','');
  const [initialSection, initialSub] = rawHash.split(':');
  if (sections[initialSection]){
    showSection(initialSection);
    // If a purchases sub-tab is indicated (e.g., to_pay), activate it
    if (initialSection === 'purchases' && initialSub){
      // Defer until purchases UI initializes
      setTimeout(()=>{
        const tabs = $$('.p-head .tabs a');
        tabs.forEach(x=>x.classList.remove('active'));
        const target = document.querySelector(`.p-head .tabs a[data-tab="${initialSub}"]`);
        if (target){ target.classList.add('active'); }
        renderPurchases(initialSub);
      }, 0);
    }
  }

  // Ensure purchases render after navigation (e.g., from checkout redirect)
  window.addEventListener('pageshow', async () => {
    if (sections.purchases && sections.purchases.style.display !== 'none'){
      const current = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
      renderPurchases(current);
    }
  });

  // On initial load, keep current section and pre-load addresses if visible
  if (sections.addresses && sections.addresses.style.display !== 'none'){
    loadAddresses();
  }

  // Purchases helpers (sessionStorage-backed)
  const ORDERS_KEY = () => `purrfectOrders:${window.PURR_USER_ID||'anon'}`;
  function getOrders(){ try { return JSON.parse(sessionStorage.getItem(ORDERS_KEY())||'[]'); } catch(e){ return []; } }
  function saveOrders(arr){ sessionStorage.setItem(ORDERS_KEY(), JSON.stringify(arr||[])); }
  function money(n){ return '\u20b1'+Number(n||0).toFixed(2); }
  // Track which orders have been rated so we can swap Rate → Buy Again
  function getRatedMap(){ try { return JSON.parse(localStorage.getItem('pp_rated_orders')||'{}'); } catch(e){ return {}; } }
  function saveRatedMap(map){ try { localStorage.setItem('pp_rated_orders', JSON.stringify(map||{})); } catch(e){} }
  // Store per-product reviews for Product Ratings section
  function getProductReviews(){ try { return JSON.parse(localStorage.getItem('pp_product_reviews')||'[]'); } catch(e){ return []; } }
  function saveProductReviews(list){ try { localStorage.setItem('pp_product_reviews', JSON.stringify(Array.isArray(list)? list : [])); } catch(e){} }
  function normalizeStatus(s){
    const raw = String(s||'to_pay').toLowerCase().trim();
    // replace any non-letters with underscore, then collapse repeats
    let t = raw.replace(/[^a-z]/g,'_').replace(/_+/g,'_');
    // map common variants (aligned with backend)
    if (t === 'shipped' || t === 'ship' || t === 'shipping') return 'to_ship';
    if (t === 'processing' || t === 'received' || t === 'receive') return 'to_receive';
    if (t === 'delivered' || t === 'delivery') return 'completed';
    if (t === 'to_ship' || t === 'toship') return 'to_ship';
    if (t === 'to_receive' || t === 'toreceive') return 'to_receive';
    if (t === 'completed' || t === 'complete' || t === 'done') return 'completed';
    if (t === 'cancelled' || t === 'canceled' || t === 'cancel') return 'cancelled';
    if (t === 'to_pay' || t === 'topay') return 'to_pay';
    return t || 'to_pay';
  }
  function initPurchases(){
    const tabs = $$('.p-head .tabs a');
    tabs.forEach(a=>a.addEventListener('click', async (e)=>{
      e.preventDefault();
      tabs.forEach(x=>x.classList.remove('active'));
      a.classList.add('active');
      const t = a.dataset.tab;
      await renderPurchases(t);
    }));
    const search = $('#p-search');
    if (search){ search.addEventListener('input', async ()=>{
      const current = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
      await renderPurchases(current);
    }); }
  }
  async function syncOrdersFromServer(){
    try{
      const res = await fetch(`../crud/crud.php?action=get_orders&user_id=${encodeURIComponent(window.PURR_USER_ID||'')}&_=${Date.now()}`);
      const arr = await res.json();
      if (!Array.isArray(arr)) return false;
      const byId = {};
      arr.forEach(o=>{ if (o && o.order_id!=null) byId[String(o.order_id)] = o.status; });
      const local = getOrders();
      let changed = false;
      for (const o of local){
        if (o.order_id != null){
          const s = byId[String(o.order_id)];
          if (s && o.status !== s){ o.status = s; changed = true; }
        }
      }
      if (changed) saveOrders(local);
      return changed;
    }catch(e){ return false; }
  }
  async function renderPurchases(tab){
    const wrap = $('#p-orders');
    if (!wrap) return;
    // Show search only on "All" tab
    const searchWrap = document.querySelector('.p-search');
    const searchInput = document.getElementById('p-search');
    if (searchWrap){ searchWrap.style.display = (tab==='all') ? '' : 'none'; }
    if (tab !== 'all' && searchInput){ searchInput.value = ''; }
    const q = (tab==='all' && searchInput ? (searchInput.value||'').toLowerCase() : '');

    // Always fetch latest orders for this user, then filter by tab locally
    let orders = [];
    try{
      // Ask server to pre-filter by status when not on 'all'
      const base = `../crud/crud.php?action=get_orders&user_id=${encodeURIComponent(String(window.PURR_USER_ID||''))}`;
      const url = base + (tab && tab!=='all' ? `&status=${encodeURIComponent(tab)}` : '') + `&_=${Date.now()}`;
      let res = await fetch(url);
      let arr = await res.json();
      // If nothing returned, retry without user filter (fallback) then filter client-side
      if (!Array.isArray(arr) || arr.length === 0){
        res = await fetch(`../crud/crud.php?action=get_orders&_=${Date.now()}`);
        arr = await res.json();
      }
      if (Array.isArray(arr)){
        orders = arr
          .filter(o => String(o.user_id||'') === String(window.PURR_USER_ID||''))
          .map(o=>({
          order_id: o.order_id,
          date: o.date || o.created_at || Date.now(),
          total: Number(o.total||0),
          status: normalizeStatus(o.status),
          items: Array.isArray(o.items)? o.items.map(it=>({
            name: it.product_name || it.name || '',
            quantity: Number(it.quantity||1),
            price: Number(it.price||0),
            image: it.image_url || it.image || ''
          })) : []
        }));
        // Cache for fallback only
        saveOrders(orders);
      }
    }catch(_){
      const cached = getOrders();
      orders = Array.isArray(cached)? cached : [];
    }

    // Client-side status filter
    if (tab && tab !== 'all') orders = orders.filter(o=>o.status===tab);
    if (q) orders = orders.filter(o => (o.items||[]).some(it => (it.name||'').toLowerCase().includes(q)) );
    if (!orders.length){ wrap.innerHTML = '<div class="address-empty" style="display:flex"><div class="empty-inner"><p class="empty-main">No orders yet.</p></div></div>'; return; }

    const statusLabel = (s)=>({to_pay:'To Pay', to_ship:'To Ship', to_receive:'To Receive', completed:'Completed', cancelled:'Cancelled'})[s]||'—';
    const ratedMap = getRatedMap();

    wrap.innerHTML = orders.map((o,idx)=>{
      if (o.status === 'to_pay'){
        return `
        <div class="order pay" style="border:1px solid #e9eef2;border-radius:8px;margin-bottom:12px;">
          <div class="shopbar" style="display:flex;justify-content:flex-end;align-items:center;padding:10px 12px;border-bottom:1px dashed #e9eef2;background:#fff;">
            <div class="status tag to-pay" style="color:#c92a2a;font-weight:700;">TO PAY</div>
          </div>
          <div class="order-items" style="padding:10px 12px;">
            ${(o.items||[]).map(it=>`
              <div class="oi" style="display:flex;gap:10px;align-items:center;padding:8px 0;">
                <img src="${it.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" alt="" style="width:64px;height:64px;border:1px solid #eee;border-radius:6px;object-fit:cover;">
                <div style="flex:1">
                  <div class="name" style="font-weight:700;">${escapeHtml(it.name)}</div>
                  <div class=\"meta\" style=\"color:#6b8897;\">x${it.quantity||1}</div>
                </div>
                <div class="price" style="color:#333;">${money((it.price||0))}</div>
              </div>
            `).join('')}
          </div>
          <div class="order-f pay" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-top:1px solid #e9eef2;background:#fff;">
            <div class="amount">Amount Payable: <strong class="aprice">${money(o.total||0)}</strong></div>
            <div class="actions" style="display:flex;gap:8px;">
              <button class="btn small" disabled style="opacity:.7;cursor:not-allowed;padding:10px 16px;border:1px solid #e0e0e0;background:#f7f7f7;border-radius:6px;font-size:14px;">Pending</button>
              <button class="btn outline danger cancel-order" data-key="${o.date}" style="padding:10px 16px;border:1px solid #ff6b6b;color:#c92a2a;background:#fff;border-radius:6px;font-size:14px;">Cancel Order</button>
            </div>
          </div>
        </div>`;
      }
      else if (o.status === 'to_receive'){
        // Shopee-like "To Receive" card
        return `
        <div class="order to-receive" style="border:1px solid #e9eef2;border-radius:8px;margin-bottom:12px;background:#fff;">
          <div class="tr-head" style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #f1f3f5;">
            <div style="display:flex;align-items:center;gap:8px;"></div>
            <div style="display:flex;align-items:center;gap:10px;">
              <span style="color:#2f9e44;font-weight:600;">Parcel has been delivered</span>
              <span style="color:#c92a2a;font-weight:700;">TO RECEIVE</span>
            </div>
          </div>
          <div class="tr-body" style="padding:10px 14px;">
            ${(o.items||[]).map(it=>`
              <div class="oi" style="display:flex;gap:12px;align-items:center;padding:10px 0;">
                <img src="${it.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" alt="" style="width:72px;height:72px;border:1px solid #eee;border-radius:6px;object-fit:cover;">
                <div style="flex:1;min-width:0;">
                  <div class="name" style="font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(it.name||'')}</div>
                  <div class="meta" style="color:#6b8897;font-size:12px;">x${it.quantity||1}</div>
                </div>
                <div class="price" style="color:#333;font-weight:600;">${money(it.price||0)}</div>
              </div>
            `).join('')}
          </div>
          <div class="tr-foot" style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-top:1px solid #f1f3f5;">
            <div style="color:#6b8897;">Order Total: <strong style="color:#111;">${money(o.total||0)}</strong></div>
            <div class="actions" style="display:flex;gap:8px;">
              <button class="btn outline danger mark-received" data-oid="${o.order_id}" style="padding:10px 16px;border:1px solid #ff6b6b;color:#c92a2a;background:#fff;border-radius:6px;font-size:14px;">Order Received</button>
            </div>
          </div>
        </div>`;
      }
      // Default card (to_ship, completed, cancelled)
      const isCompleted = o.status === 'completed';
      const rated = !!ratedMap[String(o.order_id||o.date)];
      return `
      <div class="order" style="border:1px solid #e9eef2;border-radius:8px;margin-bottom:12px;">
        <div class="order-h" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px dashed #e9eef2;background:#fff;">
          <div><strong>Order #${idx+1}</strong> <span style="color:#9ab0bd;margin-left:8px">${new Date(o.date||Date.now()).toLocaleString()}</span></div>
          <div class="status">${statusLabel(o.status)}</div>
        </div>
        <div class="order-items" style="padding:10px 12px;">
          ${(o.items||[]).map(it=>`
            <div class="oi" style="display:flex;gap:10px;align-items:center;padding:8px 0;">
              <img src="${it.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" alt="" style="width:64px;height:64px;border:1px solid #eee;border-radius:6px;object-fit:cover;">
              <div style="flex:1">
                <div class="name" style="font-weight:700;">${escapeHtml(it.name)}</div>
                <div class="meta" style="color:#6b8897;">Qty: ${it.quantity||1} • Price: ${money(it.price||0)}</div>
              </div>
            </div>
          `).join('')}
        </div>
        <div class="order-f" style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-top:1px solid #e9eef2;background:#fff;">
          <div class="order-total">Order Total: <strong>${money(o.total||0)}</strong></div>
          <div class="actions">
            ${isCompleted && !rated
              ? `<button class="btn rate-btn" data-key="${o.order_id||o.date}" style="padding:6px 12px;border-radius:6px;border:1px solid #f97316;background:#f97316;color:#fff;">Rate</button>`
              : `<button class="buy-again" onclick="location.href='../HTML/product-detail.php?name=${encodeURIComponent(((o.items||[])[0]||{}).name||'')}'" style="padding:6px 10px;border:1px solid #1a73e8;color:#1a73e8;background:#fff;border-radius:6px;">Buy Again</button>`}
          </div>
        </div>
      </div>`;
    }).join('');

    // Wire up "Order Received" to open modal confirmation
    wrap.querySelectorAll('.mark-received').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const oid = parseInt(btn.getAttribute('data-oid')||'0',10);
        openReceiveModal(oid);
      });
    });

    // Wire up cancel buttons
    wrap.querySelectorAll('.cancel-order').forEach(btn=>{
      btn.addEventListener('click', ()=> openCancelModal(btn.getAttribute('data-key')) );
    });
    // Wire up Rate buttons (Completed tab)
    wrap.querySelectorAll('.rate-btn').forEach(btn=>{
      btn.addEventListener('click', ()=> openRateModal(btn.getAttribute('data-key')) );
    });
    updatePurchaseTabCounts();
  }

  // --- Cancel Order Modal (Shopee-like) ---
  function openCancelModal(orderKey){
    // Build overlay
    let modal = document.getElementById('pcModal');
    if (!modal){
      modal = document.createElement('div');
      modal.id = 'pcModal';
      modal.className = 'pc-modal';
      modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:99999;';
      document.body.appendChild(modal);
    }
    const panelStyle = 'background:#fff;border-radius:12px;width:420px;max-width:95vw;padding:16px 16px 12px;box-shadow:0 14px 34px rgba(2,8,23,.18);font-family:inherit;border:1px solid #e9eef2;';
    const noteStyle = 'background:#fff7e6;border:1px solid #ffe0b2;color:#8d6e63;padding:10px 12px;border-radius:6px;font-size:13px;margin:8px 0 12px;';
    const btnBase = 'display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:all .15s ease;box-shadow:0 2px 6px rgba(0,0,0,.08);';

    const reasons = [
      'Need to change delivery address',
      'Found cheaper elsewhere',
      "Don't want to buy anymore",
      'Others'
    ];

    modal.innerHTML = `
      <div class="panel" style="${panelStyle}">
        <h3 style="margin:0 0 8px;">Select Cancellation Reason</h3>
        <div class="note" style="${noteStyle}">Please select a cancellation reason. Please take note that this will cancel all items in the order and the action cannot be undone.</div>
        <form id="pcForm">
          <div style="display:grid;gap:10px;margin:10px 0 16px;">
            ${reasons.map((txt)=>`
              <label style=\"display:flex;gap:8px;align-items:flex-start;\">
                <input type=\"radio\" name=\"reason\" value=\"${txt.replace(/\"/g,'&quot;')}\">
                <span>${txt}</span>
              </label>
            `).join('')}
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;">
            <button type="button" id="pcNotNow" style="${btnBase}background:#f7f7f7;border:1px solid #dfe3e6;color:#444;">NOT NOW</button>
            <button type="submit" id="pcConfirm" style="${btnBase}background:#fff;border:1px solid #ff6b6b;color:#c92a2a;">CANCEL ORDER</button>
          </div>
        </form>
      </div>`;

    modal.style.display = 'flex';

    const close = ()=>{ modal.remove(); };
    modal.addEventListener('click', (e)=>{ if (e.target===modal) close(); });
    modal.querySelector('#pcNotNow').addEventListener('click', close);

    // Disable confirm until a reason is selected (like Shopee)
    const confirmBtn = modal.querySelector('#pcConfirm');
    if (confirmBtn){ confirmBtn.disabled = true; confirmBtn.style.opacity = '0.6'; confirmBtn.style.cursor = 'not-allowed'; }
    modal.querySelectorAll('input[name="reason"]').forEach(r => r.addEventListener('change', ()=>{
      if (confirmBtn){ confirmBtn.disabled = false; confirmBtn.style.opacity = '1'; confirmBtn.style.cursor = 'pointer'; }
    }));

    modal.querySelector('#pcForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const orders = getOrders();
      const idx = orders.findIndex(o=> String(o.date) === String(orderKey));
      let oid = null;
      if (idx>-1){
        orders[idx].status = 'cancelled';
        oid = orders[idx].order_id || null;
        saveOrders(orders);
      }
      // Tell server so Admin page reflects the cancellation and restock executes.
      // If we don't have order_id saved locally, try to locate it from the server by matching.
      try {
        if (!oid){
          const uid = String(window.PURR_USER_ID || '');
          const res = await fetch('../crud/crud.php?action=get_orders&user_id=' + encodeURIComponent(uid) + '&status=to_pay&_=' + Date.now(), { credentials:'same-origin' });
          const list = await res.json();
          const arr = Array.isArray(list) ? list : [];
          const cand = arr.find(o => Math.abs(Number(o.total||0) - Number((orders[idx]||{}).total||0)) < 0.01);
          if (cand && cand.order_id) { oid = cand.order_id; }
        }
        if (oid){
          await fetch('../crud/crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_order_status', order_id: parseInt(oid,10), status: 'cancelled' }),
            keepalive: true,
            credentials: 'same-origin'
          });
        }
      } catch(_){}
      close();
      updatePurchaseTabCounts();
      const current = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
      await renderPurchases(current);
    });
  }

  // --- Receive Confirmation Modal ---
  function openReceiveModal(orderId){
    let modal = document.getElementById('prModal');
    if (!modal){
      modal = document.createElement('div');
      modal.id = 'prModal';
      modal.className = 'pc-modal';
      modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:99999;';
      document.body.appendChild(modal);
    }
    const panelStyle = 'background:#fff;border-radius:12px;width:420px;max-width:95vw;padding:16px 16px 12px;box-shadow:0 14px 34px rgba(2,8,23,.18);font-family:inherit;border:1px solid #e9eef2;';
    const noteStyle = 'background:#fff7e6;border:1px solid #ffe0b2;color:#8d6e63;padding:10px 12px;border-radius:6px;font-size:13px;margin:8px 0 12px;';
    const btnBase = 'display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;font-weight:700;font-size:13px;cursor:pointer;transition:all .15s ease;box-shadow:0 2px 6px rgba(0,0,0,.08);';
    modal.innerHTML = `
      <div class="panel" style="${panelStyle}">
        <h3 style="margin:0 0 8px;">Confirm Receipt</h3>
        <div class="note" style="${noteStyle}">Confirm receipt after you've checked the received items.</div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;">
          <button type="button" id="prNotNow" style="${btnBase}background:#f7f7f7;border:1px solid #dfe3e6;color:#444;">NOT NOW</button>
          <button type="button" id="prConfirm" style="${btnBase}background:#fff;border:1px solid #ff6b6b;color:#c92a2a;">ORDER RECEIVED</button>
        </div>
      </div>`;
    modal.style.display = 'flex';
    const close = ()=>{ const m = document.getElementById('prModal'); if (m) m.remove(); };
    modal.addEventListener('click', (e)=>{ if (e.target===modal) close(); });
    document.getElementById('prNotNow').addEventListener('click', close);
    document.getElementById('prConfirm').addEventListener('click', async ()=>{
      const oid = parseInt(orderId||0,10);
      // Update local cache optimistically
      const local = getOrders();
      let changed = false;
      for (const o of local){ if (parseInt(o.order_id||0,10) === oid){ o.status='completed'; changed = true; break; } }
      if (changed) saveOrders(local);
      updatePurchaseTabCounts();
      try{
        if (oid){
          await fetch('../crud/crud.php', {
            method:'POST',
            headers:{ 'Content-Type':'application/json' },
            credentials:'same-origin',
            keepalive:true,
            body: JSON.stringify({ action:'update_order_status', order_id: oid, status:'completed' })
          });
        }
      }catch(_){ }
      // Switch to Completed tab
      const tabs = document.querySelectorAll('.p-head .tabs a');
      tabs.forEach(x=>x.classList.remove('active'));
      const t = document.querySelector('.p-head .tabs a[data-tab="completed"]');
      if (t){ t.classList.add('active'); }
      await renderPurchases('completed');
      close();
    });
  }

  // --- Product Rating Modal (Shopee-like) ---
  function openRateModal(orderKey){
    const orders = getOrders();
    const o = orders.find(x=> String(x.order_id||x.date) === String(orderKey));
    const firstItem = (o&&o.items&&o.items[0]) || {};
    let modal = document.getElementById('rateModal');
    if (!modal){
      modal = document.createElement('div');
      modal.id = 'rateModal';
      modal.className = 'pc-modal';
      modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;z-index:99999;';
      document.body.appendChild(modal);
    }
    const panelStyle = 'background:#fff;border-radius:12px;width:520px;max-width:95vw;padding:18px 18px 14px;box-shadow:0 14px 34px rgba(2,8,23,.18);border:1px solid #e9eef2;';
    const noteStyle = 'background:#fff7ed;border:1px solid #fed7aa;color:#8d6e63;padding:8px 10px;border-radius:6px;font-size:13px;margin:4px 0 10px;';
    const starBase = 'cursor:pointer;font-size:22px;color:#e5e7eb;margin-right:4px;';
    modal.innerHTML = `
      <div class="panel" style="${panelStyle}">
        <h3 style="margin:0 0 6px;">Rate Product</h3>
        <div style="${noteStyle}">Share your feedback about this product to help other buyers.</div>
        <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px;">
          <img src="${firstItem.image||'../HTML/images/catbed.jpg'}" onerror="this.src='../HTML/images/catbed.jpg'" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;" alt="">
          <div style="flex:1;min-width:0;">
            <div style="font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(firstItem.name||'Order')}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px;">Qty: ${firstItem.quantity||1}</div>
          </div>
        </div>
        <form id="prForm">
          <div style="margin-bottom:10px;">
            <div style="font-size:13px;margin-bottom:4px;">Product Quality</div>
            <div id="prStars">
              ${[1,2,3,4,5].map(i=>`<i data-v="${i}" class="fa-solid fa-star" style="${starBase}"></i>`).join('')}
            </div>
          </div>
          <div style="margin-bottom:10px;">
            <div style="font-size:13px;margin-bottom:4px;">Review</div>
            <textarea id="prText" rows="4" style="width:100%;border-radius:8px;border:1px solid #d1d5db;padding:8px;font-size:13px;resize:vertical;" placeholder="Share more thoughts on the product to help other buyers."></textarea>
          </div>
          <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
            <button type="button" id="prCancel" style="padding:7px 14px;border-radius:8px;border:1px solid #d1d5db;background:#f9fafb;font-size:13px;cursor:pointer;">CANCEL</button>
            <button type="submit" id="prSubmit" style="padding:7px 16px;border-radius:8px;border:none;background:#f97316;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">SUBMIT</button>
          </div>
        </form>
      </div>`;
    modal.style.display = 'flex';

    const stars = modal.querySelectorAll('#prStars i');
    let current = 5;
    const paint = (val)=>{
      stars.forEach(s=>{
        const v = Number(s.dataset.v||0);
        s.style.color = v<=val ? '#f97316' : '#e5e7eb';
      });
    };
    paint(current);
    stars.forEach(s=> s.addEventListener('click', ()=>{ current = Number(s.dataset.v||5); paint(current); }));

    const close = ()=>{ const m = document.getElementById('rateModal'); if (m) m.remove(); };
    modal.addEventListener('click', (e)=>{ if (e.target===modal) close(); });
    modal.querySelector('#prCancel').addEventListener('click', close);
    modal.querySelector('#prForm').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const text = (modal.querySelector('#prText').value||'').trim();
      // mark order as rated so button becomes "Buy Again"
      const map = getRatedMap();
      map[String(orderKey)] = { rated:true, stars:current, text };
      saveRatedMap(map);
      // also save per-product review locally so Product Ratings can show it immediately
      const list = getProductReviews();
      const productName = (firstItem.name||'').toString();
      const key = productName.toLowerCase().trim();
      if (key){
        list.push({
          product: productName,   // original label
          key,                    // normalized key used for matching
          stars: current,
          text,
          user: (window.PURR_USER_NAME||'User'),
          ts: Date.now()
        });
        saveProductReviews(list);
      }

      // send rating to backend so it is saved in the database
      try {
        await fetch('../crud/crud.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({
            action: 'add_rating',
            product_name: productName,
            stars: current,
            text,
            user_id: window.PURR_USER_ID || null,
            username: window.PURR_USER_NAME || 'User'
          })
        });
      } catch (_) {
        // ignore network errors here; local state is already updated
      }

      close();
      const currentTab = document.querySelector('.p-head .tabs a.active')?.dataset.tab || 'all';
      renderPurchases(currentTab);
    });
  }
  // expose globally so inline onclick or listeners can use it
  window.openRateModal = openRateModal;

  function updatePurchaseTabCounts(){
    const orders = getOrders().map(o=> ({...o, status: (o.status||'to_pay')}));
    const counts = orders.reduce((acc,o)=>{ acc[o.status]=(acc[o.status]||0)+1; acc.all++; return acc; }, {all:0});
    const set = (tab, label, n)=>{
      const el = document.querySelector(`.p-head .tabs a[data-tab=\"${tab}\"]`);
      if (el) el.textContent = n>0 ? `${label} (${n})` : label;
    };
    set('all','All', counts.all||0);
    set('to_pay','To Pay', counts.to_pay||0);
    set('to_ship','To Ship', counts.to_ship||0);
    set('to_receive','To Receive', counts.to_receive||0);
    set('completed','Completed', counts.completed||0);
    set('cancelled','Cancelled', counts.cancelled||0);
  }

  // Phone number validation - only allow numbers
  const phoneInput = $('#phone');
  if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
      // Remove any non-digit characters except +
      e.target.value = e.target.value.replace(/[^0-9+]/g, '');
      // Limit to 11 digits (for PH format like 09XXXXXXXXX)
      if (e.target.value.length > 11) {
        e.target.value = e.target.value.slice(0, 11);
      }
    });
    phoneInput.addEventListener('keypress', (e) => {
      // Allow only numbers, +, backspace, delete, arrow keys
      const charCode = e.which || e.keyCode;
      const char = String.fromCharCode(charCode);
      if (!/[0-9+]/.test(char) && charCode !== 8 && charCode !== 46) {
        e.preventDefault();
      }
      // Prevent typing if already at 11 digits
      if (e.target.value.length >= 11 && charCode !== 8 && charCode !== 46) {
        e.preventDefault();
      }
    });
  }
})();
