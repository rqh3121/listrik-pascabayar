<?php
require __DIR__ . "/../auth.php";
require __DIR__ . "/../config.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$to = strtoupper(trim($_POST['to'] ?? ''));

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'ID tidak valid']);
  exit;
}

if (!in_array($to, ['LUNAS', 'BELUM LUNAS'], true)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'message' => 'Status tidak valid']);
  exit;
}

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->execute([$to, $id]);

echo json_encode(['ok' => true, 'message' => 'Status berhasil diubah']);
