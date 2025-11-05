<?php
require_once __DIR__.'/../db.php';
require_role('peminjam');

$u = current_user();
$cats = $conn->query("SELECT c.id, c.name, COUNT(i.id) total
                      FROM categories c LEFT JOIN items i ON i.category_id=c.id
                      GROUP BY c.id ORDER BY c.name");
$items = $conn->query("SELECT i.name, i.available_qty, c.name cat, l.name loc
                       FROM items i JOIN categories c ON c.id=i.category_id
                                    JOIN locations l ON l.id=i.location_id
                       ORDER BY i.name LIMIT 12");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Dashboard Peminjam — LabLoans</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary"><div class="container">
  <a class="navbar-brand" href="/peminjam/dashboard.php">LabLoans</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link active" href="/peminjam/dashboard.php">Beranda</a></li>
      <li class="nav-item"><a class="nav-link" href="/peminjam/catalog.php">Katalog Alat</a></li>
    </ul>
    <span class="navbar-text me-3"><?=e($u['name'])?> (<?=e($u['role'])?>)</span>
    <a class="btn btn-outline-danger" href="/logout.php">Logout</a>
  </div>
</div></nav>

<div class="container py-4">
  <h3>Halo, <?=e($u['name'])?></h3>
  <p class="text-muted">Lihat kategori atau langsung jelajah beberapa alat yang tersedia.</p>
  <div class="row g-3 my-2">
    <?php while($c=$cats->fetch_assoc()): ?>
      <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
          <b><?=e($c['name'])?></b>
          <div><?= (int)$c['total'] ?> jenis alat</div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <div class="card mt-3">
    <div class="card-header">Beberapa Alat Tersedia</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Alat</th><th>Kategori</th><th>Lokasi</th><th>Tersedia</th></tr></thead>
        <tbody>
          <?php while($r=$items->fetch_assoc()): ?>
            <tr>
              <td><?=e($r['name'])?></td>
              <td><?=e($r['cat'])?></td>
              <td><?=e($r['loc'])?></td>
              <td><?= (int)$r['available_qty'] ?></td>
            </tr>
          <?php endwhile; if($items->num_rows===0): ?>
            <tr><td colspan="4" class="text-center text-muted">Belum ada data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body></html>
