<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

// CREATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])) {
  $name = trim($_POST['name'] ?? '');
  if ($name !== '') {
    $stmt = $conn->prepare("INSERT INTO categories(name) VALUES (?)");
    $stmt->bind_param('s', $name);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  if ($id && $name!=='') {
    $stmt = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $stmt->bind_param('si', $name, $id);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

// DELETE
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
  }
  header('Location: /admin/categories.php'); exit;
}

$rows = $conn->query("SELECT * FROM categories ORDER BY name");
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
<nav class="navbar navbar-expand-lg bg-body-tertiary"><div class="container">
  <a class="navbar-brand" href="/admin/dashboard.php">LabLoans</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link active" href="/admin/categories.php">Kategori</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/items.php">Alat</a></li>
    </ul>
    <span class="navbar-text me-3"><?=e($u['name'])?> (<?=e($u['role'])?>)</span>
    <a class="btn btn-outline-danger" href="/logout.php">Logout</a>
  </div>
</div></nav>

<div class="container py-4">
  <h4 class="mb-3">Kategori</h4>
  <form class="row g-2 mb-3" method="post">
    <div class="col-auto">
      <input type="text" name="name" class="form-control" placeholder="Nama kategori" required>
      <input type="hidden" name="create" value="1">
    </div>
    <div class="col-auto"><button class="btn btn-primary">Tambah</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>Nama</th><th style="width:180px">Aksi</th></tr></thead>
      <tbody>
        <?php while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><?= e($r['name']) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary" onclick="editKat(<?= (int)$r['id'] ?>,'<?= e($r['name']) ?>')">Edit</button>
            <a class="btn btn-sm btn-outline-danger" href="?delete=<?=(int)$r['id']?>" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; if($rows->num_rows===0): ?>
        <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal edit -->
<div class="modal" tabindex="-1" id="modalEdit">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post">
      <div class="modal-header"><h5 class="modal-title">Edit Kategori</h5>
        <button type="button" class="btn-close" onclick="hide()"></button></div>
      <div class="modal-body">
        <input type="hidden" name="id" id="eid">
        <input type="hidden" name="update" value="1">
        <div class="mb-2">
          <label class="form-label">Nama</label>
          <input type="text" class="form-control" name="name" id="ename" required>
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
function editKat(id,name){ document.getElementById('eid').value=id; document.getElementById('ename').value=name; show(); }
function show(){ document.getElementById('modalEdit').style.display='block'; document.getElementById('modalEdit').classList.add('show'); }
function hide(){ document.getElementById('modalEdit').classList.remove('show'); document.getElementById('modalEdit').style.display='none'; }
</script>
</body></html>
