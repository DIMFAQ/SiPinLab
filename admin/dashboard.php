<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

$tot_items = (int)($conn->query("SELECT COUNT(*) c FROM items")->fetch_assoc()['c'] ?? 0);
$tot_qty   = (int)($conn->query("SELECT COALESCE(SUM(total_qty),0) s FROM items")->fetch_assoc()['s'] ?? 0);
$low_stock = (int)($conn->query("SELECT COUNT(*) c FROM items WHERE available_qty <= min_qty_alert")->fetch_assoc()['c'] ?? 0);
$low_list  = $conn->query("SELECT i.name, i.available_qty, i.total_qty, c.name cat
                           FROM items i JOIN categories c ON c.id=i.category_id
                           WHERE i.available_qty <= i.min_qty_alert ORDER BY i.available_qty ASC LIMIT 20");
$top       = $conn->query("SELECT i.name, i.available_qty, i.total_qty, c.name cat
                           FROM items i JOIN categories c ON c.id=i.category_id
                           ORDER BY i.total_qty DESC, i.name ASC LIMIT 10");
$u = current_user();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Admin — LabLoans</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary"><div class="container">
  <a class="navbar-brand" href="/admin/dashboard.php">LabLoans</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link active" href="/admin/dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/categories.php">Kategori</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/items.php">Alat</a></li>
    </ul>
    <span class="navbar-text me-3"><?=e($u['name'])?> (<?=e($u['role'])?>)</span>
    <a class="btn btn-outline-danger" href="/logout.php">Logout</a>
  </div>
</div></nav>

<div class="container py-4">
  <h3 class="mb-3">Dashboard Admin</h3>
  <div class="row g-3">
    <div class="col-md-4"><div class="card p-3"><b>Total Jenis Alat</b><div class="display-6"><?=$tot_items?></div></div></div>
    <div class="col-md-4"><div class="card p-3"><b>Total Unit</b><div class="display-6"><?=$tot_qty?></div></div></div>
    <div class="col-md-4"><div class="card p-3"><b>Stok Menipis</b><div class="display-6 text-danger"><?=$low_stock?></div></div></div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Daftar Stok Menipis</div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead><tr><th>Alat</th><th>Kategori</th><th>Tersedia</th><th>Total</th></tr></thead>
            <tbody>
              <?php while($r=$low_list->fetch_assoc()): ?>
                <tr>
                  <td><?=e($r['name'])?></td>
                  <td><?=e($r['cat'])?></td>
                  <td><?= (int)$r['available_qty'] ?></td>
                  <td><?= (int)$r['total_qty'] ?></td>
                </tr>
              <?php endwhile; if($low_list->num_rows===0): ?>
                <tr><td colspan="4" class="text-center text-muted">Tidak ada yang menipis</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Top Alat (berdasarkan total unit)</div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead><tr><th>Alat</th><th>Kategori</th><th>Tersedia/Total</th></tr></thead>
            <tbody>
              <?php while($r=$top->fetch_assoc()): ?>
                <tr>
                  <td><?=e($r['name'])?></td>
                  <td><?=e($r['cat'])?></td>
                  <td><?= (int)$r['available_qty'] ?>/<?= (int)$r['total_qty'] ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</body></html>
