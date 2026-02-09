<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function activeExact($path){
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  return $uri === $path ? 'active' : '';
}

function activePath($prefix){
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  return str_starts_with($uri, $prefix) ? 'active' : '';
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
    <a class="nav-item <?= activeExact('/listrik-pascabayar/dashboard.php') ?>" href="/listrik-pascabayar/dashboard.php">
      <span class="icon">🏠</span>
      <span>Dashboard</span>
    </a>

    <a class="nav-item <?= activePath('/listrik-pascabayar/users/') ?>" href="/listrik-pascabayar/users/index.php">
      <span class="icon">👥</span>
      <span>Data Pelanggan</span>
    </a>

    <a class="nav-item <?= activePath('/listrik-pascabayar/bills/') ?>" href="/listrik-pascabayar/bills/index.php">
      <span class="icon">🧾</span>
      <span>Tagihan</span>
    </a>

    <a class="nav-item <?= activeExact('/listrik-pascabayar/users/create.php') ?>" href="/listrik-pascabayar/users/create.php">
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
