<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

// Ambil kategori & lokasi untuk pilihan
$cat = $conn->query("SELECT id, nama FROM kategori ORDER BY nama");
$loc = $conn->query("SELECT id, nama FROM lokasi ORDER BY nama");

// CREATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])) {
  $kode   = trim($_POST['kode_unik'] ?? '');
  $nama   = trim($_POST['nama'] ?? '');
  $kat_id = (int)($_POST['kategori_id'] ?? 0);
  $lok_id = (int)($_POST['lokasi_id'] ?? 0);
  $kond   = $_POST['kondisi_enum'] ?? 'baik'; // 'baru','baik','cukup','rusak'
  $total  = (int)($_POST['jumlah_total'] ?? 0);
  $avail  = (int)($_POST['jumlah_tersedia'] ?? 0);
  $min    = (int)($_POST['minimum_alert'] ?? 0);
  $catat  = trim($_POST['catatan'] ?? '');

  if ($kode !== '' && $nama !== '') {
    $stmt = $conn->prepare("
      INSERT INTO alat(kode_unik,nama,kategori_id,lokasi_id,kondisi_enum,jumlah_total,jumlah_tersedia,minimum_alert,catatan)
      VALUES (?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param('ssiisiiis', $kode,$nama,$kat_id,$lok_id,$kond,$total,$avail,$min,$catat);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $id     = (int)($_POST['id'] ?? 0);
  $kode   = trim($_POST['kode_unik'] ?? '');
  $nama   = trim($_POST['nama'] ?? '');
  $kat_id = (int)($_POST['kategori_id'] ?? 0);
  $lok_id = (int)($_POST['lokasi_id'] ?? 0);
  $kond   = $_POST['kondisi_enum'] ?? 'baik';
  $total  = (int)($_POST['jumlah_total'] ?? 0);
  $avail  = (int)($_POST['jumlah_tersedia'] ?? 0);
  $min    = (int)($_POST['minimum_alert'] ?? 0);
  $catat  = trim($_POST['catatan'] ?? '');

  if ($id && $kode !== '' && $nama !== '') {
    $stmt = $conn->prepare("
      UPDATE alat
      SET kode_unik=?, nama=?, kategori_id=?, lokasi_id=?, kondisi_enum=?, jumlah_total=?, jumlah_tersedia=?, minimum_alert=?, catatan=?
      WHERE id=?
    ");
    $stmt->bind_param('ssiisiiisi', $kode,$nama,$kat_id,$lok_id,$kond,$total,$avail,$min,$catat,$id);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// DELETE
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM alat WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// LIST
$list = $conn->query("
  SELECT a.*, k.nama AS kat, l.nama AS lok
  FROM alat a
  JOIN kategori k ON k.id = a.kategori_id
  JOIN lokasi   l ON l.id = a.lokasi_id
  ORDER BY a.nama ASC
");

$u = current_user();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Alat — Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="/admin/dashboard.php">SiPinLab</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/categories.php">Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
        <li class="nav-item"><a class="nav-link active" href="/admin/items.php">Alat</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($u['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-3">Alat</h4>

  <!-- Form tambah -->
  <form class="row g-2 mb-3" method="post">
    <input type="hidden" name="create" value="1">
    <div class="col-md-2"><input class="form-control" name="kode_unik" placeholder="Kode unik" required></div>
    <div class="col-md-3"><input class="form-control" name="nama" placeholder="Nama alat" required></div>
    <div class="col-md-2">
      <select class="form-select" name="kategori_id" required>
        <option value="">Kategori...</option>
        <?php while($c=$cat->fetch_assoc()): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e($c['nama']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="lokasi_id" required>
        <option value="">Lokasi...</option>
        <?php while($l=$loc->fetch_assoc()): ?>
          <option value="<?= (int)$l['id'] ?>"><?= e($l['nama']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-1">
      <select class="form-select" name="kondisi_enum">
        <option value="baru">baru</option>
        <option value="baik" selected>baik</option>
        <option value="cukup">cukup</option>
        <option value="rusak">rusak</option>
      </select>
    </div>
    <div class="col-md-1"><input class="form-control" type="number" name="jumlah_total" value="0" min="0" placeholder="Total"></div>
    <div class="col-md-1"><input class="form-control" type="number" name="jumlah_tersedia" value="0" min="0" placeholder="Ters."></div>
    <div class="col-md-1"><input class="form-control" type="number" name="minimum_alert" value="1" min="0" placeholder="Min"></div>
    <div class="col-md-12"><input class="form-control" name="catatan" placeholder="Catatan (opsional)"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Tambah</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
      <thead>
        <tr>
          <th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th>
          <th>Kondisi</th><th>Tersedia/Total</th><th>Min</th><th style="width:180px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($list && $list->num_rows): while($r=$list->fetch_assoc()): ?>
        <tr>
          <td><?= e($r['kode_unik']) ?></td>
          <td><?= e($r['nama']) ?></td>
          <td><?= e($r['kat']) ?></td>
          <td><?= e($r['lok']) ?></td>
          <td><?= e($r['kondisi_enum']) ?></td>
          <td><?= (int)$r['jumlah_tersedia'] ?>/<?= (int)$r['jumlah_total'] ?></td>
          <td><?= (int)$r['minimum_alert'] ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary"
              onclick='editItem(<?= json_encode($r, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>Edit</button>
            <a class="btn btn-sm btn-outline-danger"
               href="?delete=<?= (int)$r['id'] ?>"
               onclick="return confirm('Hapus alat ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="8" class="text-center text-muted">Belum ada data</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal edit -->
<div class="modal" tabindex="-1" id="modalEdit" style="display:none;">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post">
      <div class="modal-header">
        <h5 class="modal-title">Edit Alat</h5>
        <button type="button" class="btn-close" onclick="hide()"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="id" id="eid">
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label">Kode</label>
            <input class="form-control" name="kode_unik" id="ecode" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Nama</label>
            <input class="form-control" name="nama" id="ename" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="kategori_id" id="ecat" required>
              <?php
              $cat2 = $conn->query("SELECT id, nama FROM kategori ORDER BY nama");
              while($c=$cat2->fetch_assoc()){
                echo "<option value='".(int)$c['id']."'>".e($c['nama'])."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Lokasi</label>
            <select class="form-select" name="lokasi_id" id="elok" required>
              <?php
              $loc2 = $conn->query("SELECT id, nama FROM lokasi ORDER BY nama");
              while($l=$loc2->fetch_assoc()){
                echo "<option value='".(int)$l['id']."'>".e($l['nama'])."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Kondisi</label>
            <select class="form-select" name="kondisi_enum" id="ekond">
              <option value="baru">baru</option>
              <option value="baik">baik</option>
              <option value="cukup">cukup</option>
              <option value="rusak">rusak</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Total</label>
            <input type="number" class="form-control" name="jumlah_total" id="etotal" min="0">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tersedia</label>
            <input type="number" class="form-control" name="jumlah_tersedia" id="etersedia" min="0">
          </div>
          <div class="col-md-3">
            <label class="form-label">Min Alert</label>
            <input type="number" class="form-control" name="minimum_alert" id="emin" min="0">
          </div>
          <div class="col-md-12">
            <label class="form-label">Catatan</label>
            <input class="form-control" name="catatan" id="ecatatan">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" onclick="hide()">Batal</button>
      </div>
    </form>
  </div></div>
</div>

<script>
function editItem(r){
  document.getElementById('eid').value       = r.id;
  document.getElementById('ecode').value     = r.kode_unik;
  document.getElementById('ename').value     = r.nama;
  document.getElementById('ecat').value      = r.kategori_id;
  document.getElementById('elok').value      = r.lokasi_id;
  document.getElementById('ekond').value     = r.kondisi_enum;
  document.getElementById('etotal').value    = r.jumlah_total;
  document.getElementById('etersedia').value = r.jumlah_tersedia;
  document.getElementById('emin').value      = r.minimum_alert;
  document.getElementById('ecatatan').value  = r.catatan ?? '';
  show();
}
function show(){ const m=document.getElementById('modalEdit'); m.style.display='block'; m.classList.add('show'); }
function hide(){ const m=document.getElementById('modalEdit'); m.classList.remove('show'); m.style.display='none'; }
</script>
</body>
</html>
