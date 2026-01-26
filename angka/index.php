<?php
session_start();
$active = 'menu';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Menu Angka</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <div class="top">
    <div>
      <h1>Menu Angka (Sorting & Searching)</h1>
      <div class="muted">Sesuai skenario tugas: input angka → sorting → searching</div>
    </div>
    <div class="nav">
      <a class="active" href="index.php">Menu</a>
      <a href="input.php">Input Angka</a>
      <a href="sort.php">Sorting</a>
      <a href="search.php">Searching</a>
      <a href="reset.php">Reset</a>
    </div>
  </div>

  <div class="card">
    <div class="badge">📌 Data saat ini:
      <b><?= isset($_SESSION['nums']) ? count($_SESSION['nums']) : 0 ?></b> angka
    </div>

    <div class="list" style="margin-top:12px;">
      <b>Petunjuk:</b><br>
      1) Masuk <b>Input Angka</b> → masukkan angka acak sebanyak n<br>
      2) Masuk <b>Sorting</b> → lihat hasil urut<br>
      3) Masuk <b>Searching</b> → cari angka, tampil “ditemukan / tidak ditemukan”<br>
    </div>

    <hr>

    <div class="small">
      Kompleksitas (default): Bubble Sort O(n²) memori O(1), Binary Search O(log n) memori O(1).
    </div>
  </div>
</div>
</body>
</html>
