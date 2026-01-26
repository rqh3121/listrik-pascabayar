<?php
$title = "Data User";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
?>
<main class="content">
  <h2>Data User</h2>

  <div class="toolbar">
    <form class="search" id="searchForm">
      <input id="q" type="text" placeholder="Cari nama / nomor KWH / alamat / no HP..." autocomplete="off">
      <button class="btn primary" type="submit" id="btnSearch">Search</button>
      <button class="btn" type="button" id="btnReset">Reset</button>
    </form>

    <div style="display:flex; gap:10px; align-items:center;">
      <span class="badge" id="countBadge">0 data</span>
      <a class="btn primary" href="create.php">+ Tambah</a>
    </div>
  </div>

  <div class="table-wrap" id="tableBox" data-api="api.php">
    <div class="table-scroll">
      <table class="table">
        <thead>
          <tr>
            <th style="min-width:160px;">
              <span class="thsort"><a href="#" data-sort="nama">Nama</a><span class="arrow" id="arrow-nama"></span></span>
            </th>
            <th style="min-width:140px;">
              <span class="thsort"><a href="#" data-sort="nomor_kwh">Nomor KWH</a><span class="arrow" id="arrow-nomor_kwh"></span></span>
            </th>
            <th style="min-width:260px;">
              <span class="thsort"><a href="#" data-sort="alamat">Alamat</a><span class="arrow" id="arrow-alamat"></span></span>
            </th>
            <th class="num" style="min-width:120px;">
              <span class="thsort"><a href="#" data-sort="voltase">Voltase</a><span class="arrow" id="arrow-voltase"></span></span>
            </th>
            <th style="min-width:140px;">
              <span class="thsort"><a href="#" data-sort="no_hp">No HP</a><span class="arrow" id="arrow-no_hp"></span></span>
            </th>
            <th class="center" style="min-width:150px;">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="6" class="center muted" style="padding:18px;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div id="pager"></div>
</main>

<script>
(function(){
  const api   = document.getElementById('tableBox').dataset.api;
  const qEl   = document.getElementById('q');
  const tbody = document.getElementById('tbody');
  const pager = document.getElementById('pager');
  const badge = document.getElementById('countBadge');
  const form  = document.getElementById('searchForm');

  let state = { q:'', page:1, sort:'id', dir:'desc' };
  let tmr = null;

  function setArrows(){
    ['nama','nomor_kwh','alamat','voltase','no_hp'].forEach(k=>{
      const el = document.getElementById('arrow-'+k);
      if (!el) return;
      el.textContent = '';
      if (state.sort === k) el.textContent = state.dir === 'asc' ? '▲' : '▼';
    });
  }

  function showMsg(msg){
    tbody.innerHTML = `<tr><td colspan="6" class="center muted" style="padding:18px;">${msg}</td></tr>`;
    pager.innerHTML = '';
  }

  async function load(){
    const params = new URLSearchParams(state);

    // ✅ debug biar kamu bisa lihat q kekirim apa nggak
    // buka console (F12) nanti kelihatan URL fetch-nya
    console.log('[FETCH]', api + '?' + params.toString());

    tbody.innerHTML = `<tr><td colspan="6" class="center muted" style="padding:18px;">Loading...</td></tr>`;

    try {
      const res = await fetch(api + '?' + params.toString(), {headers:{'X-Requested-With':'fetch'}});
      const data = await res.json();

      if (data.error) {
        showMsg(data.error);
        badge.textContent = '0 data';
        return;
      }

      tbody.innerHTML = data.tbody_html || `<tr><td colspan="6" class="center muted" style="padding:18px;">Tidak ada data</td></tr>`;
      pager.innerHTML = data.pagination_html || '';
      badge.textContent = data.count_text || '0 data';
      setArrows();
    } catch (e) {
      console.error(e);
      showMsg('Gagal load data (cek API / koneksi).');
      badge.textContent = '0 data';
    }
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

  function triggerSearch(){
    state.q = qEl.value.trim();
    state.page = 1;
    load();
  }

  // ✅ Search via Enter / submit
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    triggerSearch();
  });

  // realtime search (debounce)
  qEl.addEventListener('input', ()=>{
    clearTimeout(tmr);
    tmr = setTimeout(triggerSearch, 300);
  });

  document.getElementById('btnReset').addEventListener('click', ()=>{
    qEl.value = '';
    state = { q:'', page:1, sort:'id', dir:'desc' };
    load();
  });

  load();
})();
</script>

</div></body></html>
