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
  const showSection = (name) => {
    Object.entries(sections).forEach(([k, el]) => el && (el.style.display = (k===name? 'block':'none')));
  };
  const addrLink = $$('a[href="#addresses"], #link-addresses')[0];
  if (addrLink){ addrLink.addEventListener('click', (e)=>{ e.preventDefault(); showSection('addresses'); loadAddresses(); }); }
  const profLink = $$('a[href="#profile"], #link-profile')[0];
  if (profLink){ profLink.addEventListener('click', (e)=>{ e.preventDefault(); showSection('profile'); }); }
  const purLink = $('#link-purchases');
  if (purLink){ purLink.addEventListener('click', (e)=>{ e.preventDefault(); showSection('purchases'); }); }

  // Toast
  const toast = $('#toast') || Object.assign(document.createElement('div'), { id:'toast', className:'toast' });
  if (!toast.parentNode) document.body.appendChild(toast);
  function showToast(msg, isErr=false){
    toast.textContent = msg;
    toast.classList.toggle('error', !!isErr);
    toast.classList.add('show');
    setTimeout(()=> toast.classList.remove('show'), 2200);
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
    function onDoc(e){ if (!wrap.contains(e.target)) close(); }
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
          btn.onclick = ()=>{ state.barangay = name; updateInput(); $('#ab_hidden_barangay', root).value = name; close(); };
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
      list.innerHTML = items.map(a => `
        <div class="address-row" data-id="${a.address_id}">
          <div class="select">
            <input type="radio" name="defaultAddress" class="def-radio" value="${a.address_id}" ${a.is_default==1 ? 'checked':''}>
          </div>
          <div class="addr-left">
            <div class="name-line">
              <span class="name">${escapeHtml(a.fullname||'')}</span>
              <span class="divider">|</span>
              <span class="phone">${escapeHtml(a.phone||'')}</span>
              ${a.is_default==1 ? '<span class="badge">Default</span>' : ''}
            </div>
            <div class="addr-lines">${escapeHtml(a.address_line||'')}${a.barangay? ', '+escapeHtml(a.barangay):''}${a.city? ', '+escapeHtml(a.city):''}${a.province? ', '+escapeHtml(a.province):''}${a.postal_code? ', '+escapeHtml(a.postal_code):''}
              ${a.label? ` <span style="margin-left:6px;color:#6b8897;font-weight:600;">• ${escapeHtml(a.label)}</span>`:''}
            </div>
          </div>
          <div class="addr-actions">
            <button class="link-btn edit">Edit</button>
            <button class="link-btn danger delete">Delete</button>
          </div>
        </div>`).join('');
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

    listEl.addEventListener('change', async (e)=>{
      if (e.target.classList.contains('def-radio')){
        const id = e.target.value;
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

  // On initial load, keep current section and pre-load addresses if visible
  if (sections.addresses && sections.addresses.style.display !== 'none'){
    loadAddresses();
  }
})();
