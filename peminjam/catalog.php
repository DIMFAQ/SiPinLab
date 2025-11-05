<?php
require_once __DIR__.'/../db.php';
require_role('peminjam');

$q   = trim($_GET['q']   ?? '');
$cat = (int)($_GET['cat'] ?? 0);

// kategori untuk dropdown
$cats = $conn->query("SELECT id, nama FROM kategori ORDER BY nama ASC");

// build query katalog
$sql = "
  SELECT a.nama, a.jumlah_tersedia, a.jumlah_total,
         k.nama AS kat, l.nama AS lok
  FROM alat a
  JOIN kategori k ON k.id = a.kategori_id
  JOIN lokasi   l ON l.id = a.lokasi_id
  WHERE 1=1
";
$params = [];
$types  = '';

if ($q !== '') {
  $sql .= " AND (a.nama LIKE CONCAT('%',?,'%') OR a.kode_unik LIKE CONCAT('%',?,'%'))";
  $params[] = $q; $params[] = $q; $types .= 'ss';
}
if ($cat) {
  $sql .= " AND a.kategori_id = ?";
  $params[] = $cat; $types .= 'i';
}
$sql .= " ORDER BY a.nama ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$list = $stmt->get_result();

$u = current_user(); // ['id','nama','email','peran',...]
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Katalog — Peminjam</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="/peminjam/dashboard.php">SiPinLab</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/peminjam/dashboard.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link active" href="/peminjam/catalog.php">Katalog Alat</a></li>
        <li class="nav-item"><a class="nav-link" href="/peminjam/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($u['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-3">Katalog Alat</h4>

  <form class="row g-2 mb-3" method="get">
    <div class="col-md-5">
      <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Cari nama/kode...">
    </div>
    <div class="col-md-4">
      <select class="form-select" name="cat">
        <option value="0">Semua Kategori</option>
        <?php while($c = $cats->fetch_assoc()): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>>
            <?= e($c['nama']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100">Terapkan</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
      <thead>
        <tr><th>Alat</th><th>Kategori</th><th>Lokasi</th><th>Tersedia/Total</th></tr>
      </thead>
      <tbody>
        <?php if ($list && $list->num_rows): while($r = $list->fetch_assoc()): ?>
          <tr>
            <td><?= e($r['nama']) ?></td>
            <td><?= e($r['kat']) ?></td>
            <td><?= e($r['lok']) ?></td>
            <td><?= (int)$r['jumlah_tersedia'] ?>/<?= (int)$r['jumlah_total'] ?></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
