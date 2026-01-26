<?php
// auth.php
require __DIR__ . "/config.php";

if (!isset($_SESSION['admin_id'])) {
  header("Location: /listrik-pascabayar/login.php");
  exit;
}
