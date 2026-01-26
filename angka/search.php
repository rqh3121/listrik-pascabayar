<?php
session_start();
require __DIR__ . "/lib.php";

$nums = $_SESSION['nums'] ?? [];
$sorted = $_SESSION['sorted'] ?? [];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $x = (int)($_POST['x'] ?? 0);

  // binary search perlu data urut
  if (!$sorted && $nums) {
    $sorted = bubbleSort($nums);
    $_SESSION['sorted'] = $sorted;
  }

  if (!$sorted) {
    $result = "⚠️ Belum ada data angka. Input dulu.";
  } else {
    $found = binarySearch($sorted, $x);
    $result = $found ? "✅ Angka ditemukan" : "❌ Angka tidak ditemukan";
  }
}

$active = 'search';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Searching</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Searching</h1>
      <div class="muted">Cari angka menggunakan Binary Search (data otomatis diurutkan dulu).</div>
    </div>
    <div class="nav">
      <a href="index.php">Menu</a>
      <a href="input.php">Input Angka</a>
      <a href="sort.php">Sorting</a>
      <a class="active" href="search.php">Searching</a>
      <a href="reset.php">Reset</a>
    </div>
  </div>

  <div class="card">
    <form method="post">
      <label>Masukkan angka yang mau dicari</label>
      <input type="number" name="x" required placeholder="contoh: 7">

      <div class="btns">
        <button class="primary" type="submit">Cari</button>
        <button type="button" onclick="location.href='index.php'">Kembali ke Menu</button>
      </div>
    </form>

    <?php if($result !== null): ?>
      <div class="list" style="margin-top:14px;">
        <b>Hasil:</b> <?= htmlspecialchars($result) ?>
      </div>
    <?php endif; ?>

    <hr>

    <div class="list">
      <b>Data (urut):</b><br>
      <?= $sorted ? implode(', ', $sorted) : '<span class="muted">Belum ada</span>' ?>
    </div>

    <div class="small" style="margin-top:10px;">
      Kompleksitas Binary Search: waktu O(log n), memori O(1)
    </div>
  </div>
</div>
</body>
</html>
