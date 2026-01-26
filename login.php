<?php
require __DIR__ . "/config.php";

$error = "";

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
<html>
<head>
  <meta charset="utf-8" />
  <title>Login Admin</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body class="auth-body">
  <div class="auth-card">
    <h1>Login Admin</h1>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required />

      <label>Password</label>
      <input type="password" name="password" required />

      <button class="btn primary" type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>

<style>
:root{
  --bg1:#070b14;
  --bg2:#0b1220;

  --card: rgba(255,255,255,.04);
  --line: rgba(255,255,255,.10);

  --text:#e5e7eb;
  --muted:#94a3b8;

  --input-bg: rgba(255,255,255,.92);
  --input-text:#0f172a;

  --primary1:#3b82f6;
  --primary2:#2563eb;
}

*{ box-sizing:border-box; }

body{
  margin:0;
  min-height:100vh;
  display:grid;
  place-items:center;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color:var(--text);
  background:
    radial-gradient(900px 500px at 15% 0%, rgba(59,130,246,.18), transparent 60%),
    radial-gradient(900px 500px at 85% 100%, rgba(37,99,235,.14), transparent 60%),
    linear-gradient(120deg, var(--bg1), var(--bg2));
}

/* CARD */
.login-card{
  width:min(420px, 92vw);
  padding:28px 26px;
  border-radius:20px;
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
  border:1px solid var(--line);
  box-shadow:
    0 30px 80px rgba(0,0,0,.55),
    inset 0 1px 0 rgba(255,255,255,.06);
  backdrop-filter: blur(10px);
}

/* TITLE */
.login-card h1{
  margin:0 0 18px;
  font-size:34px;
  font-weight:800;
  letter-spacing:.2px;
}

/* FIELD */
.field{ margin-top:14px; }
label{
  display:block;
  margin-bottom:8px;
  font-size:13px;
  color:var(--muted);
}

/* INPUT */
input{
  width:100%;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(255,255,255,.12);
  background: var(--input-bg);
  color: var(--input-text);
  outline:none;
  font-size:14px;
  transition: .18s ease;
}

input::placeholder{ color:#64748b; }

input:focus{
  transform: translateY(-1px);
  border-color: rgba(59,130,246,.65);
  box-shadow: 0 0 0 4px rgba(59,130,246,.18);
}

/* BUTTON */
.btn-login, button[type="submit"]{
  width:100%;
  margin-top:18px;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(59,130,246,.35);
  background: linear-gradient(180deg, rgba(59,130,246,.25), rgba(37,99,235,.18));
  color:var(--text);
  font-weight:700;
  letter-spacing:.3px;
  cursor:pointer;
  transition:.18s ease;
}

.btn-login:hover, button[type="submit"]:hover{
  transform: translateY(-1px);
  border-color: rgba(59,130,246,.55);
  box-shadow: 0 12px 28px rgba(0,0,0,.35);
}

.btn-login:active, button[type="submit"]:active{
  transform: translateY(0px);
}

/* ALERT ERROR (kalau ada) */
.alert{
  margin-top:12px;
  padding:10px 12px;
  border-radius:14px;
  background: rgba(239,68,68,.14);
  border: 1px solid rgba(239,68,68,.35);
  color:#fecaca;
  font-size:13px;
}
</style>

<body>