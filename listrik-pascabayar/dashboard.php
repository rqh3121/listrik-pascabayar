<?php
$title = "Dashboard";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
?>
<main class="content">
  <h2>Dashboard</h2>
  <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['admin_username']) ?></b>.</p>

  <div class="cards">
    <div class="card">
      <div class="card-title">Menu</div>
      <div class="card-text">Kelola Data User, Tagihan, dan Cetak Struk.</div>
    </div>
  </div>
</main>
</div>
</body>
</html>
