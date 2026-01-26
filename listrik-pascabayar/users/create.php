<?php
$title = "Tambah User";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
require __DIR__ . "/../config.php";

$err = "";

// paket bulanan (flat)
$paket = [
  900  => 452986,
  1300 => 800700,  // 1.300 - 2.200
  2200 => 800700,
  3500 => 1299530, // 3.500 - 5.500
  5500 => 1299530,
  6600 => 1699530, // ≥ 6.600
];

function rupiah($n){
  return "Rp " . number_format((int)$n, 0, ',', '.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama   = trim($_POST['nama'] ?? '');
  $kwhNo  = trim($_POST['nomor_kwh'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $nohp   = trim($_POST['no_hp'] ?? '');

  $daya_va = (int)($_POST['daya_va'] ?? 900);
  if (!isset($paket[$daya_va])) $daya_va = 900;

  $tagihan_bulanan = $paket[$daya_va];
  $status = "BELUM LUNAS";

  if ($nama === '' || $kwhNo === '' || $alamat === '' || $nohp === '') {
    $err = "Semua field wajib diisi.";
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO users (nama, nomor_kwh, alamat, no_hp, daya_va, tagihan_bulanan, status)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nama, $kwhNo, $alamat, $nohp, $daya_va, $tagihan_bulanan, $status]);

    header("Location: index.php");
    exit;
  }
}
?>

<main class="content">
  <h2>Tambah User</h2>

  <?php if ($err): ?>
    <div class="alert"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="form-card">
    <form method="post" autocomplete="off">
      <div class="form-grid">
        <div class="field">
          <label>Nama</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" placeholder="Nama pelanggan">
        </div>

        <div class="field">
          <label>Nomor KWH</label>
          <input type="text" name="nomor_kwh" value="<?= htmlspecialchars($_POST['nomor_kwh'] ?? '') ?>" placeholder="Contoh: 983256">
        </div>

        <div class="field span-2">
          <label>Alamat</label>
          <textarea name="alamat" placeholder="Alamat lengkap..."><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
        </div>

        <div class="field">
          <label>Nomor HP</label>
          <input type="text" name="no_hp" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" placeholder="Contoh: 08xxxx">
        </div>

        <div class="field">
          <label>Daya (VA) / Paket Bulanan</label>
          <select name="daya_va" id="daya_va">
            <option value="900">900 VA — <?= rupiah(452986) ?>/bulan</option>
            <option value="1300">1.300 VA - 2.200 VA — <?= rupiah(800700) ?>/bulan</option>
            <option value="3500">R-2 (3.500 - 5.500 VA) — <?= rupiah(1299530) ?>/bulan</option>
            <option value="6600">R-3 (≥ 6.600 VA) — <?= rupiah(1699530) ?>/bulan</option>
          </select>

          <div class="help">Tagihan otomatis mengikuti paket (tanpa input kWh).</div>
        </div>

        <div class="field span-2">
          <label>Tagihan Bulanan</label>
          <div class="tarif-pill" id="billInfo"><?= rupiah(452986) ?>/bulan</div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn primary" type="submit">Simpan</button>
        <a class="btn" href="index.php">Batal</a>
      </div>
    </form>
  </div>
</main>

<script>
(function(){
  const daya = document.getElementById('daya_va');
  const info = document.getElementById('billInfo');

  const map = {
    '900': 452986,
    '1300': 800700,
    '3500': 1299530,
    '6600': 1699530
  };

  function rupiah(n){
    return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function update(){
    const val = map[daya.value] ?? 452986;
    info.textContent = `${rupiah(val)}/bulan`;
  }

  daya.addEventListener('change', update);
  update();
})();
</script>

</div></body></html>
