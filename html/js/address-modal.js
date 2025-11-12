(function(){
  function esc(s){ const d=document.createElement('div'); d.textContent = s||''; return d.innerHTML; }
  function wireLabelPills(root){
    if(!root) return;
    const pills = root.querySelectorAll('.label-pill');
    const input = root.querySelector('input[name="label"]');
    pills.forEach(pill=>{
      pill.addEventListener('click', ()=>{
        pills.forEach(x=>x.classList.remove('active'));
        pill.classList.add('active');
        if(input) input.value = pill.getAttribute('data-value');
      });
    });
  }
  function getChecked(container, name){ return container.querySelector('input[name="'+name+'"]:checked'); }

  // Minimal PH location dataset and picker used by profile; shared here
  const PH_LOC = {
    regions: {
      'Metro Manila': { 'Metro Manila': { 'Manila': ['Tondo','Ermita','Malate'], 'Quezon City': ['Batasan Hills','Commonwealth','Payatas'], 'Makati': ['Bel-Air','Poblacion'], 'Pasig': ['Ortigas Center','Manggahan'], 'Taguig': ['Lower Bicutan','Western Bicutan'] } },
      'North Luzon': { 'Ilocos Norte': { 'Laoag City': ['Barangay 1','Barangay 2'], 'Batac': ['Ablan Central','Barbar'] }, 'Pangasinan': { 'Dagupan': ['Barangay I','Calmay'], 'Urdaneta': ['Anonas','Bayaoas'] }, 'Benguet': { 'Baguio City': ['Asin Road','Loakan'] } },
      'South Luzon': { 'Laguna': { 'Calamba': ['Canlubang','Pansol'], 'Santa Rosa': ['Balibago','Dila'], 'San Pedro': ['Magsaysay','United Bayanihan'] }, 'Batangas': { 'Lipa City': ['Banaybanay','Sico'], 'Batangas City': ['Alangilan','Pallocan'] }, 'Cavite': { 'Bacoor': ['Molino I','Talaba'], 'Dasmariñas': ['Salitran','Langkaan'] } },
      'Visayas': { 'Cebu': { 'Cebu City': ['Lahug','Mabolo'], 'Mandaue City': ['Banilad','Subangdaku'] }, 'Iloilo': { 'Iloilo City': ['Jaro','La Paz'] }, 'Negros Occidental': { 'Bacolod': ['Mansilingan','Tangub'] } },
      'Mindanao': { 'Zamboanga del Norte': { 'Dipolog': ['Sta. Filomena','Central'], 'Dapitan': ['Potol','Bagting'] }, 'Zamboanga del Sur': { 'Pagadian': ['Balangasan','San Pedro'], 'Zamboanga City': ['Tetuan','Putik'] }, 'Zamboanga Sibugay': { 'Ipil': ['Pangi','Sanito'], 'Kabasalan': ['Tiayon','Salipyasin'] }, 'Misamis Occidental': { 'Ozamiz': ['Bañadero','Tinago'], 'Oroquieta': ['Lower Lamac','Loyal'] }, 'Misamis Oriental': { 'Cagayan de Oro': ['Carmen','Lumbia'], 'Gingoog': ['Tupsan','Barangay 1'] }, 'Lanao del Norte': { 'Iligan': ['Acmac','Tibanga'], 'Tubod': ['Poblacion','Malingao'] }, 'Bukidnon': { 'Malaybalay': ['Sumpong','Barangay 9'], 'Valencia': ['Poblacion','Bagontaas'] }, 'Davao de Oro': { 'Nabunturan': ['Poblacion','Magsaysay'], 'Monkayo': ['Poblacion','San Jose'] }, 'Davao del Norte': { 'Tagum': ['Magugpo East','Poblacion'], 'Panabo': ['Gredu','San Francisco'] }, 'Davao del Sur': { 'Digos': ['Zone 1','Zone 2'] }, 'Davao Oriental': { 'Mati': ['Central','Sainz'] }, 'Davao Occidental': { 'Malita': ['Bulila','Poblacion'] }, 'South Cotabato': { 'Koronadal': ['Zone 1','Zone 2'], 'General Santos': ['Lagao','Apopong'] } }
    }
  };

  async function initPhPicker(root){
    const wrap = root.querySelector('#phPicker');
    if (!wrap) return;
    const input = wrap.querySelector('#ab_region');
    const panel = wrap.querySelector('.ph-panel');
    const list = wrap.querySelector('#phList');
    const tabs = Array.from(wrap.querySelectorAll('.ph-tabs button'));
    const $ = (sel, r=root)=> r.querySelector(sel);
    const state = {
      region: $('#ab_hidden_region', root)?.value || '',
      province: $('#ab_hidden_province', root)?.value || '',
      city: $('#ab_hidden_city', root)?.value || '',
      barangay: $('#ab_hidden_barangay', root)?.value || ''
    };
    const updateInput = () => { input.value = [state.region, state.province, state.city, state.barangay].filter(Boolean).join(', '); };
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
          const btn = document.createElement('button'); btn.className='ph-item'; btn.type='button'; btn.textContent=name;
          btn.onclick = ()=>{ state.region = name; state.province=''; state.city=''; state.barangay=''; enable('province'); render('province'); updateInput(); $('#ab_hidden_region', root).value = name; };
          list.appendChild(btn);
        });
      }
      if (tab==='province'){
        const obj = PH_LOC.regions[state.region] || {};
        Object.keys(obj).forEach(name=>{
          const btn = document.createElement('button'); btn.className='ph-item'; btn.type='button'; btn.textContent=name;
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
    tabs.forEach(b=> b.addEventListener('click', ()=>{ if (!b.disabled) render(b.dataset.tab); }));
    if (state.region){ enable('province'); }
    if (state.province){ enable('city'); }
    if (state.city){ enable('barangay'); }
  }

  window.AddressModal = { esc, wireLabelPills, getChecked, initPhPicker };
})();
