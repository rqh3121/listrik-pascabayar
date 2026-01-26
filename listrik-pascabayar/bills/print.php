<?php
require __DIR__ . "/../auth.php";
require __DIR__ . "/../config.php";

$tarif = 1500;

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  die("ID tidak valid.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
  die("Data user tidak ditemukan.");
}

$nama      = $u['nama'];
$kwh       = $u['nomor_kwh'];
$alamat    = $u['alamat'];
$voltase   = (int)$u['voltase'];
$nohp      = $u['no_hp'] ?? '-';
$status    = $u['status'] ?? 'BELUM LUNAS';

$total = $voltase * $tarif;

$invoice = "INV-" . date("Ymd") . "-" . str_pad((string)$id, 4, "0", STR_PAD_LEFT);
$tanggal = date("d-m-Y H:i");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Struk Tagihan</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    /* halaman struk: center */
    body{
      background: linear-gradient(120deg,#070b14,#0b1220);
    }
    .receipt-page{
      min-height:100vh;
      display:grid;
      place-items:center;
      padding:24px;
    }
    .receipt{
      width:min(520px, 100%);
      border:1px solid var(--line);
      border-radius:16px;
      background:rgba(255,255,255,.03);
      padding:18px;
      backdrop-filter: blur(10px);
    }
    .receipt h1{
      margin:0;
      font-size:20px;
      letter-spacing:.4px;
      text-align:center;
    }
    .receipt .meta{
      margin-top:10px;
      display:flex;
      justify-content:space-between;
      gap:10px;
      color:var(--muted);
      font-size:13px;
      border-bottom:1px dashed var(--line);
      padding-bottom:12px;
    }
    .grid{
      margin-top:14px;
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:12px;
    }
    .box{
      border:1px solid var(--line);
      border-radius:14px;
      padding:12px;
      background:rgba(0,0,0,.10);
    }
    .label{color:var(--muted);font-size:12px;margin-bottom:4px}
    .value{font-weight:700;line-height:1.35}
    .value.small{font-weight:600}
    .full{grid-column:1 / -1}
    .split{
      display:flex;
      justify-content:space-between;
      gap:10px;
      margin:8px 0;
      font-size:14px;
    }
    .split .muted{color:var(--muted)}
    .total{
      margin-top:10px;
      padding-top:10px;
      border-top:1px dashed var(--line);
      display:flex;
      justify-content:space-between;
      font-weight:800;
      font-size:16px;
    }
    .status-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:6px 10px;
      border-radius:999px;
      border:1px solid var(--line);
      background:rgba(255,255,255,.03);
      font-size:12px;
      color:var(--text);
      width:max-content;
    }
    .status-pill.lunas{border-color:rgba(79,255,160,.35)}
    .status-pill.belum{border-color:rgba(255,200,79,.35)}
    .note{
      margin-top:12px;
      color:var(--muted);
      font-size:12px;
      text-align:center;
    }
    .actions{
      margin-top:14px;
      display:flex;
      justify-content:center;
      gap:10px;
      flex-wrap:wrap;
    }

    /* PRINT MODE: cuma struk */
    @media print{
      body{background:#fff}
      .receipt-page{padding:0}
      .receipt{
        border:1px solid #ddd;
        background:#fff;
        color:#000;
        box-shadow:none;
      }
      .note,.actions{display:none !important}
      .label{color:#555}
      .meta{color:#333}
      .box{background:#fff;border:1px solid #eee}
    }
  </style>
</head>
<body>
  <div class="receipt-page">
    <div class="receipt" id="receipt">
      <h1>STRUK TAGIHAN LISTRIK</h1>

      <div class="meta">
        <div>No: <b><?= htmlspecialchars($invoice) ?></b></div>
        <div>Tanggal: <b><?= htmlspecialchars($tanggal) ?></b></div>
      </div>

      <div class="grid">
        <div class="box">
          <div class="label">Nama</div>
          <div class="value"><?= htmlspecialchars($nama) ?></div>
        </div>

        <div class="box">
          <div class="label">Nomor KWH</div>
          <div class="value"><?= htmlspecialchars($kwh) ?></div>
        </div>

        <div class="box full">
          <div class="label">Alamat</div>
          <div class="value small"><?= nl2br(htmlspecialchars($alamat)) ?></div>
        </div>

        <div class="box">
          <div class="label">Nomor HP</div>
          <div class="value"><?= htmlspecialchars($nohp) ?></div>
        </div>

        <div class="box">
          <div class="label">Status</div>
          <?php $isLunas = ($status === 'LUNAS'); ?>
          <div class="status-pill <?= $isLunas ? 'lunas' : 'belum' ?>">
            <?= htmlspecialchars($status) ?>
          </div>
        </div>

        <div class="box full">
          <div class="split">
            <span class="muted">Voltase diambil</span>
            <b><?= number_format($voltase,0,',','.') ?></b>
          </div>
          <div class="split">
            <span class="muted">Tarif / voltase</span>
            <b>Rp <?= number_format($tarif,0,',','.') ?></b>
          </div>

          <div class="total">
            <span>Total Tagihan</span>
            <span>Rp <?= number_format($total,0,',','.') ?></span>
          </div>
        </div>
      </div>

      <div class="note">
        Terima kasih. Simpan struk ini sebagai bukti pembayaran.
      </div>

      <div class="actions">
        <button class="btn primary" onclick="window.print()">Print</button>
        <a class="btn" href="index.php">Kembali</a>
      </div>
    </div>
  </div>
</body>
</html>
