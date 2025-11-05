<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

/* ========= CREATE ========= */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])) {
  $nama  = trim($_POST['nama']  ?? '');
  $ruang = trim($_POST['ruang'] ?? '');
  $rak   = trim($_POST['rak']   ?? '');
  if ($nama !== '') {
    $stmt = $conn->prepare("INSERT INTO lokasi(nama, ruang, rak) VALUES (?,?,?)");
    $stmt->bind_param('sss', $nama, $ruang, $rak);
    $stmt->execute();
  }
  header('Location: /admin/locations.php'); exit;
}

/* ========= UPDATE ========= */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $id    = (int)($_POST['id'] ?? 0);
  $nama  = trim($_POST['nama']  ?? '');
  $ruang = trim($_POST['ruang'] ?? '');
  $rak   = trim($_POST['rak']   ?? '');
  if ($id && $nama !== '') {
    $stmt = $conn->prepare("UPDATE lokasi SET nama=?, ruang=?, rak=? WHERE id=?");
    $stmt->bind_param('sssi', $nama, $ruang, $rak, $id);
    $stmt->execute();
  }
  header('Location: /admin/locations.php'); exit;
}

/* ========= DELETE ========= */
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM lokasi WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
  }
  header('Location: /admin/locations.php'); exit;
}

/* ========= LIST ========= */
$rows = $conn->query("SELECT id, nama, ruang, rak FROM lokasi ORDER BY nama ASC");
$u = current_user(); // ['id','nama','email','peran',...]
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Lokasi — Admin</title>
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
        <li class="nav-item"><a class="nav-link active" href="/admin/locations.php">Lokasi</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/items.php">Alat</a></li>
        <li class="nav-item"><a class="nav-link" href="/admin/peminjaman.php">Peminjaman</a></li>
      </ul>
      <span class="navbar-text me-3"><?= e($u['nama']) ?> (<?= e($u['peran']) ?>)</span>
      <a class="btn btn-outline-danger" href="/logout.php">Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h4 class="mb-3">Lokasi</h4>

  <!-- Form tambah -->
  <form class="row g-2 mb-3" method="post">
    <input type="hidden" name="create" value="1">
    <div class="col-md-3">
      <input name="nama" class="form-control" placeholder="Nama lokasi" required>
    </div>
    <div class="col-md-2">
      <input name="ruang" class="form-control" placeholder="Ruang (opsional)">
    </div>
    <div class="col-md-2">
      <input name="rak" class="form-control" placeholder="Rak (opsional)">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary">Tambah</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr><th style="width:80px">ID</th><th>Nama</th><th>Ruang</th><th>Rak</th><th style="width:200px">Aksi</th></tr>
      </thead>
      <tbody>
        <?php if ($rows && $rows->num_rows): while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= e($r['nama']) ?></td>
          <td><?= e($r['ruang']) ?></td>
          <td><?= e($r['rak']) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary"
              onclick="editL(<?= (int)$r['id'] ?>,'<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>','<?= htmlspecialchars($r['ruang'] ?? '', ENT_QUOTES) ?>','<?= htmlspecialchars($r['rak'] ?? '', ENT_QUOTES) ?>')">
              Edit
            </button>
            <a class="btn btn-sm btn-outline-danger"
               href="?delete=<?= (int)$r['id'] ?>"
               onclick="return confirm('Hapus lokasi ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal edit -->
<div class="modal" tabindex="-1" id="modalEdit" style="display:none;">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post">
      <div class="modal-header">
        <h5 class="modal-title">Edit Lokasi</h5>
        <button type="button" class="btn-close" onclick="hide()"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="update" value="1">
        <input type="hidden" name="id" id="eid">
        <div class="mb-2">
          <label class="form-label">Nama</label>
          <input class="form-control" name="nama" id="enama" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Ruang</label>
          <input class="form-control" name="ruang" id="eruang">
        </div>
        <div class="mb-2">
          <label class="form-label">Rak</label>
          <input class="form-control" name="rak" id="erak">
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
function editL(id, nama, ruang, rak){
  document.getElementById('eid').value   = id;
  document.getElementById('enama').value = nama;
  document.getElementById('eruang').value= ruang ?? '';
  document.getElementById('erak').value  = rak ?? '';
  show();
}
function show(){ const m=document.getElementById('modalEdit'); m.style.display='block'; m.classList.add('show'); }
function hide(){ const m=document.getElementById('modalEdit'); m.classList.remove('show'); m.style.display='none'; }
</script>
</body>
</html>
