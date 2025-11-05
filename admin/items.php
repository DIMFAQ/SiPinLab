<?php
require_once __DIR__.'/../db.php';
require_role('admin','laboran');

// read categories/locations for selects
$cat = $conn->query("SELECT id,name FROM categories ORDER BY name");
$loc = $conn->query("SELECT id,name FROM locations ORDER BY name");

// CREATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create'])) {
  $code = trim($_POST['code_unique'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $category_id = (int)($_POST['category_id'] ?? 0);
  $location_id = (int)($_POST['location_id'] ?? 0);
  $cond = $_POST['condition_enum'] ?? 'good';
  $total = (int)($_POST['total_qty'] ?? 0);
  $avail = (int)($_POST['available_qty'] ?? 0);
  $min   = (int)($_POST['min_qty_alert'] ?? 0);
  $notes = trim($_POST['notes'] ?? '');
  if ($code!=='' && $name!=='') {
    $stmt = $conn->prepare("INSERT INTO items(code_unique,name,category_id,location_id,condition_enum,total_qty,available_qty,min_qty_alert,notes) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssissiiis', $code,$name,$category_id,$location_id,$cond,$total,$avail,$min,$notes);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update'])) {
  $id = (int)($_POST['id'] ?? 0);
  $code = trim($_POST['code_unique'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $category_id = (int)($_POST['category_id'] ?? 0);
  $location_id = (int)($_POST['location_id'] ?? 0);
  $cond = $_POST['condition_enum'] ?? 'good';
  $total = (int)($_POST['total_qty'] ?? 0);
  $avail = (int)($_POST['available_qty'] ?? 0);
  $min   = (int)($_POST['min_qty_alert'] ?? 0);
  $notes = trim($_POST['notes'] ?? '');
  if ($id && $code!=='' && $name!=='') {
    $stmt = $conn->prepare("UPDATE items SET code_unique=?, name=?, category_id=?, location_id=?, condition_enum=?, total_qty=?, available_qty=?, min_qty_alert=?, notes=? WHERE id=?");
    $stmt->bind_param('ssissiiisi', $code,$name,$category_id,$location_id,$cond,$total,$avail,$min,$notes,$id);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// DELETE
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id) {
    $stmt = $conn->prepare("DELETE FROM items WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
  }
  header('Location: /admin/items.php'); exit;
}

// LIST
$list = $conn->query("SELECT i.*, c.name cat, l.name loc
                      FROM items i
                      JOIN categories c ON c.id=i.category_id
                      JOIN locations l ON l.id=i.location_id
                      ORDER BY i.name ASC");
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
<nav class="navbar navbar-expand-lg bg-body-tertiary"><div class="container">
  <a class="navbar-brand" href="/admin/dashboard.php">LabLoans</a>
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link" href="/admin/dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/categories.php">Kategori</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/locations.php">Lokasi</a></li>
      <li class="nav-item"><a class="nav-link active" href="/admin/items.php">Alat</a></li>
    </ul>
    <span class="navbar-text me-3"><?=e($u['name'])?> (<?=e($u['role'])?>)</span>
    <a class="btn btn-outline-danger" href="/logout.php">Logout</a>
  </div>
</div></nav>

<div class="container py-4">
  <h4 class="mb-3">Alat</h4>

  <!-- form tambah -->
  <form class="row g-2 mb-3" method="post">
    <input type="hidden" name="create" value="1">
    <div class="col-md-2"><input class="form-control" name="code_unique" placeholder="Kode unik" required></div>
    <div class="col-md-3"><input class="form-control" name="name" placeholder="Nama alat" required></div>
    <div class="col-md-2">
      <select class="form-select" name="category_id" required>
        <option value="">Kategori...</option>
        <?php while($c=$cat->fetch_assoc()): ?>
          <option value="<?=$c['id']?>"><?=e($c['name'])?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="location_id" required>
        <option value="">Lokasi...</option>
        <?php while($l=$loc->fetch_assoc()): ?>
          <option value="<?=$l['id']?>"><?=e($l['name'])?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-1">
      <select class="form-select" name="condition_enum">
        <option>good</option><option>new</option><option>fair</option><option>broken</option>
      </select>
    </div>
    <div class="col-md-1"><input class="form-control" type="number" name="total_qty" value="0" min="0" placeholder="Total"></div>
    <div class="col-md-1"><input class="form-control" type="number" name="available_qty" value="0" min="0" placeholder="Avail"></div>
    <div class="col-md-1"><input class="form-control" type="number" name="min_qty_alert" value="1" min="0" placeholder="Min"></div>
    <div class="col-md-12"><input class="form-control" name="notes" placeholder="Catatan (opsional)"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Tambah</button></div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped table-sm align-middle">
      <thead>
        <tr>
          <th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th>
          <th>Kondisi</th><th>Avail/Total</th><th>Min</th><th style="width:180px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while($r=$list->fetch_assoc()): ?>
        <tr>
          <td><?=e($r['code_unique'])?></td>
          <td><?=e($r['name'])?></td>
          <td><?=e($r['cat'])?></td>
          <td><?=e($r['loc'])?></td>
          <td><?=e($r['condition_enum'])?></td>
          <td><?= (int)$r['available_qty'] ?>/<?= (int)$r['total_qty'] ?></td>
          <td><?= (int)$r['min_qty_alert'] ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary"
              onclick='editItem(<?=json_encode($r, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT)?>)'>Edit</button>
            <a class="btn btn-sm btn-outline-danger" href="?delete=<?=$r['id']?>" onclick="return confirm('Hapus alat ini?')">Hapus</a>
          </td>
        </tr>
      <?php endwhile; if($list->num_rows===0): ?>
        <tr><td colspan="8" class="text-center text-muted">Belum ada data</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal edit -->
<div class="modal" tabindex="-1" id="modalEdit">
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
          <div class="col-md-3"><label class="form-label">Kode</label><input class="form-control" name="code_unique" id="ecode" required></div>
          <div class="col-md-4"><label class="form-label">Nama</label><input class="form-control" name="name" id="ename" required></div>
          <div class="col-md-3">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="category_id" id="ecat" required>
              <?php
              $cat2 = $conn->query("SELECT id,name FROM categories ORDER BY name");
              while($c=$cat2->fetch_assoc()){
                echo "<option value='".(int)$c['id']."'>".e($c['name'])."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Lokasi</label>
            <select class="form-select" name="location_id" id="eloc" required>
              <?php
              $loc2 = $conn->query("SELECT id,name FROM locations ORDER BY name");
              while($l=$loc2->fetch_assoc()){
                echo "<option value='".(int)$l['id']."'>".e($l['name'])."</option>";
              }
              ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Kondisi</label>
            <select class="form-select" name="condition_enum" id="econd">
              <option>new</option><option selected>good</option><option>fair</option><option>broken</option>
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Total</label><input type="number" class="form-control" name="total_qty" id="etotal" min="0"></div>
          <div class="col-md-3"><label class="form-label">Tersedia</label><input type="number" class="form-control" name="available_qty" id="eavail" min="0"></div>
          <div class="col-md-3"><label class="form-label">Min Alert</label><input type="number" class="form-control" name="min_qty_alert" id="emin" min="0"></div>
          <div class="col-md-12"><label class="form-label">Catatan</label><input class="form-control" name="notes" id="enotes"></div>
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
  document.getElementById('eid').value = r.id;
  document.getElementById('ecode').value = r.code_unique;
  document.getElementById('ename').value = r.name;
  document.getElementById('ecat').value = r.category_id;
  document.getElementById('eloc').value = r.location_id;
  document.getElementById('econd').value = r.condition_enum;
  document.getElementById('etotal').value = r.total_qty;
  document.getElementById('eavail').value = r.available_qty;
  document.getElementById('emin').value = r.min_qty_alert;
  document.getElementById('enotes').value = r.notes ?? '';
  show();
}
function show(){ const m=document.getElementById('modalEdit'); m.style.display='block'; m.classList.add('show'); }
function hide(){ const m=document.getElementById('modalEdit'); m.classList.remove('show'); m.style.display='none'; }
</script>
</body></html>
