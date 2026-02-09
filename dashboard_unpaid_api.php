<?php
require __DIR__ . "/auth.php";
require __DIR__ . "/config.php";

header('Content-Type: application/json; charset=utf-8');

$perPage = 10; // cocok untuk panel dashboard
$page = max(1, (int)($_GET['page'] ?? 1));

function rupiah($n){
  return "Rp " . number_format((int)$n,0,',','.');
}

function pagHtml($page, $totalPages){
  if ($totalPages <= 1) return '';

  $mk = function($p, $label, $disabled=false, $active=false){
    $cls = "pbtn";
    if ($disabled) $cls .= " disabled";
    if ($active) $cls .= " active";
    $attr = $disabled ? "" : "data-page=\"$p\"";
    return "<button class=\"$cls\" $attr type=\"button\">$label</button>";
  };

 $html = '<div class="pager pager-center">';
  $html .= $mk($page-1, "‹", $page<=1);

  $start = max(1, $page-1);
  $end   = min($totalPages, $page+1);

  if ($start > 1) {
    $html .= $mk(1, "1");
    if ($start > 2) $html .= '<span class="dots" style="opacity:.6;">…</span>';
  }

  for ($i=$start; $i<=$end; $i++){
    $html .= $mk($i, (string)$i, false, $i===$page);
  }

  if ($end < $totalPages) {
    if ($end < $totalPages-1) $html .= '<span class="dots" style="opacity:.6;">…</span>';
    $html .= $mk($totalPages, (string)$totalPages);
  }

  $html .= $mk($page+1, "›", $page>=$totalPages);
  $html .= '</div>';
  return $html;
}

try {
  // total belum lunas
  $stmtTotal = $pdo->query("SELECT COUNT(*) FROM users WHERE status='BELUM LUNAS'");
  $totalRows = (int)$stmtTotal->fetchColumn();

  $totalPages = max(1, (int)ceil($totalRows / $perPage));
  $page = min($page, $totalPages);
  $offset = ($page - 1) * $perPage;

  // data page
  $stmt = $pdo->prepare("
    SELECT id, nama, nomor_kwh, daya_va, tagihan_bulanan
    FROM users
    WHERE status='BELUM LUNAS'
    ORDER BY id DESC
    LIMIT :limit OFFSET :offset
  ");
  $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ob_start();
  if (!$rows) {
    echo "<tr><td colspan='5' class='center muted' style='padding:16px;'>Semua pelanggan sudah lunas ✅</td></tr>";
  } else {
    foreach ($rows as $u) {
      $id = (int)$u['id'];
      $nama = htmlspecialchars($u['nama']);
      $kwh = htmlspecialchars($u['nomor_kwh']);
      $daya = number_format((int)($u['daya_va'] ?? 0),0,',','.');
      $tagihan = rupiah((int)($u['tagihan_bulanan'] ?? 0)) . "/bulan";

      echo "<tr>
        <td>{$nama}</td>
        <td>{$kwh}</td>
        <td>{$daya} VA</td>
        <td>{$tagihan}</td>
      </tr>";
    }
  }
  $tbody = ob_get_clean();

  echo json_encode([
    'tbody_html' => $tbody,
    'pagination_html' => pagHtml($page, $totalPages),
    'page' => $page,
    'total_pages' => $totalPages,
    'total' => $totalRows
  ]);

} catch (Throwable $e) {
  echo json_encode(['error'=>'API Error','message'=>$e->getMessage()]);
}
