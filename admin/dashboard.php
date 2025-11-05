<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

// Ringkasan angka
$tot_items = (int)($conn->query("SELECT COUNT(*) AS c FROM alat")->fetch_assoc()['c'] ?? 0);
$tot_qty   = (int)($conn->query("SELECT COALESCE(SUM(jumlah_total),0) AS s FROM alat")->fetch_assoc()['s'] ?? 0);
$low_stock = (int)($conn->query("SELECT COUNT(*) AS c FROM alat WHERE jumlah_tersedia <= minimum_alert")->fetch_assoc()['c'] ?? 0);

// Daftar stok menipis (join kategori)
$low_list  = $conn->query("
  SELECT a.nama AS alat, a.jumlah_tersedia, a.jumlah_total, k.nama AS kategori
  FROM alat a
  JOIN kategori k ON k.id = a.kategori_id
  WHERE a.jumlah_tersedia <= a.minimum_alert
  ORDER BY a.jumlah_tersedia ASC, a.nama ASC
  LIMIT 20
");

// Alat berdasarkan total unit
$top = $conn->query("
  SELECT a.nama AS alat, a.jumlah_tersedia, a.jumlah_total, k.nama AS kategori
  FROM alat a
  JOIN kategori k ON k.id = a.kategori_id
  ORDER BY a.jumlah_total DESC, a.nama ASC
  LIMIT 10
");

$u = current_user(); 
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Admin — SiPinLab</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="/admin/dashboard.php">SiPinLab</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="/admin/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/categories.php">Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/items.php">Alat</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($u['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h3 class="mb-3">Dashboard Admin</h3>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card p-3">
        <b>Total Jenis Alat</b>
        <div class="display-6"><?= $tot_items ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <b>Total Unit</b>
        <div class="display-6"><?= $tot_qty ?></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <b>Stok Menipis</b>
        <div class="display-6 text-danger"><?= $low_stock ?></div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Daftar Stok Menipis</div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead>
              <tr><th>Alat</th><th>Kategori</th><th>Tersedia</th><th>Total</th></tr>
            </thead>
            <tbody>
              <?php if ($low_list && $low_list->num_rows): while($r=$low_list->fetch_assoc()): ?>
                <tr>
                  <td><?= e($r['alat']) ?></td>
                  <td><?= e($r['kategori']) ?></td>
                  <td><?= (int)$r['jumlah_tersedia'] ?></td>
                  <td><?= (int)$r['jumlah_total'] ?></td>
                </tr>
              <?php endwhile; else: ?>
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
            <thead>
              <tr><th>Alat</th><th>Kategori</th><th>Tersedia/Total</th></tr>
            </thead>
            <tbody>
              <?php if ($top && $top->num_rows): while($r=$top->fetch_assoc()): ?>
                <tr>
                  <td><?= e($r['alat']) ?></td>
                  <td><?= e($r['kategori']) ?></td>
                  <td><?= (int)$r['jumlah_tersedia'] ?>/<?= (int)$r['jumlah_total'] ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>
