<?php
require_once __DIR__.'/db.php';
if (!current_user()) { header('Location: /login.php'); exit; }
$u = current_user();
if (in_array($u['role'], ['admin','laboran'])) {
  header('Location: /admin/dashboard.php'); exit;
} else {
  header('Location: /peminjam/dashboard.php'); exit;
}
