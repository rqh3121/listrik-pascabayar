<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . "/config.php";

if (!isset($_SESSION['admin_id'])) {

    // kalau dipanggil via fetch / ajax
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'fetch'
    ) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // akses halaman biasa
    header("Location: /listrik-pascabayar/login.php");
    exit;
}
