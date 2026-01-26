<?php
$title = "Dashboard";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require __DIR__ . "/config.php";

// KPI
$totalUser  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLunas = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='LUNAS'")->fetchColumn();
$totalBelum = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='BELUM LUNAS'")->fetchColumn();
$totalTagihan = (int)$pdo->query("SELECT COALESCE(SUM(tagihan_bulanan),0) FROM users")->fetchColumn();

$persen = $totalUser > 0 ? round(($totalLunas / $totalUser) * 100) : 0;

// Data grafik: rekap per daya
$stmtDaya = $pdo->query("
  SELECT daya_va,
         COUNT(*) AS jumlah,
         COALESCE(SUM(tagihan_bulanan),0) AS total
  FROM users
  GROUP BY daya_va
  ORDER BY daya_va ASC
");
$rekapDaya = $stmtDaya->fetchAll();

// List belum lunas
$stmt = $pdo->prepare("SELECT * FROM users WHERE status='BELUM LUNAS' ORDER BY id DESC LIMIT 8");
$stmt->execute();
$belum = $stmt->fetchAll();

// Normalize untuk chart (biar paket utama muncul walau 0)
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
  'status' => [
    'lunas' => $totalLunas,
    'belum' => $totalBelum,
  ],
  'bar' => [
    'labels' => $barLabels,
    'totals' => $barTotals,
    'counts' => $barCounts,
  ]
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
      <div class="big"><?= number_format($totalUser,0,',','.') ?></div>
      <div class="mini muted">Semua data pelanggan</div>
    </div>

    <div class="card kpi">
      <div class="muted">LUNAS</div>
      <div class="big"><?= number_format($totalLunas,0,',','.') ?></div>
      <div class="mini muted"><?= $persen ?>% sudah lunas</div>
    </div>

    <div class="card kpi">
      <div class="muted">BELUM LUNAS</div>
      <div class="big"><?= number_format($totalBelum,0,',','.') ?></div>
      <div class="mini muted">Perlu ditindak</div>
    </div>

    <div class="card kpi">
      <div class="muted">Total Tagihan / Bulan</div>
      <div class="big"><?= rupiah($totalTagihan) ?></div>
      <div class="mini muted">Akumulasi semua pelanggan</div>
    </div>
  </div>

  <!-- Charts + table -->
  <div class="dash-layout">
    <!-- LEFT: charts -->
    <div class="stack">
      <div class="card">
        <div class="card-title">Grafik Status Pembayaran</div>
        <div class="chart-row">
          <div class="chart-box">
            <canvas id="donut" width="320" height="240"></canvas>
          </div>
          <div class="legend">
            <div class="leg">
              <span class="dot dot-blue"></span>
              <div>
                <div class="muted">LUNAS</div>
                <div class="strong"><?= number_format($totalLunas,0,',','.') ?> pelanggan</div>
              </div>
            </div>
            <div class="leg">
              <span class="dot dot-amber"></span>
              <div>
                <div class="muted">BELUM LUNAS</div>
                <div class="strong"><?= number_format($totalBelum,0,',','.') ?> pelanggan</div>
              </div>
            </div>

            <div class="progress-wrap">
              <div class="muted" style="margin-bottom:8px;">Progress Lunas</div>
              <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $persen ?>%"></div>
              </div>
              <div class="mini muted" style="margin-top:8px;"><?= $persen ?>% pelanggan sudah lunas</div>
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
          <canvas id="bar" width="760" height="280"></canvas>
        </div>
      </div>
    </div>

    <!-- RIGHT: table -->
    <div class="card">
      <div class="card-title">Belum Lunas Terbaru</div>

      <div class="table-scroll">
        <table class="table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>No KWH</th>
              <th>Daya</th>
              <th>Tagihan</th>
              <th class="center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if(!$belum): ?>
            <tr><td colspan="5" class="center muted" style="padding:16px;">Semua pelanggan sudah lunas ✅</td></tr>
          <?php else: foreach($belum as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['nama']) ?></td>
              <td><?= htmlspecialchars($u['nomor_kwh']) ?></td>
              <td><?= number_format((int)($u['daya_va'] ?? 0),0,',','.') ?> VA</td>
              <td><?= rupiah((int)($u['tagihan_bulanan'] ?? 0)) ?>/bulan</td>
              <td class="center">
                <div class="btn-group">
                  <a class="btn" target="_blank" href="bills/print.php?id=<?= (int)$u['id'] ?>">Cetak</a>
                  <button class="btn primary paybtn" data-id="<?= (int)$u['id'] ?>" data-to="LUNAS" type="button">Lunas</button>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<script>
const DATA = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;

function rupiah(n){
  n = Math.round(n || 0);
  return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/* ===== DONUT CHART (canvas) ===== */
(function drawDonut(){
  const c = document.getElementById('donut');
  if(!c) return;
  const ctx = c.getContext('2d');
  const W = c.width, H = c.height;
  const cx = W/2, cy = H/2 + 5;
  const r = Math.min(W,H) * 0.33;
  const thick = r * 0.45;

  const lunas = DATA.status.lunas || 0;
  const belum = DATA.status.belum || 0;
  const total = lunas + belum;

  ctx.clearRect(0,0,W,H);

  // ring background
  ctx.beginPath();
  ctx.arc(cx, cy, r, 0, Math.PI*2);
  ctx.strokeStyle = 'rgba(255,255,255,.10)';
  ctx.lineWidth = thick;
  ctx.stroke();

  if(total <= 0){
    ctx.fillStyle = 'rgba(255,255,255,.65)';
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

  // center text
  ctx.fillStyle = 'rgba(255,255,255,.92)';
  ctx.font = '900 20px system-ui';
  ctx.textAlign = 'center';
  ctx.fillText(Math.round((lunas/total)*100) + '%', cx, cy - 2);

  ctx.fillStyle = 'rgba(255,255,255,.60)';
  ctx.font = '600 12px system-ui';
  ctx.fillText('LUNAS', cx, cy + 16);
})();

/* ===== BAR CHART (canvas) ===== */
(function drawBar(){
  const c = document.getElementById('bar');
  if(!c) return;
  const ctx = c.getContext('2d');

  const labels = DATA.bar.labels;
  const totals = DATA.bar.totals;

  const W = c.width, H = c.height;
  const padL = 44, padR = 16, padT = 18, padB = 42;
  const chartW = W - padL - padR;
  const chartH = H - padT - padB;

  ctx.clearRect(0,0,W,H);

  const maxVal = Math.max(1, ...totals);
  const gridLines = 4;

  // grid
  ctx.strokeStyle = 'rgba(255,255,255,.08)';
  ctx.lineWidth = 1;
  for(let i=0;i<=gridLines;i++){
    const y = padT + (chartH/gridLines)*i;
    ctx.beginPath();
    ctx.moveTo(padL, y);
    ctx.lineTo(W-padR, y);
    ctx.stroke();
  }

  // y labels
  ctx.fillStyle = 'rgba(255,255,255,.55)';
  ctx.font = '11px system-ui';
  ctx.textAlign = 'right';
  for(let i=0;i<=gridLines;i++){
    const v = maxVal - (maxVal/gridLines)*i;
    const y = padT + (chartH/gridLines)*i + 4;
    ctx.fillText((Math.round(v/1000000)*1) + 'jt', padL-6, y);
  }

  const n = labels.length;
  const gap = 16;
  const barW = (chartW - gap*(n-1)) / n;

  for(let i=0;i<n;i++){
    const val = totals[i] || 0;
    const h = (val / maxVal) * chartH;
    const x = padL + i*(barW + gap);
    const y = padT + (chartH - h);

    // bar
    const grad = ctx.createLinearGradient(0,y,0,y+h);
    grad.addColorStop(0,'rgba(59,130,246,.85)');
    grad.addColorStop(1,'rgba(37,99,235,.35)');
    ctx.fillStyle = grad;
    ctx.beginPath();
    const r = 10;
    ctx.roundRect(x, y, barW, h, r);
    ctx.fill();

    // value top
    ctx.fillStyle = 'rgba(255,255,255,.85)';
    ctx.font = '700 11px system-ui';
    ctx.textAlign = 'center';
    ctx.fillText((val>=1000000 ? (val/1000000).toFixed(2)+'jt' : rupiah(val)), x + barW/2, y - 6);

    // x label
    ctx.fillStyle = 'rgba(255,255,255,.65)';
    ctx.font = '700 12px system-ui';
    ctx.fillText(labels[i] + ' VA', x + barW/2, H - 18);
  }
})();

/* Toggle status cepat (buat tombol Lunas di tabel) */
document.addEventListener('click', async (e)=>{
  const btn = e.target.closest('.paybtn');
  if(!btn) return;

  const id = btn.dataset.id;
  const to = btn.dataset.to;

  btn.disabled = true;
  btn.textContent = '...';

  const body = new URLSearchParams();
  body.set('id', id);
  body.set('to', to);

  const res = await fetch('bills/toggle_status.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: body.toString()
  });

  const data = await res.json();
  if(!data.ok) alert(data.message || 'Gagal');
  location.reload();
});
</script>

</div></body></html>
