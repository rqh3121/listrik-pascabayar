<?php
require __DIR__ . "/config.php";

$error = "";
$username = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch();

  if ($admin && password_verify($password, $admin['password_hash'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>

<body class="auth-body">
  <div class="auth-shell">
    <div class="auth-card auth-card--fancy">
      <div class="auth-top">
        <div class="auth-brand">
          <div class="auth-mark">⚡</div>
          <div>
            <div class="auth-title">Listrik Pascabayar</div>
            <div class="auth-subtitle">Admin Panel • Login</div>
          </div>
        </div>
      </div>

      <h1 class="auth-h1">Masuk</h1>
      <p class="auth-desc">Silakan login untuk mengelola pelanggan dan tagihan bulanan.</p>

      <?php if ($error): ?>
        <div class="alert"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <div class="field">
          <label>Username</label>
          <div class="input-wrap">
            <span class="icon" aria-hidden="true">👤</span>
            <input
              type="text"
              name="username"
              value="<?= htmlspecialchars($username) ?>"
              placeholder="Masukkan username"
              required
            />
          </div>
        </div>

        <div class="field">
          <label>Password</label>
          <div class="input-wrap">
            <span class="icon" aria-hidden="true">🔒</span>
            <input
              id="pwd"
              type="password"
              name="password"
              placeholder="Masukkan password"
              required
            />
            <button class="pw-toggle" type="button" id="btnToggle" aria-label="Tampilkan password">👁</button>
          </div>
        </div>

        <div class="auth-row">
          <span class="auth-mini muted">Pastikan akun admin terdaftar.</span>
        </div>

        <button class="btn primary btn-full" type="submit">Masuk</button>

        <div class="auth-foot">
          <span class="muted">© <?= date('Y') ?> Muhammad Kurnia Sandi</span>
        </div>
      </form>
    </div>
  </div>

  <script>
    (function(){
      const pwd = document.getElementById('pwd');
      const btn = document.getElementById('btnToggle');
      if (!pwd || !btn) return;

      btn.addEventListener('click', () => {
        const isPw = pwd.type === 'password';
        pwd.type = isPw ? 'text' : 'password';
        btn.textContent = isPw ? '🙈' : '👁';
        btn.setAttribute('aria-label', isPw ? 'Sembunyikan password' : 'Tampilkan password');
      });
    })();
  </script>
</body>
</html>
