<?php require_once __DIR__.'/db.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login — SiPinLab</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  // Ambil user dari tabel 'pengguna'
  $stmt = $conn->prepare("SELECT id, nama, email, sandi, peran, prodi, nim, diblokir FROM pengguna WHERE email = ? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows === 1) {
    $u = $res->fetch_assoc();

    if (!empty($u['diblokir'])) {
      $error = "Akun Anda diblokir. Hubungi admin.";
    } elseif (password_verify($pass, $u['sandi'])) {
      // Simpan ke session dengan key yang konsisten (nama & peran)
      $_SESSION['user'] = [
        'id'     => $u['id'],
        'nama'   => $u['nama'],
        'email'  => $u['email'],
        'peran'  => $u['peran'],
        'prodi'  => $u['prodi'] ?? null,
        'nim'    => $u['nim'] ?? null,
      ];

      // Arahkan sesuai peran
      if (in_array($u['peran'], ['admin','laboran'], true)) {
        header('Location: /admin/dashboard.php'); exit;
      } else {
        header('Location: /peminjam/dashboard.php'); exit;
      }
    } else {
      $error = "Email atau password salah.";
    }
  } else {
    $error = "Email atau password salah.";
  }
}
?>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3">SiPinLab — Login</h4>
          <?php if($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required placeholder="admin@lab.test">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required placeholder="******">
            </div>
            <button class="btn btn-primary w-100">Masuk</button>
          </form>
        </div>

        <p class="text-center mt-3">
          Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
      </div>
      <p class="text-center text-muted mt-3" style="font-size:13px">
        Admin default: <code>admin@lab.test</code> / <code>admin123</code>
      </p>
    </div>
  </div>
</div>
</body>
</html>
