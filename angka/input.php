<?php
session_start();
require __DIR__ . "/lib.php";

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $n = max(0, (int)($_POST['n'] ?? 0));
  $raw = trim($_POST['numbers'] ?? '');

  $nums = parseNumbers($raw);

  // jika user isi n tapi jumlah angka lebih/kurang, tetap simpan yang ada
  // (kamu bisa paksa harus n kalau mau)
  $_SESSION['nums'] = $nums;
  $msg = "✅ Data tersimpan: " . count($nums) . " angka";
}

$active = 'input';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Input Angka</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Input Angka</h1>
      <div class="muted">Masukkan angka acak sebanyak n, lalu kembali ke menu.</div>
    </div>
    <div class="nav">
      <a href="index.php">Menu</a>
      <a class="active" href="input.php">Input Angka</a>
      <a href="sort.php">Sorting</a>
      <a href="search.php">Searching</a>
      <a href="reset.php">Reset</a>
    </div>
  </div>

  <div class="card">
    <?php if($msg): ?>
      <div class="badge ok"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Jumlah n (opsional, buat info saja)</label>
      <input type="number" name="n" min="0" placeholder="contoh: 10">

      <label>Masukkan angka (pisah dengan spasi / koma / enter)</label>
      <textarea name="numbers" placeholder="contoh: 5 2 9 1 7 3"></textarea>

      <div class="btns">
        <button class="primary" type="submit">Simpan</button>
        <a class="nav a" href="index.php" style="text-decoration:none"></a>
        <a href="index.php" class="nav-item" style="display:none"></a>
        <button type="button" onclick="location.href='index.php'">Kembali ke Menu</button>
      </div>
    </form>

    <div class="list">
      <b>Data tersimpan:</b><br>
      <?= isset($_SESSION['nums']) ? implode(', ', $_SESSION['nums']) : '<span class="muted">Belum ada</span>' ?>
    </div>
  </div>
</div>
</body>
</html>
