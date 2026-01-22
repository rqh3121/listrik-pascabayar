<?php
$title = "Edit User";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
require __DIR__ . "/../config.php";

$err = "";

// paket bulanan (flat)
$paket = [
  900  => 452986,
  1300 => 800700,
  2200 => 800700,
  3500 => 1299530,
  5500 => 1299530,
  6600 => 1699530,
];

function rupiah($n){
  return "Rp " . number_format((int)$n, 0, ',', '.');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID tidak valid.");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$u = $stmt->fetch();
if (!$u) die("Data user tidak ditemukan.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama   = trim($_POST['nama'] ?? '');
  $kwhNo  = trim($_POST['nomor_kwh'] ?? '');
  $alamat = trim($_POST['alamat'] ?? '');
  $nohp   = trim($_POST['no_hp'] ?? '');

  $daya_va = (int)($_POST['daya_va'] ?? 900);
  if (!isset($paket[$daya_va])) $daya_va = 900;

  $tagihan_bulanan = $paket[$daya_va];

  if ($nama === '' || $kwhNo === '' || $alamat === '' || $nohp === '') {
    $err = "Semua field wajib diisi.";
  } else {
    $stmtUp = $pdo->prepare("
      UPDATE users
      SET nama=?, nomor_kwh=?, alamat=?, no_hp=?, daya_va=?, tagihan_bulanan=?
      WHERE id=?
    ");
    $stmtUp->execute([$nama, $kwhNo, $alamat, $nohp, $daya_va, $tagihan_bulanan, $id]);

    header("Location: index.php");
    exit;
  }
}

$valNama   = $_POST['nama'] ?? $u['nama'];
$valKwhNo  = $_POST['nomor_kwh'] ?? $u['nomor_kwh'];
$valAlamat = $_POST['alamat'] ?? $u['alamat'];
$valNoHp   = $_POST['no_hp'] ?? ($u['no_hp'] ?? '');

$valDaya   = (int)($_POST['daya_va'] ?? ($u['daya_va'] ?? 900));
$valBill   = (int)($paket[$valDaya] ?? ($u['tagihan_bulanan'] ?? 452986));
?>
<main class="content">
  <h2>Edit User</h2>

  <?php if ($err): ?>
    <div class="alert"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="form-card">
    <form method="post" autocomplete="off">
      <div class="form-grid">
        <div class="field">
          <label>Nama</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($valNama) ?>">
        </div>

        <div class="field">
          <label>Nomor KWH</label>
          <input type="text" name="nomor_kwh" value="<?= htmlspecialchars($valKwhNo) ?>">
        </div>

        <div class="field span-2">
          <label>Alamat</label>
          <textarea name="alamat"><?= htmlspecialchars($valAlamat) ?></textarea>
        </div>

        <div class="field">
          <label>Nomor HP</label>
          <input type="text" name="no_hp" value="<?= htmlspecialchars($valNoHp) ?>">
        </div>

        <div class="field">
          <label>Daya (VA) / Paket Bulanan</label>
          <select name="daya_va" id="daya_va">
            <option value="900"  <?= $valDaya===900  ? 'selected' : '' ?>>900 VA — <?= rupiah(452986) ?>/bulan</option>
            <option value="1300" <?= ($valDaya===1300 || $valDaya===2200) ? 'selected' : '' ?>>1.300 VA - 2.200 VA — <?= rupiah(800700) ?>/bulan</option>
            <option value="3500" <?= ($valDaya===3500 || $valDaya===5500) ? 'selected' : '' ?>>R-2 (3.500 - 5.500 VA) — <?= rupiah(1299530) ?>/bulan</option>
            <option value="6600" <?= $valDaya>=6600 ? 'selected' : '' ?>>R-3 (≥ 6.600 VA) — <?= rupiah(1699530) ?>/bulan</option>
          </select>
        </div>

        <div class="field span-2">
          <label>Tagihan Bulanan</label>
          <div class="tarif-pill" id="billInfo"><?= rupiah($valBill) ?>/bulan</div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn primary" type="submit">Update</button>
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
