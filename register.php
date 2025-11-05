<?php
require __DIR__.'/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama  = trim($_POST['name']  ?? '');   // form masih pakai name="name" biar kompatibel
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass1 = $_POST['password']  ?? '';
  $pass2 = $_POST['password2'] ?? '';

  // Validasi dasar
  if ($nama === '' || $email === '' || $pass1 === '' || $pass2 === '') {
    $error = 'Semua kolom wajib diisi.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Format email tidak valid.';
  } elseif ($pass1 !== $pass2) {
    $error = 'Konfirmasi password tidak sama.';
  } elseif (strlen($pass1) < 6) {
    $error = 'Password minimal 6 karakter.';
  } else {
    // Cek email duplikat
    $stmt = $conn->prepare("SELECT id FROM pengguna WHERE LOWER(email)=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
      $error = 'Email sudah terdaftar.';
    } else {
      // Simpan user baru
      $hash = password_hash($pass1, PASSWORD_BCRYPT);

      $stmt = $conn->prepare("
        INSERT INTO pengguna(nama, email, sandi, peran, diblokir)
        VALUES(?, ?, ?, 'peminjam', 0)
      ");
      $stmt->bind_param('sss', $nama, $email, $hash);
      $stmt->execute();

      // Set session (pakai key konsisten dengan db.php: nama & peran)
      $_SESSION['user'] = [
        'id'    => $conn->insert_id,
        'nama'  => $nama,
        'email' => $email,
        'peran' => 'peminjam',
      ];

      header('Location: /peminjam/dashboard.php');
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Registrasi — SiPinLab</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3 text-center">Daftar Akun Peminjam</h4>

          <?php if($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
          <?php if($success): ?><div class="alert alert-success py-2"><?= e($success) ?></div><?php endif; ?>

          <form method="post" autocomplete="on">
            <div class="mb-3">
              <label for="name" class="form-label">Nama Lengkap</label>
              <input id="name" type="text" name="name" class="form-control" required placeholder="Nama kamu">
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input id="email" type="email" name="email" class="form-control" required autocomplete="username" placeholder="contoh@lab.test">
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password" placeholder="Minimal 6 karakter">
            </div>
            <div class="mb-3">
              <label for="password2" class="form-label">Konfirmasi Password</label>
              <input id="password2" type="password" name="password2" class="form-control" required autocomplete="new-password" placeholder="Ulangi password">
            </div>
            <button class="btn btn-success w-100" type="submit">Daftar</button>
          </form>

          <p class="text-center mt-3">
            Sudah punya akun? <a href="login.php">Login di sini</a>
          </p>
        </div>
      </div>
      <p class="text-center text-muted mt-3" style="font-size:13px">
        Setelah daftar, kamu otomatis login sebagai <b>peminjam</b>.
      </p>
    </div>
  </div>
</div>
</body>
</html>
