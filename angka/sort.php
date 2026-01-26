<?php
session_start();
require __DIR__ . "/lib.php";

$nums = $_SESSION['nums'] ?? [];
$sorted = [];

if ($nums) {
  $sorted = bubbleSort($nums);
  $_SESSION['sorted'] = $sorted; // simpan hasil urut
}

$active = 'sort';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sorting</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Sorting</h1>
      <div class="muted">Menampilkan hasil sorting (Bubble Sort).</div>
    </div>
    <div class="nav">
      <a href="index.php">Menu</a>
      <a href="input.php">Input Angka</a>
      <a class="active" href="sort.php">Sorting</a>
      <a href="search.php">Searching</a>
      <a href="reset.php">Reset</a>
    </div>
  </div>

  <div class="card">
    <?php if(!$nums): ?>
      <div class="badge no">⚠️ Belum ada data angka. Silakan input dulu.</div>
    <?php else: ?>
      <div class="badge ok">✅ Data berhasil diurutkan</div>

      <div class="grid">
        <div>
          <div class="muted">Sebelum</div>
          <div class="list"><?= implode(', ', $nums) ?></div>
        </div>
        <div>
          <div class="muted">Sesudah (urut naik)</div>
          <div class="list"><?= implode(', ', $sorted) ?></div>
        </div>
      </div>

      <hr>
      <div class="small">
        Kompleksitas Bubble Sort: waktu O(n²), memori O(1)
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
