<?php
require_once __DIR__.'/../db.php';
require_role('peminjam');

$u = current_user(); // ['id','nama','email','peran',...]

// Kategori + jumlah alat per kategori
$cats = $conn->query("
  SELECT k.id, k.nama, COUNT(a.id) AS total
  FROM kategori k
  LEFT JOIN alat a ON a.kategori_id = k.id
  GROUP BY k.id, k.nama
  ORDER BY k.nama ASC
");

// Beberapa alat tersedia
$items = $conn->query("
  SELECT a.nama AS alat, a.jumlah_tersedia,
         k.nama AS kat, l.nama AS lok
  FROM alat a
  JOIN kategori k ON k.id = a.kategori_id
  JOIN lokasi   l ON l.id = a.lokasi_id
  ORDER BY a.nama ASC
  LIMIT 12
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Dashboard Peminjam — SiPinLab</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="/peminjam/dashboard.php">SiPinLab</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link active" href="/peminjam/dashboard.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="/peminjam/catalog.php">Katalog Alat</a></li>
        <li class="nav-item"><a class="nav-link" href="/peminjam/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($u['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h3>Halo, <?= e($u['nama']) ?></h3>
  <p class="text-muted">Lihat kategori atau langsung jelajahi beberapa alat yang tersedia.</p>

  <div class="row g-3 my-2">
    <?php if ($cats && $cats->num_rows): while($c = $cats->fetch_assoc()): ?>
      <div class="col-6 col-md-3">
        <div class="card p-3 h-100">
          <b><?= e($c['nama']) ?></b>
          <div><?= (int)$c['total'] ?> jenis alat</div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <div class="col-12">
        <div class="alert alert-light border text-muted mb-0">Belum ada kategori.</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="card mt-3">
    <div class="card-header">Beberapa Alat Tersedia</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead>
          <tr><th>Alat</th><th>Kategori</th><th>Lokasi</th><th>Tersedia</th></tr>
        </thead>
        <tbody>
          <?php if ($items && $items->num_rows): while($r = $items->fetch_assoc()): ?>
            <tr>
              <td><?= e($r['alat']) ?></td>
              <td><?= e($r['kat']) ?></td>
              <td><?= e($r['lok']) ?></td>
              <td><?= (int)$r['jumlah_tersedia'] ?></td>
            </tr>
          <?php endwhile; else: ?>
            <tr><td colspan="4" class="text-center text-muted">Belum ada data alat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
