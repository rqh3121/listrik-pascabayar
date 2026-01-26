<?php
function bubbleSort(array $A): array {
  $n = count($A);
  for ($i=0; $i<$n-1; $i++){
    for ($j=0; $j<$n-1-$i; $j++){
      if ($A[$j] > $A[$j+1]){
        $tmp = $A[$j];
        $A[$j] = $A[$j+1];
        $A[$j+1] = $tmp;
      }
    }
  }
  return $A;
}

function binarySearch(array $A, int $x): bool {
  $l = 0;
  $r = count($A) - 1;
  while ($l <= $r){
    $m = intdiv($l + $r, 2);
    if ($A[$m] === $x) return true;
    if ($A[$m] < $x) $l = $m + 1;
    else $r = $m - 1;
  }
  return false;
}

function parseNumbers(string $raw): array {
  // ambil semua angka (boleh dipisah spasi/koma/newline)
  preg_match_all('/-?\d+/', $raw, $m);
  $nums = array_map('intval', $m[0] ?? []);
  return $nums;
}
