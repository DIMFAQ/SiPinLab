<?php

session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'sipinlab';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die('Koneksi gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

function current_user() { return $_SESSION['user'] ?? null; }

function require_login() {
  if (!current_user()) { header('Location: /login.php'); exit; }
}
function require_role(...$roles) {
  require_login();
  $u = current_user();
  if (!in_array($u['peran'], $roles)) {
    http_response_code(403);
    echo "<!doctype html><meta charset='utf-8'><div style='padding:24px;font-family:system-ui'>
            <h3>403 Forbidden</h3><p>Anda tidak memiliki akses.</p>
            <p><a href='javascript:history.back()'>Kembali</a></p></div>";
    exit;
  }
}

function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
