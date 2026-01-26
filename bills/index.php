<?php
$title = "Tagihan";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
$tarif = 1500;
?>
<main class="content">
  <h2>Tagihan</h2>

  <div class="toolbar">
    <form class="search" onsubmit="return false;">
      <input id="q" type="text" placeholder="Cari nama / nomor KWH / alamat..." autocomplete="off">
      <button class="btn primary" type="button" id="btnSearch">Search</button>
      <button class="btn" type="button" id="btnReset">Reset</button>
    </form>

    <div style="display:flex; gap:10px; align-items:center;">
      <span class="badge" id="countBadge">0 data</span>
    </div>
  </div>

  <div class="table-wrap" id="tableBox" data-api="api.php">
    <div class="table-scroll">
      <table class="table">
        <thead>
          <tr>
            <th><span class="thsort"><a href="#" data-sort="nama">Nama</a><span class="arrow" id="arrow-nama"></span></span></th>
            <th><span class="thsort"><a href="#" data-sort="nomor_kwh">Nomor KWH</a><span class="arrow" id="arrow-nomor_kwh"></span></span></th>
            <th><span class="thsort"><a href="#" data-sort="alamat">Alamat</a><span class="arrow" id="arrow-alamat"></span></span></th>
            <th><span class="thsort"><a href="#" data-sort="voltase">Voltase</a><span class="arrow" id="arrow-voltase"></span></span></th>
            <th>Total Tagihan</th>
            <th><span class="thsort"><a href="#" data-sort="status">Status</a><span class="arrow" id="arrow-status"></span></span></th>
            <th class="center">Aksi</th>
            <th><span class="thsort"><a href="#" data-sort="status">Tandai Lunas</a><span class="arrow" id="arrow-status"></span></span></th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="7" class="center muted" style="padding:18px;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div id="pager"></div>
</main>

<script>
(function(){
  const api = document.getElementById('tableBox').dataset.api;
  const qEl = document.getElementById('q');
  const tbody = document.getElementById('tbody');
  const pager = document.getElementById('pager');
  const badge = document.getElementById('countBadge');

  let state = { q:'', page:1, sort:'id', dir:'desc' };
  let tmr = null;

  function setArrows(){
    ['nama','nomor_kwh','alamat','voltase','status'].forEach(k=>{
      const el = document.getElementById('arrow-'+k);
      if (!el) return;
      el.textContent = '';
      if (state.sort === k) el.textContent = state.dir === 'asc' ? '▲' : '▼';
    });
  }

  async function load(){
    const params = new URLSearchParams(state);
    tbody.innerHTML = `<tr><td colspan="7" class="center muted" style="padding:18px;">Loading...</td></tr>`;
    const res = await fetch(api + '?' + params.toString(), {headers:{'X-Requested-With':'fetch'}});
    const data = await res.json();
    tbody.innerHTML = data.tbody_html;
    pager.innerHTML = data.pagination_html;
    badge.textContent = data.count_text;
    setArrows();
  }

  // sort klik header
  document.querySelectorAll('[data-sort]').forEach(a=>{
    a.addEventListener('click', (e)=>{
      e.preventDefault();
      const s = a.dataset.sort;
      if (state.sort === s) state.dir = (state.dir === 'asc') ? 'desc' : 'asc';
      else { state.sort = s; state.dir = 'asc'; }
      state.page = 1;
      load();
    });
  });

  // pagination klik
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.pbtn');
    if (!btn || btn.classList.contains('disabled') || !btn.dataset.page) return;
    state.page = parseInt(btn.dataset.page, 10);
    load();
  });

  // realtime search
  function triggerSearch(){
    state.q = qEl.value.trim();
    state.page = 1;
    load();
  }

  qEl.addEventListener('input', ()=>{
    clearTimeout(tmr);
    tmr = setTimeout(triggerSearch, 300);
  });

  document.getElementById('btnSearch').addEventListener('click', triggerSearch);
  document.getElementById('btnReset').addEventListener('click', ()=>{
    qEl.value = '';
    state = { q:'', page:1, sort:'id', dir:'desc' };
    load();
  });

  // klik tombol tandai lunas / batalkan (AJAX)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.paybtn');
    if (!btn) return;

    const id = btn.dataset.id;
    const to = btn.dataset.to;

    btn.disabled = true;
    btn.textContent = '...';

    const form = new URLSearchParams();
    form.set('id', id);
    form.set('to', to);

    const res = await fetch('toggle_status.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: form.toString()
    });

    const data = await res.json();
    if (!data.ok) {
      alert(data.message || 'Gagal ubah status');
    }
    await load();
  });

  load();
})();
</script>

</div></body></html>
