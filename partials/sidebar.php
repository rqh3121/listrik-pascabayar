<?php
$current = basename($_SERVER['PHP_SELF']);
function active($file){
  return strpos($_SERVER['PHP_SELF'], $file) !== false ? 'active' : '';
}
?>

<aside class="sidebar">
  <div class="sidebar-head">
    <div class="logo">⚡</div>
    <div>
      <div class="title">Listrik Pascabayar</div>
      <div class="subtitle">Admin Panel</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a class="nav-item <?= active('dashboard') ?>" href="/listrik-pascabayar/dashboard.php">
      <span class="icon">🏠</span>
      <span>Dashboard</span>
    </a>

    <a class="nav-item <?= active('users/index') ?>" href="/listrik-pascabayar/users/index.php">
      <span class="icon">👥</span>
      <span>Data User</span>
    </a>

    <a class="nav-item <?= active('bills/index') ?>" href="/listrik-pascabayar/bills/index.php">
      <span class="icon">🧾</span>
      <span>Tagihan</span>
    </a>

    <a class="nav-item <?= active('users/create') ?>" href="/listrik-pascabayar/users/create.php">
      <span class="icon">➕</span>
      <span>Tambah User</span>
    </a>
  </nav>

  <div class="sidebar-foot">
    <a class="nav-item logout" href="/listrik-pascabayar/logout.php">
      <span class="icon">🚪</span>
      <span>Logout</span>
    </a>
  </div>
</aside>
