<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])) {
  $nama = trim($_POST['nama'] ?? '');
  if ($nama !== '') {
    $stmt = $conn->prepare("INSERT INTO kategori(nama) VALUES (?)");
    $stmt->bind_param('s', $nama);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $id   = (int)($_POST['id'] ?? 0);
  $nama = trim($_POST['nama'] ?? '');
  if ($id && $nama!=='') {
    $stmt = $conn->prepare("UPDATE kategori SET nama=? WHERE id=?");
    $stmt->bind_param('si', $nama, $id);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

$rows = $conn->query("SELECT id, nama FROM kategori ORDER BY nama ASC");
$u = current_user(); 
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><title>Kategori — Admin</title>
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
        <li class="nav-item"><a class="nav-link active" href="/admin/categories.php">Kategori</a></li>
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
  <h4 class="mb-3">Kategori</h4>

  <form class="row g-2 mb-3" method="post">
    <div class="col-auto">
      <input type="text" name="nama" class="form-control" placeholder="Nama kategori" required>
      <input type="hidden" name="create" value="1">
    </div>
    <div class="col-auto"><button class="btn btn-primary">Tambah</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th style="width:80px">ID</th><th>Nama</th><th style="width:200px">Aksi</th></tr></thead>
      <tbody>
        <?php if ($rows && $rows->num_rows): while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= e($r['nama']) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary"
                    onclick="editKat(<?= (int)$r['id'] ?>,'<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>')">Edit</button>
            <a class="btn btn-sm btn-outline-danger"
               href="?delete=<?= (int)$r['id'] ?>"
               onclick="return confirm('Hapus kategori ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal" tabindex="-1" id="modalEdit" style="display:none;">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post">
      <div class="modal-header">
        <h5 class="modal-title">Edit Kategori</h5>
        <button type="button" class="btn-close" onclick="hide()"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="eid">
        <input type="hidden" name="update" value="1">
        <div class="mb-2">
          <label class="form-label">Nama</label>
          <input type="text" class="form-control" name="nama" id="enama" required>
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
function editKat(id, nama){
  document.getElementById('eid').value = id;
  document.getElementById('enama').value = nama;
  show();
}
function show(){
  const m = document.getElementById('modalEdit');
  m.style.display = 'block';
  m.classList.add('show');
}
function hide(){
  const m = document.getElementById('modalEdit');
  m.classList.remove('show');
  m.style.display = 'none';
}
</script>
</body>
</html>
