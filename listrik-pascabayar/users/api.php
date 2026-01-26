<?php
require __DIR__ . "/../auth.php";
require __DIR__ . "/../config.php";

header('Content-Type: application/json; charset=utf-8');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$q = trim($_GET['q'] ?? '');

$sort = $_GET['sort'] ?? 'id';
$dir  = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

$allowedSort = ['id','nama','nomor_kwh','alamat','voltase','no_hp'];
if (!in_array($sort, $allowedSort, true)) $sort = 'id';

$where = "";
$params = [];

if ($q !== '') {
  $where = "WHERE nama LIKE :q OR nomor_kwh LIKE :q OR alamat LIKE :q OR no_hp LIKE :q";
  $params['q'] = "%$q%";
}

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$stmtTotal->execute($params);
$totalRows = (int)$stmtTotal->fetchColumn();

$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM users $where ORDER BY $sort $dir LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($params as $k => $v) {
  $stmt->bindValue(":$k", $v, PDO::PARAM_STR);
}
$stmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

ob_start();
if (!$rows) {
  echo '<tr><td colspan="6" class="center muted" style="padding:18px;">Data tidak ditemukan.</td></tr>';
} else {
  foreach ($rows as $u) {
    $id = (int)$u['id'];
    echo "<tr>
      <td>".htmlspecialchars($u['nama'])."</td>
      <td>".htmlspecialchars($u['nomor_kwh'])."</td>
      <td class='wrap'>".htmlspecialchars($u['alamat'])."</td>
      <td class='num'>".(int)$u['voltase']."</td>
      <td>".htmlspecialchars($u['no_hp'])."</td>
      <td class='center'>
        <div class='btn-group'>
          <a class='btn' href='edit.php?id=$id'>Edit</a>
          <a class='btn danger' onclick=\"return confirm('Yakin hapus?')\" href='delete.php?id=$id'>Hapus</a>
        </div>
      </td>
    </tr>";
  }
}
$tbody = ob_get_clean();

function pagHtml($page, $totalPages) {
  if ($totalPages <= 1) return '';

  $mk = function($p, $label, $disabled=false, $active=false){
    $cls = "pbtn";
    if ($disabled) $cls .= " disabled";
    if ($active) $cls .= " active";
    $attr = $disabled ? "" : "data-page=\"$p\"";
    return "<button class=\"$cls\" $attr type=\"button\">$label</button>";
  };

  $html = '<div class="pager">';
  $html .= $mk($page-1, "‹", $page<=1);

  $start = max(1, $page-2);
  $end = min($totalPages, $page+2);

  if ($start > 1) {
    $html .= $mk(1, "1");
    if ($start > 2) $html .= '<span class="dots">…</span>';
  }

  for ($i=$start; $i<=$end; $i++){
    $html .= $mk($i, (string)$i, false, $i===$page);
  }

  if ($end < $totalPages) {
    if ($end < $totalPages-1) $html .= '<span class="dots">…</span>';
    $html .= $mk($totalPages, (string)$totalPages);
  }

  $html .= $mk($page+1, "›", $page>=$totalPages);
  $html .= '</div>';
  return $html;
}

echo json_encode([
  'tbody_html' => $tbody,
  'pagination_html' => pagHtml($page, $totalPages),
  'count_text' => $totalRows . " data",
  'page' => $page,
  'total_pages' => $totalPages
]);
