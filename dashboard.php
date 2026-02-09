<?php
$title = "Dashboard";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require __DIR__ . "/config.php";

// KPI (initial render)
$totalUser  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLunas = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='LUNAS'")->fetchColumn();
$totalBelum = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='BELUM LUNAS'")->fetchColumn();
$totalTagihan = (int)$pdo->query("SELECT COALESCE(SUM(tagihan_bulanan),0) FROM users")->fetchColumn();
$persen = $totalUser > 0 ? round(($totalLunas / $totalUser) * 100) : 0;

// chart initial
$stmtDaya = $pdo->query("
  SELECT daya_va, COUNT(*) AS jumlah, COALESCE(SUM(tagihan_bulanan),0) AS total
  FROM users
  GROUP BY daya_va
  ORDER BY daya_va ASC
");
$rekapDaya = $stmtDaya->fetchAll(PDO::FETCH_ASSOC);

$paketKeys = [900,1300,3500,6600];
$rekapMap = [];
foreach ($rekapDaya as $r) {
  $rekapMap[(int)$r['daya_va']] = [
    'jumlah' => (int)$r['jumlah'],
    'total'  => (int)$r['total'],
  ];
}
$barLabels = [];
$barTotals = [];
$barCounts = [];
foreach ($paketKeys as $k) {
  $barLabels[] = (string)$k;
  $barTotals[] = (int)($rekapMap[$k]['total'] ?? 0);
  $barCounts[] = (int)($rekapMap[$k]['jumlah'] ?? 0);
}

$chartData = [
  'status' => ['lunas' => $totalLunas, 'belum' => $totalBelum],
  'bar' => ['labels' => $barLabels, 'totals' => $barTotals, 'counts' => $barCounts]
];

function rupiah($n){
  return "Rp " . number_format((int)$n,0,',','.');
}
?>

<main class="content">
  <div class="dash-head">
    <div>
      <h2 style="margin:0 0 6px;">Dashboard</h2>
      <div class="muted">Ringkasan tagihan & status pembayaran pelanggan</div>
    </div>

    <div class="dash-actions">
      <a class="btn primary" href="users/create.php">+ Tambah User</a>
      <a class="btn" href="bills/index.php">Tagihan</a>
      <a class="btn" href="users/index.php">Data User</a>
    </div>
  </div>

  <!-- KPI -->
  <div class="dash-grid">
    <div class="card kpi">
      <div class="muted">Total Pelanggan</div>
      <div class="big" id="kpi-totalUser"><?= number_format($totalUser,0,',','.') ?></div>
      <div class="mini muted">Semua data pelanggan</div>
    </div>

    <div class="card kpi">
      <div class="muted">LUNAS</div>
      <div class="big" id="kpi-totalLunas"><?= number_format($totalLunas,0,',','.') ?></div>
      <div class="mini muted"><span id="kpi-persen"><?= (int)$persen ?></span>% sudah lunas</div>
    </div>

    <div class="card kpi">
      <div class="muted">BELUM LUNAS</div>
      <div class="big" id="kpi-totalBelum"><?= number_format($totalBelum,0,',','.') ?></div>
      <div class="mini muted">Perlu ditindak</div>
    </div>

    <div class="card kpi">
      <div class="muted">Total Tagihan / Bulan</div>
      <div class="big" id="kpi-totalTagihan"><?= rupiah($totalTagihan) ?></div>
      <div class="mini muted">Akumulasi semua pelanggan</div>
    </div>
  </div>

  <div class="dash-layout">
    <!-- LEFT -->
    <div class="stack">
      <div class="card">
        <div class="card-title">Grafik Status Pembayaran</div>

        <div class="chart-row">
          <div class="chart-box">
            <canvas id="donut"></canvas>
          </div>

          <div class="legend">
            <div class="leg">
              <span class="dot dot-blue"></span>
              <div>
                <div class="muted">LUNAS</div>
                <div class="strong"><span id="leg-lunas"><?= number_format($totalLunas,0,',','.') ?></span> pelanggan</div>
              </div>
            </div>
            <div class="leg">
              <span class="dot dot-amber"></span>
              <div>
                <div class="muted">BELUM LUNAS</div>
                <div class="strong"><span id="leg-belum"><?= number_format($totalBelum,0,',','.') ?></span> pelanggan</div>
              </div>
            </div>

            <div class="progress-wrap">
              <div class="muted" style="margin-bottom:8px;">Progress Lunas</div>

              <div class="progress-bar">
                <div id="kpi-progress" class="progress-fill" style="width: <?= $persen ?>%"></div>
              </div>

              <div id="kpi-progressText" class="mini muted" style="margin-top:8px;">
                <?= $persen ?>% pelanggan sudah lunas
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Grafik Tagihan per Daya (VA)</div>
        <div class="mini muted" style="margin-bottom:10px;">
          Menampilkan total tagihan akumulasi per paket daya.
        </div>
        <div class="chart-big">
          <canvas id="bar"></canvas>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="card">
      <div class="card-title">Belum Lunas Terbaru</div>

      <div class="table-scroll">
        <table class="table table-dashboard">
          <thead>
            <tr>
              <th>Nama</th>
              <th>No KWH</th>
              <th>Daya</th>
              <th>Tagihan</th>
            </tr>
          </thead>

          <tbody id="unpaidTbody">
            <tr><td colspan="5" class="center muted" style="padding:16px;">Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <div id="unpaidPager"></div>
    </div>
  </div>
</main>

<script>
let DATA = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;

function rupiah(n){
  n = Math.round(n || 0);
  return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function fmtNum(n){
  return (n||0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/* Canvas helper */
function fitCanvas(canvas, w, h){
  const dpr = window.devicePixelRatio || 1;
  canvas.style.width = w + "px";
  canvas.style.height = h + "px";
  canvas.width = Math.floor(w * dpr);
  canvas.height = Math.floor(h * dpr);
  const ctx = canvas.getContext("2d");
  ctx.setTransform(dpr,0,0,dpr,0,0);
  return ctx;
}

/* Donut */
function drawDonut(){
  const c = document.getElementById('donut');
  if(!c) return;
  const parent = c.parentElement;
  const W = Math.min(320, parent.clientWidth);
  const H = 240;
  const ctx = fitCanvas(c, W, H);

  const cx = W/2, cy = H/2 + 5;
  const r = Math.min(W,H) * 0.33;
  const thick = r * 0.45;

  const lunas = DATA.status.lunas || 0;
  const belum = DATA.status.belum || 0;
  const total = lunas + belum;

  ctx.clearRect(0,0,W,H);

  ctx.beginPath();
  ctx.arc(cx, cy, r, 0, Math.PI*2);
  ctx.strokeStyle = 'rgba(0,0,0,.08)';
  ctx.lineWidth = thick;
  ctx.stroke();

  if(total <= 0){
    ctx.fillStyle = 'rgba(0,0,0,.50)';
    ctx.font = '700 14px system-ui';
    ctx.textAlign = 'center';
    ctx.fillText('Belum ada data', cx, cy);
    return;
  }

  let start = -Math.PI/2;
  const slices = [
    {v:lunas, color:'rgba(59,130,246,.95)'},
    {v:belum, color:'rgba(245,158,11,.95)'},
  ];

  slices.forEach(s=>{
    const ang = (s.v/total) * Math.PI*2;
    ctx.beginPath();
    ctx.arc(cx, cy, r, start, start+ang);
    ctx.strokeStyle = s.color;
    ctx.lineWidth = thick;
    ctx.lineCap = 'round';
    ctx.stroke();
    start += ang;
  });

  ctx.fillStyle = 'rgba(17,24,39,.92)';
  ctx.font = '900 20px system-ui';
  ctx.textAlign = 'center';
  ctx.fillText(Math.round((lunas/total)*100) + '%', cx, cy - 2);

  ctx.fillStyle = 'rgba(17,24,39,.55)';
  ctx.font = '700 12px system-ui';
  ctx.fillText('LUNAS', cx, cy + 16);
}

/* Bar */
function drawBar(){
  const c = document.getElementById('bar');
  if(!c) return;

  const parent = c.parentElement;
  const W = parent.clientWidth;
  const H = 280;
  const ctx = fitCanvas(c, W, H);

  const labels = DATA.bar.labels || [];
  const totals = DATA.bar.totals || [];

  const padL = 56, padR = 16, padT = 18, padB = 44;
  const chartW = W - padL - padR;
  const chartH = H - padT - padB;

  ctx.clearRect(0,0,W,H);

  const maxVal = Math.max(1, ...totals);
  const gridLines = 4;

  ctx.strokeStyle = 'rgba(0,0,0,.08)';
  ctx.lineWidth = 1;
  for(let i=0;i<=gridLines;i++){
    const y = padT + (chartH/gridLines)*i;
    ctx.beginPath();
    ctx.moveTo(padL, y);
    ctx.lineTo(W-padR, y);
    ctx.stroke();
  }

  ctx.fillStyle = 'rgba(17,24,39,.55)';
  ctx.font = '11px system-ui';
  ctx.textAlign = 'right';
  for(let i=0;i<=gridLines;i++){
    const v = maxVal - (maxVal/gridLines)*i;
    const y = padT + (chartH/gridLines)*i + 4;
    ctx.fillText((v/1000000).toFixed(1) + ' jt', padL-8, y);
  }

  const n = labels.length || 1;
  const gap = 16;
  const barW = (chartW - gap*(n-1)) / n;

  for(let i=0;i<labels.length;i++){
    const val = totals[i] || 0;
    const h = (val / maxVal) * chartH;
    const x = padL + i*(barW + gap);
    const y = padT + (chartH - h);

    const grad = ctx.createLinearGradient(0,y,0,y+h);
    grad.addColorStop(0,'rgba(59,130,246,.85)');
    grad.addColorStop(1,'rgba(37,99,235,.30)');
    ctx.fillStyle = grad;

    if (ctx.roundRect) {
      ctx.beginPath();
      ctx.roundRect(x, y, barW, h, 10);
      ctx.fill();
    } else {
      ctx.fillRect(x, y, barW, h);
    }

    ctx.fillStyle = 'rgba(17,24,39,.78)';
    ctx.font = '700 11px system-ui';
    ctx.textAlign = 'center';
    const txt = (val>=1000000 ? (val/1000000).toFixed(2)+' jt' : rupiah(val));
    ctx.fillText(txt, x + barW/2, y - 6);

    ctx.fillStyle = 'rgba(17,24,39,.60)';
    ctx.font = '700 12px system-ui';
    ctx.fillText(labels[i] + ' VA', x + barW/2, H - 18);
  }
}

function redraw(){
  drawDonut();
  drawBar();
}

window.addEventListener('resize', ()=>{
  clearTimeout(window.__tmr);
  window.__tmr = setTimeout(redraw, 100);
});
redraw();

/* Refresh KPI + chart from API */
async function refreshKpiAndChart(){
  const res = await fetch('dashboard_kpi_api.php', { headers:{'X-Requested-With':'fetch'} });
  const json = await res.json();
  if(!json.ok) return;

  // KPI
  document.getElementById('kpi-totalUser').textContent = fmtNum(json.kpi.totalUser);
  document.getElementById('kpi-totalLunas').textContent = fmtNum(json.kpi.totalLunas);
  document.getElementById('kpi-totalBelum').textContent = fmtNum(json.kpi.totalBelum);
  document.getElementById('kpi-totalTagihan').textContent = rupiah(json.kpi.totalTagihan);
  document.getElementById('kpi-persen').textContent = json.kpi.persen;

  // legend
  document.getElementById('leg-lunas').textContent = fmtNum(json.kpi.totalLunas);
  document.getElementById('leg-belum').textContent = fmtNum(json.kpi.totalBelum);

  // progress
  const fill = document.getElementById('kpi-progress');
  const txt  = document.getElementById('kpi-progressText');
  if (fill) fill.style.width = json.kpi.persen + '%';
  if (txt) txt.textContent = json.kpi.persen + '% pelanggan sudah lunas';

  // chart data
  DATA.status = json.chart.status;
  DATA.bar = json.chart.bar;

  redraw();
}

/* Unpaid table */
(function(){
  const unpaidTbody = document.getElementById('unpaidTbody');
  const unpaidPager = document.getElementById('unpaidPager');
  let unpaidPage = 1;

  async function loadUnpaid(page = 1){
    unpaidPage = page;

    unpaidTbody.innerHTML = `<tr><td colspan="5" class="center muted" style="padding:16px;">Loading...</td></tr>`;

    const res = await fetch('dashboard_unpaid_api.php?page=' + page, {
      headers:{'X-Requested-With':'fetch'}
    });
    const data = await res.json();

    if (data.error) {
      unpaidTbody.innerHTML = `<tr><td colspan="5" class="center muted" style="padding:16px;">Gagal load data</td></tr>`;
      unpaidPager.innerHTML = '';
      return;
    }

    unpaidTbody.innerHTML = data.tbody_html || '';
    unpaidPager.innerHTML = data.pagination_html || '';
  }

  // pagination
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#unpaidPager .pbtn');
    if (!btn || btn.classList.contains('disabled') || !btn.dataset.page) return;
    loadUnpaid(parseInt(btn.dataset.page, 10));
  });

  // click lunas
  document.addEventListener('click', async (e)=> {
    const btn = e.target.closest('#unpaidTbody .paybtn');
    if(!btn) return;

    const id = btn.dataset.id;
    const to = btn.dataset.to;

    btn.disabled = true;
    const oldText = btn.textContent;
    btn.textContent = '...';

    const body = new URLSearchParams();
    body.set('id', id);
    body.set('to', to);

    const res = await fetch('bills/toggle_status.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: body.toString()
    });
    const out = await res.json();

    if(!out.ok){
      alert(out.message || 'Gagal');
      btn.disabled = false;
      btn.textContent = oldText;
      return;
    }

    // refresh table + KPI/chart
    loadUnpaid(unpaidPage);
    refreshKpiAndChart();
  });

  loadUnpaid(1);
})();
</script>

</div></body></html>
