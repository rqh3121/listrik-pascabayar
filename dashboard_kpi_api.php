<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/config.php";

header('Content-Type: application/json; charset=utf-8');

try {
  // KPI
  $totalUser  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
  $totalLunas = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='LUNAS'")->fetchColumn();
  $totalBelum = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='BELUM LUNAS'")->fetchColumn();
  $totalTagihan = (int)$pdo->query("SELECT COALESCE(SUM(tagihan_bulanan),0) FROM users")->fetchColumn();
  $persen = $totalUser > 0 ? (int)round(($totalLunas / $totalUser) * 100) : 0;

  // rekap daya (bar)
  $stmtDaya = $pdo->query("
    SELECT daya_va,
           COUNT(*) AS jumlah,
           COALESCE(SUM(tagihan_bulanan),0) AS total
    FROM users
    GROUP BY daya_va
    ORDER BY daya_va ASC
  ");
  $rekapDaya = $stmtDaya->fetchAll(PDO::FETCH_ASSOC);

  $paketKeys = [900,1300,3500,6600];
  $rekapMap = [];
  foreach ($rekapDaya as $r) {
    $rekapMap[(int)$r['daya_va']] = [
      'jumlah' => (int)$r['jumlah'],
      'total'  => (int)$r['total'],
    ];
  }

  $barLabels = [];
  $barTotals = [];
  $barCounts = [];

  foreach ($paketKeys as $k) {
    $barLabels[] = (string)$k;
    $barTotals[] = (int)($rekapMap[$k]['total'] ?? 0);
    $barCounts[] = (int)($rekapMap[$k]['jumlah'] ?? 0);
  }

  echo json_encode([
    'ok' => true,
    'kpi' => [
      'totalUser' => $totalUser,
      'totalLunas' => $totalLunas,
      'totalBelum' => $totalBelum,
      'totalTagihan' => $totalTagihan,
      'persen' => $persen,
    ],
    'chart' => [
      'status' => [
        'lunas' => $totalLunas,
        'belum' => $totalBelum,
      ],
      'bar' => [
        'labels' => $barLabels,
        'totals' => $barTotals,
        'counts' => $barCounts,
      ],
    ]
  ]);
} catch (Throwable $e) {
  echo json_encode([
    'ok' => false,
    'message' => $e->getMessage()
  ]);
}
