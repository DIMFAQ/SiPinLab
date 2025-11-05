<?php
require_once __DIR__.'/../db.php';
require_role('peminjam');

$pengguna = current_user(); // ['id','nama','email','peran',...]

// Proses form peminjaman
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ajukan'])) {
  $alat_id     = (int)($_POST['alat_id'] ?? 0);
  $jumlah      = (int)($_POST['jumlah'] ?? 0);
  $tgl_pinjam  = trim($_POST['tgl_pinjam'] ?? '');
  $tgl_kembali = trim($_POST['tgl_kembali'] ?? '');

  if ($alat_id && $jumlah > 0 && $tgl_pinjam !== '' && $tgl_kembali !== '') {
    // (opsional) validasi stok cepat
    $cek = $conn->prepare("SELECT jumlah_tersedia FROM alat WHERE id=?");
    $cek->bind_param('i', $alat_id);
    $cek->execute();
    $stok = $cek->get_result()->fetch_assoc()['jumlah_tersedia'] ?? 0;

    if ($stok >= $jumlah) {
      $conn->begin_transaction();
      try {
        // Simpan header peminjaman
        $sql = "INSERT INTO peminjaman (pengguna_id, tanggal_pinjam, tanggal_kembali_rencana, status)
                VALUES (?,?,?,'Menunggu')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $pengguna['id'], $tgl_pinjam, $tgl_kembali);
        $stmt->execute();
        $id_peminjaman = $conn->insert_id;

        // Simpan detail peminjaman
        $stmt2 = $conn->prepare("INSERT INTO detail_peminjaman (peminjaman_id, alat_id, jumlah) VALUES (?,?,?)");
        $stmt2->bind_param('iii', $id_peminjaman, $alat_id, $jumlah);
        $stmt2->execute();

        $conn->commit();
        $pesan = "Permintaan peminjaman berhasil dikirim. Tunggu persetujuan admin.";
      } catch (Exception $e) {
        $conn->rollback();
        $pesan = "Terjadi kesalahan. Silakan coba lagi.";
      }
    } else {
      $pesan = "Jumlah melebihi stok tersedia.";
    }
  } else {
    $pesan = "Lengkapi semua kolom.";
  }
}

// Dropdown alat yang masih tersedia
$alat = $conn->query("
  SELECT id, nama, jumlah_tersedia
  FROM alat
  WHERE jumlah_tersedia > 0
  ORDER BY nama ASC
");

// Riwayat peminjaman peminjam aktif
$peminjaman = $conn->query("
  SELECT 
    p.id, 
    p.status, 
    p.tanggal_pinjam,
    p.tanggal_kembali_rencana,
    COALESCE(d.daftar_alat, '-') AS daftar_alat
  FROM peminjaman p
  LEFT JOIN (
    SELECT dp.peminjaman_id,
           GROUP_CONCAT(CONCAT(a.nama,' (',dp.jumlah,')') ORDER BY a.nama SEPARATOR ', ') AS daftar_alat
    FROM detail_peminjaman dp
    JOIN alat a ON a.id = dp.alat_id
    GROUP BY dp.peminjaman_id
  ) d ON d.peminjaman_id = p.id
  WHERE p.pengguna_id = {$pengguna['id']}
  ORDER BY p.id DESC
");
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Peminjaman Alat — SiPinLab</title>
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
        <li class="nav-item"><a class="nav-link" href="/peminjam/catalog.php">Katalog</a></li>
        <li class="nav-item"><a class="nav-link active" href="/peminjam/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($pengguna['nama']) ?> (<?= e($pengguna['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-3">Ajukan Peminjaman Alat</h4>
  <?php if(!empty($pesan)): ?>
    <div class="alert alert-info"><?= e($pesan) ?></div>
  <?php endif; ?>

  <form method="post" class="row g-2 mb-4">
    <input type="hidden" name="ajukan" value="1">

    <div class="col-md-4">
      <select class="form-select" name="alat_id" required>
        <option value="">Pilih alat...</option>
        <?php while($a = $alat->fetch_assoc()): ?>
          <option value="<?= (int)$a['id'] ?>">
            <?= e($a['nama']) ?> (tersedia <?= (int)$a['jumlah_tersedia'] ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-md-2">
      <input type="number" name="jumlah" min="1" class="form-control" placeholder="Jumlah" required>
    </div>

    <div class="col-md-2">
      <input type="date" name="tgl_pinjam" class="form-control" required>
    </div>

    <div class="col-md-2">
      <input type="date" name="tgl_kembali" class="form-control" required>
    </div>

    <div class="col-md-2">
      <button class="btn btn-primary w-100">Ajukan</button>
    </div>
  </form>

  <h5>Riwayat Peminjaman Saya</h5>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal Pinjam</th>
          <th>Batas Kembali</th>
          <th>Alat & Jumlah</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($peminjaman && $peminjaman->num_rows): while($r = $peminjaman->fetch_assoc()): ?>
          <?php
            $badge = [
              'Menunggu'  => 'secondary',
              'Disetujui' => 'success',
              'Ditolak'   => 'danger',
              'Selesai'   => 'info'
            ][$r['status']] ?? 'secondary';
          ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['tanggal_pinjam']) ?></td>
            <td><?= e($r['tanggal_kembali_rencana']) ?></td>
            <td><?= e($r['daftar_alat']) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= e($r['status']) ?></span></td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="5" class="text-center text-muted">Belum ada pengajuan.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
