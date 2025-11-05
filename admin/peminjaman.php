<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

/**
 * Ubah status peminjaman:
 * - Disetujui : set status + kurangi stok alat (jumlah_tersedia)
 * - Ditolak   : set status saja
 * - Selesai   : set status + tanggal_kembali + simpan denda + kembalikan stok
 */
if (isset($_GET['ubah']) && isset($_GET['id'])) {
  $id     = (int)$_GET['id'];
  $status = $_GET['ubah'];

  if (in_array($status, ['Disetujui','Ditolak','Selesai'], true)) {

    if ($status === 'Disetujui') {
      // set status & kurangi stok sesuai detail
      $conn->query("UPDATE peminjaman SET status='Disetujui' WHERE id={$id}");

      $detail = $conn->query("SELECT alat_id, jumlah FROM detail_peminjaman WHERE peminjaman_id={$id}");
      while ($d = $detail->fetch_assoc()) {
        $conn->query("UPDATE alat SET jumlah_tersedia = jumlah_tersedia - {$d['jumlah']} WHERE id={$d['alat_id']}");
      }
    }

    if ($status === 'Ditolak') {
      // hanya ubah status
      $conn->query("UPDATE peminjaman SET status='Ditolak' WHERE id={$id}");
    }

    if ($status === 'Selesai') {
      // tandai selesai, catat tanggal_kembali = hari ini, dan simpan denda (telat x 5000)
      $conn->query("
        UPDATE peminjaman
        SET status='Selesai',
            tanggal_kembali = CURDATE(),
            denda = GREATEST(DATEDIFF(CURDATE(), tanggal_kembali_rencana), 0) * 5000
        WHERE id={$id}
      ");

      // kembalikan stok
      $detail = $conn->query("SELECT alat_id, jumlah FROM detail_peminjaman WHERE peminjaman_id={$id}");
      while ($d = $detail->fetch_assoc()) {
        $conn->query("UPDATE alat SET jumlah_tersedia = jumlah_tersedia + {$d['jumlah']} WHERE id={$d['alat_id']}");
      }
    }
  }

  header('Location: /admin/peminjaman.php'); exit;
}

/**
 * Ambil data peminjaman untuk tabel:
 * - nama peminjam (dari tabel pengguna)
 * - tanggal_pinjam, tanggal_kembali_rencana, tanggal_kembali
 * - status, denda
 * - hari_telat (hitung di SQL)
 * - daftar_alat (gabungan nama alat + jumlah)
 */
$data = $conn->query("
  SELECT
    p.id,
    p.pengguna_id,
    p.tanggal_pinjam,
    p.tanggal_kembali_rencana,
    p.tanggal_kembali,
    p.status,
    COALESCE(p.denda,0) AS denda,
    g.nama AS nama_peminjam,
    GREATEST(DATEDIFF(CURDATE(), p.tanggal_kembali_rencana), 0) AS hari_telat,
    d.daftar_alat
  FROM peminjaman p
  JOIN pengguna g ON g.id = p.pengguna_id
  LEFT JOIN (
    SELECT
      dp.peminjaman_id,
      GROUP_CONCAT(CONCAT(a.nama,' (',dp.jumlah,')') ORDER BY a.nama SEPARATOR ', ') AS daftar_alat
    FROM detail_peminjaman dp
    JOIN alat a ON a.id = dp.alat_id
    GROUP BY dp.peminjaman_id
  ) d ON d.peminjaman_id = p.id
  ORDER BY p.id DESC
");

$admin = current_user(); // ['id','nama','email','peran',...]
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kelola Peminjaman Alat</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="/admin/dashboard.php">SiPinLab</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/categories.php">Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/items.php">Alat</a></li>
        <li class="nav-item"><a class="nav-link active" href="/admin/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($admin['nama']) ?> (<?= e($admin['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-3">Daftar Peminjaman Alat</h4>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Peminjam</th>
          <th>Tanggal Pinjam</th>
          <th>Batas Kembali</th>
          <th>Tanggal Kembali</th>
          <th>Daftar Alat</th>
          <th>Status</th>
          <th>Denda</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($r = $data->fetch_assoc()): ?>
          <?php
            $hari_telat   = max(0, (int)($r['hari_telat'] ?? 0));
            $denda_tampil = ($r['status'] === 'Selesai')
                            ? (int)$r['denda']
                            : $hari_telat * 5000;

            $badge = [
              'Menunggu'  => 'secondary',
              'Disetujui' => 'success',
              'Ditolak'   => 'danger',
              'Selesai'   => 'info'
            ][$r['status']] ?? 'secondary';
          ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['nama_peminjam'] ?? '-') ?></td>
            <td><?= e($r['tanggal_pinjam'] ?? '-') ?></td>
            <td><?= e($r['tanggal_kembali_rencana'] ?? '-') ?></td>
            <td><?= e($r['tanggal_kembali'] ?? '-') ?></td>
            <td><?= e($r['daftar_alat'] ?? '-') ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= e($r['status']) ?></span></td>
            <td>
              <?= ($hari_telat > 0 || $r['status'] === 'Selesai')
                    ? '<span class="text-danger fw-bold">Rp'.number_format($denda_tampil,0,',','.').'</span>'
                    : '-' ?>
            </td>
            <td>
              <?php if ($r['status'] === 'Menunggu'): ?>
                <a class="btn btn-sm btn-success" href="?ubah=Disetujui&id=<?= (int)$r['id'] ?>">Setujui</a>
                <a class="btn btn-sm btn-danger"  href="?ubah=Ditolak&id=<?= (int)$r['id'] ?>">Tolak</a>
              <?php elseif ($r['status'] === 'Disetujui'): ?>
                <a class="btn btn-sm btn-info"    href="?ubah=Selesai&id=<?= (int)$r['id'] ?>">Selesai</a>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
